<?php

namespace Tests\Feature\Employer;

use App\Models\Application;
use App\Models\ApplicationStatusChange;
use App\Models\JobListing;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use LogicException;
use Tests\Concerns\CreatesUsers;
use Tests\Concerns\FakesAi;
use Tests\TestCase;

class RankedApplicantsTest extends TestCase
{
    use RefreshDatabase, CreatesUsers, FakesAi;

    private function applicant(JobListing $job, array $skills): Application
    {
        $seeker = $this->seekerUser();
        $seeker->jobseeker->update(['profile' => ['skills' => $skills, 'years_experience' => 3, 'roles' => [], 'summary' => '']]);

        return Application::factory()->create([
            'jobListing_id' => $job->id,
            'jobSeeker_id' => $seeker->jobseeker->id,
        ]);
    }

    private function jobWithTags(User $owner, array $tags): JobListing
    {
        $job = $this->jobFor($owner);
        $job->tags()->sync(collect($tags)->map(fn ($t) => Tag::firstOrCreate(['name' => $t])->id));

        return $job;
    }

    public function test_applicants_are_ranked_by_match_score_without_ai_calls(): void
    {
        $ai = $this->fakeAi();
        $owner = $this->employerUser();
        $job = $this->jobWithTags($owner, ['php', 'laravel']);
        $weak = $this->applicant($job, ['go']);
        $strong = $this->applicant($job, ['php', 'laravel']);
        $medium = $this->applicant($job, ['php']);

        $this->actingAs($owner)->get('/view-applications')
            ->assertInertia(fn (Assert $p) => $p
                ->where('ranked', true)
                ->where('applications.0.id', $strong->id)
                ->where('applications.0.match.score', 100)
                ->where('applications.0.match.matched', ['laravel', 'php'])
                ->where('applications.1.id', $medium->id)
                ->where('applications.1.match.missing', ['laravel'])
                ->where('applications.2.id', $weak->id)
                ->missing('applications.0.job_seeker.resume_text')
                ->missing('applications.0.job_seeker.profile'));

        $this->assertSame([], $ai->calls);
    }

    public function test_employer_can_switch_ranking_off(): void
    {
        $this->fakeAi();
        $owner = $this->employerUser();
        $job = $this->jobWithTags($owner, ['php']);
        $first = $this->applicant($job, ['php']);
        $this->travel(1)->minutes();
        $second = $this->applicant($job, ['go']);

        $this->actingAs($owner)->get('/view-applications?ranked=0')
            ->assertInertia(fn (Assert $p) => $p
                ->where('ranked', false)
                ->where('applications.0.id', $second->id)
                ->where('applications.1.id', $first->id));
    }

    public function test_rankings_are_only_visible_to_the_owning_employer(): void
    {
        $this->fakeAi();
        $owner = $this->employerUser();
        $this->applicant($this->jobWithTags($owner, ['php']), ['php']);

        $this->actingAs($this->employerUser())->get('/view-applications')
            ->assertInertia(fn (Assert $p) => $p->has('applications', 0));
    }

    public function test_ranking_never_changes_a_status(): void
    {
        $this->fakeAi();
        $owner = $this->employerUser();
        $job = $this->jobWithTags($owner, ['php', 'laravel', 'aws']);
        $application = $this->applicant($job, ['go']);

        $this->actingAs($owner)->get('/view-applications');
        app(\App\Matching\MatchService::class)->recomputeForJob($job);

        $this->assertSame('applied', $application->fresh()->application_status);
        $this->assertSame(0, ApplicationStatusChange::count());
    }

    public function test_every_status_change_is_audited_with_actor_and_time(): void
    {
        \Illuminate\Support\Facades\Storage::fake('uploads');
        $owner = $this->employerUser();
        $job = $this->jobFor($owner);
        $seeker = $this->withResumeOnFile($this->seekerUser());

        $this->actingAs($seeker)->post("/apply/{$job->id}", ['cover_letter' => 'c'])->assertRedirect();
        $application = Application::firstOrFail();
        $this->assertSame('applied', $application->application_status);

        $this->actingAs($owner)->put("/applications/{$application->id}", ['application_status' => 'shortlisted']);

        $history = $application->statusHistory()->get();
        $this->assertCount(2, $history);
        $this->assertSame([null, 'applied', $seeker->id], [$history[0]->from_status, $history[0]->to_status, $history[0]->changed_by]);
        $this->assertSame(['applied', 'shortlisted', $owner->id], [$history[1]->from_status, $history[1]->to_status, $history[1]->changed_by]);
        $this->assertNotNull($history[1]->created_at);
    }

    public function test_history_is_append_only(): void
    {
        $owner = $this->employerUser();
        $application = $this->applicant($this->jobFor($owner), ['php']);
        $application->transitionTo('shortlisted', $owner);
        $row = ApplicationStatusChange::firstOrFail();

        $this->expectException(LogicException::class);
        $row->update(['to_status' => 'rejected']);
    }
}
