<?php

namespace Tests\Feature\Timeline;

use App\Events\ApplicationStatusUpdated;
use App\Models\Application;
use App\Models\ApplicationStatusChange;
use App\Models\User;
use App\Notifications\ApplicationStageReached;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class ApplicationTimelineTest extends TestCase
{
    use RefreshDatabase, CreatesUsers;

    private User $owner;
    private User $seeker;
    private Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = $this->employerUser();
        $this->seeker = $this->seekerUser();
        $job = $this->jobFor($this->owner);

        $this->actingAs($this->seeker)->post("/apply/{$job->id}", ['resume_text' => 'r', 'cover_letter' => 'c']);
        $this->application = Application::firstOrFail();
    }

    private function move(string $status, array $extra = [])
    {
        return $this->actingAs($this->owner)->put("/applications/{$this->application->id}", ['application_status' => $status, ...$extra]);
    }

    public function test_new_application_starts_at_applied(): void
    {
        $this->assertSame('applied', $this->application->application_status);
    }

    public function test_status_change_broadcasts_only_to_the_applicant(): void
    {
        Event::fake([ApplicationStatusUpdated::class]);

        $this->move('shortlisted')->assertRedirect();

        Event::assertDispatched(ApplicationStatusUpdated::class, function (ApplicationStatusUpdated $e) {
            $channels = collect($e->broadcastOn())->map->name->all();
            $payload = $e->broadcastWith();

            return $channels === ['private-user.'.$this->seeker->id]
                && $e->broadcastAs() === 'application.status'
                && $payload['status'] === 'shortlisted'
                && $payload['application_id'] === $this->application->id
                && ! array_key_exists('changed_by', $payload);
        });
    }

    public function test_other_users_cannot_subscribe_to_the_applicants_channel(): void
    {
        config([
            'broadcasting.default' => 'pusher',
            'broadcasting.connections.pusher.key' => 'test-key',
            'broadcasting.connections.pusher.secret' => 'test-secret',
            'broadcasting.connections.pusher.app_id' => 'test-app',
        ]);
        // Channels are registered on the driver that was active at boot; re-register them on Pusher.
        app(\Illuminate\Broadcasting\BroadcastManager::class)->forgetDrivers();
        require base_path('routes/channels.php');
        $channel = 'private-user.'.$this->seeker->id;

        $this->actingAs($this->owner)
            ->post('/broadcasting/auth', ['socket_id' => '1234.5678', 'channel_name' => $channel])
            ->assertForbidden();

        $this->actingAs($this->seeker)
            ->post('/broadcasting/auth', ['socket_id' => '1234.5678', 'channel_name' => $channel])
            ->assertOk();
    }

    public function test_history_row_written_for_each_change_and_viewed_recorded_once(): void
    {
        $this->actingAs($this->owner)->postJson("/applications/{$this->application->id}/viewed")->assertJson(['viewed' => true]);
        $this->actingAs($this->owner)->postJson("/applications/{$this->application->id}/viewed")->assertJson(['viewed' => false]);
        $this->move('shortlisted');
        $this->move('interview');

        $this->assertSame(
            ['applied', 'viewed', 'shortlisted', 'interview'],
            ApplicationStatusChange::orderBy('id')->pluck('to_status')->all(),
        );
        $this->assertSame(1, ApplicationStatusChange::where('to_status', 'viewed')->count());
    }

    public function test_only_the_owner_can_mark_viewed(): void
    {
        $this->actingAs($this->employerUser())->postJson("/applications/{$this->application->id}/viewed")->assertForbidden();
        $this->actingAs($this->seeker)->postJson("/applications/{$this->application->id}/viewed")->assertForbidden();

        $this->assertSame('applied', $this->application->fresh()->application_status);
    }

    public function test_stages_cannot_go_backwards_without_reopen(): void
    {
        $this->move('interview');

        $this->move('shortlisted')->assertSessionHasErrors('application_status');
        $this->assertSame('interview', $this->application->fresh()->application_status);

        $this->move('shortlisted', ['reopen' => true])->assertSessionHasNoErrors();
        $this->assertSame('shortlisted', $this->application->fresh()->application_status);
    }

    public function test_closed_applications_need_reopen(): void
    {
        $this->move('rejected');

        $this->move('offer')->assertSessionHasErrors('application_status');
        $this->move('rejected')->assertSessionHasErrors('application_status');
        $this->move('interview', ['reopen' => true])->assertSessionHasNoErrors();
    }

    public function test_emails_are_queued_only_for_important_stages(): void
    {
        Notification::fake();

        $this->actingAs($this->owner)->postJson("/applications/{$this->application->id}/viewed");
        Notification::assertNothingSent();

        $this->move('shortlisted');
        $this->move('interview');
        $this->move('offer');

        Notification::assertSentToTimes($this->seeker, ApplicationStageReached::class, 3);
        $this->assertInstanceOf(ShouldQueue::class, new ApplicationStageReached($this->application));
    }

    public function test_rejection_sends_no_email(): void
    {
        Notification::fake();

        $this->move('rejected');

        Notification::assertNothingSent();
    }

    public function test_applied_jobs_page_shows_the_timeline(): void
    {
        $this->actingAs($this->owner)->postJson("/applications/{$this->application->id}/viewed");
        $this->move('shortlisted');

        $this->actingAs($this->seeker)->get('/applied-jobs')
            ->assertInertia(fn (Assert $p) => $p
                ->component('AppliedJobs')
                ->where('appliedJobs.0.application_status', 'shortlisted')
                ->where('appliedJobs.0.timeline.0.status', 'applied')
                ->where('appliedJobs.0.timeline.2.status', 'shortlisted')
                ->missing('appliedJobs.0.timeline.0.changed_by'));
    }

    public function test_employers_cannot_open_applied_jobs_page(): void
    {
        $this->actingAs($this->owner)->get('/applied-jobs')->assertForbidden();
    }

    public function test_rejected_seeker_can_reapply_and_timeline_restarts(): void
    {
        $this->move('rejected');

        $this->actingAs($this->seeker)
            ->post("/apply/{$this->application->jobListing_id}", ['resume_text' => 'r2', 'cover_letter' => 'c2'])
            ->assertRedirect();

        $this->assertSame('applied', $this->application->fresh()->application_status);
        $this->assertSame(['applied', 'rejected', 'applied'], ApplicationStatusChange::orderBy('id')->pluck('to_status')->all());
    }
}
