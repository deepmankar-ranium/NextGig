<?php

namespace Tests\Feature\Messaging;

use App\Jobs\EmailUnreadMessage;
use App\Models\Application;
use App\Models\DirectMessage;
use App\Models\User;
use App\Notifications\NewDirectMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class MessagingRulesTest extends TestCase
{
    use RefreshDatabase, CreatesUsers;

    private function send(User $from, User $to, string $text = 'Hello', ?int $job = null)
    {
        return $this->actingAs($from)->post('/messages', array_filter([
            'receiver_id' => $to->id, 'message' => $text, 'job_listing_id' => $job,
        ]));
    }

    private function apply(User $seeker, $job): Application
    {
        return Application::create([
            'jobListing_id' => $job->id, 'jobSeeker_id' => $seeker->jobseeker->id, 'application_status' => 'applied', 'resume_text' => 'cv', 'cover_letter' => '',
        ]);
    }

    public function test_seeker_can_message_an_employer_with_a_posted_job(): void
    {
        Queue::fake();
        $employer = $this->employerUser();
        $job = $this->jobFor($employer);
        $seeker = $this->seekerUser();

        $this->send($seeker, $employer, 'Is this remote?', $job->id)->assertSessionHasNoErrors();

        $this->assertDatabaseHas('direct_messages', ['sender_id' => $seeker->id, 'job_listing_id' => $job->id]);
    }

    public function test_cold_messages_between_seekers_or_to_employers_without_jobs_are_refused(): void
    {
        $a = $this->seekerUser();
        $b = $this->seekerUser();
        $employerWithoutJobs = $this->employerUser();

        $this->send($a, $b)->assertSessionHasErrors('message');
        $this->send($a, $employerWithoutJobs)->assertSessionHasErrors('message');
        $this->actingAs($a)->get("/messages/{$b->id}")->assertRedirect('/messages');

        $this->assertDatabaseCount('direct_messages', 0);
    }

    public function test_employer_can_message_only_their_applicants(): void
    {
        Queue::fake();
        $employer = $this->employerUser();
        $job = $this->jobFor($employer);
        $applicant = $this->seekerUser();
        $stranger = $this->seekerUser();
        $this->apply($applicant, $job);

        $this->send($employer, $applicant, 'Thanks for applying', $job->id)->assertSessionHasNoErrors();
        $this->send($employer, $stranger)->assertSessionHasErrors('message');
    }

    public function test_job_must_belong_to_one_of_the_two_people(): void
    {
        $employer = $this->employerUser();
        $this->jobFor($employer);
        $otherJob = $this->jobFor($this->employerUser());

        $this->send($this->seekerUser(), $employer, 'Hi', $otherJob->id)->assertSessionHasErrors('job_listing_id');
    }

    public function test_thread_shows_linked_applications_and_the_job_from_the_link(): void
    {
        $employer = $this->employerUser();
        $job = $this->jobFor($employer, ['title' => 'Frontend Engineer']);
        $seeker = $this->seekerUser();
        $this->apply($seeker, $job);

        $this->actingAs($employer)->get("/messages/{$seeker->id}?job={$job->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->where('conversation.applications.0.title', 'Frontend Engineer')
                ->where('conversation.about.id', $job->id)
                ->where('conversation.can_send', true)
                ->where('conversation.block', null));
    }

    public function test_block_stops_messages_both_ways_and_unblock_restores(): void
    {
        Queue::fake();
        $employer = $this->employerUser();
        $this->jobFor($employer);
        $seeker = $this->seekerUser();
        $this->send($seeker, $employer)->assertSessionHasNoErrors();

        $this->actingAs($employer)->post("/users/{$seeker->id}/block")->assertRedirect();

        $this->send($seeker, $employer)->assertSessionHasErrors(['message' => "You can't reply to this conversation."]);
        $this->send($employer, $seeker)->assertSessionHasErrors('message');
        $this->actingAs($employer)->get("/messages/{$seeker->id}")
            ->assertInertia(fn (Assert $page) => $page->where('conversation.block', 'you_blocked')->where('conversation.can_send', false));

        $this->actingAs($employer)->delete("/users/{$seeker->id}/block");
        $this->send($seeker, $employer)->assertSessionHasNoErrors();
    }

    public function test_report_is_stored_and_blocks_by_default(): void
    {
        $employer = $this->employerUser();
        $seeker = $this->seekerUser();

        $this->actingAs($seeker)->post("/users/{$employer->id}/report", ['reason' => 'fake', 'details' => 'Asked for a fee'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('user_reports', ['reporter_id' => $seeker->id, 'reported_id' => $employer->id, 'reason' => 'fake']);
        $this->assertDatabaseHas('user_blocks', ['blocker_id' => $seeker->id, 'blocked_id' => $employer->id]);

        $this->actingAs($seeker)->post("/users/{$employer->id}/report", ['reason' => 'nope'])->assertSessionHasErrors('reason');
    }

    public function test_unread_message_is_emailed_once_after_a_delay(): void
    {
        Queue::fake();
        $employer = $this->employerUser();
        $this->jobFor($employer);
        $seeker = $this->seekerUser();

        $this->send($seeker, $employer, 'Hello?');
        Queue::assertPushed(EmailUnreadMessage::class, fn ($job) => $job->delay !== null);

        Notification::fake();
        $first = DirectMessage::first();
        (new EmailUnreadMessage($first->id))->handle(app(\App\Messaging\MessagingRules::class));
        Notification::assertSentTo($employer, NewDirectMessage::class);

        // A second unread message soon after: no second email (throttled).
        $second = DirectMessage::create(['sender_id' => $seeker->id, 'receiver_id' => $employer->id, 'message' => 'Again']);
        (new EmailUnreadMessage($second->id))->handle(app(\App\Messaging\MessagingRules::class));
        Notification::assertSentToTimes($employer, NewDirectMessage::class, 1);
    }

    public function test_read_messages_are_not_emailed(): void
    {
        Notification::fake();
        $employer = $this->employerUser();
        $seeker = $this->seekerUser();
        $m = DirectMessage::create(['sender_id' => $seeker->id, 'receiver_id' => $employer->id, 'message' => 'Hi']);
        $m->forceFill(['read_at' => now()])->save();

        (new EmailUnreadMessage($m->id))->handle(app(\App\Messaging\MessagingRules::class));

        Notification::assertNothingSent();
    }
}
