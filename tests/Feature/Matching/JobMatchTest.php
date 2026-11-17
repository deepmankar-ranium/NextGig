<?php

namespace Tests\Feature\Matching;

use App\Models\JobListing;
use App\Models\JobMatch;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUsers;
use Tests\Concerns\FakesAi;
use Tests\TestCase;

class JobMatchTest extends TestCase
{
    use RefreshDatabase, CreatesUsers, FakesAi;

    private function seekerWith(array $skills, float $years = 3): User
    {
        $user = $this->seekerUser();
        $user->jobseeker->update(['profile' => ['skills' => $skills, 'years_experience' => $years, 'roles' => [], 'summary' => '']]);

        return $user;
    }

    private function jobWithSkills(array $skills, ?int $minYears = null): JobListing
    {
        $job = $this->jobFor($this->employerUser(), ['min_years_experience' => $minYears]);
        $job->tags()->sync(collect($skills)->map(fn ($s) => Tag::firstOrCreate(['name' => $s])->id));

        return $job->fresh('tags');
    }

    public function test_job_page_shows_score_matched_and_missing_without_calling_ai(): void
    {
        $ai = $this->fakeAi();
        $seeker = $this->seekerWith(['php', 'laravel']);
        $job = $this->jobWithSkills(['php', 'laravel', 'aws', 'docker']);

        $this->actingAs($seeker)->get("/jobs/{$job->id}")
            ->assertInertia(fn (Assert $p) => $p
                ->where('match.score', 50)
                ->where('match.matched', ['laravel', 'php'])
                ->where('match.missing', ['aws', 'docker'])
                ->where('match.explanation', null));

        $this->assertSame([], $ai->calls);
    }

    public function test_jobs_list_includes_cached_scores_and_sorts_best_matches_first(): void
    {
        $ai = $this->fakeAi();
        $weak = $this->jobWithSkills(['go', 'rust']);
        $strong = $this->jobWithSkills(['php', 'laravel']);
        $medium = $this->jobWithSkills(['php', 'aws']);
        $seeker = $this->seekerWith(['php', 'laravel']);
        app(\App\Matching\MatchService::class)->recomputeForSeeker($seeker->jobseeker);

        $response = $this->actingAs($seeker)->getJson('/filter?sort=match')->assertOk();

        $ids = collect($response->json('jobListings.data'))->pluck('id')->all();
        $this->assertSame([$strong->id, $medium->id, $weak->id], $ids);
        $this->assertSame([100, 50, 0], collect($response->json('jobListings.data'))->pluck('match_score')->all());
        $this->assertSame([], $ai->calls, 'listing never calls the AI');
    }

    public function test_updating_the_profile_recomputes_scores_in_the_background(): void
    {
        $seeker = $this->seekerWith(['php']);
        $job = $this->jobWithSkills(['php', 'laravel']);
        app(\App\Matching\MatchService::class)->recomputeForSeeker($seeker->jobseeker);
        $this->assertSame(50, JobMatch::firstWhere('job_listing_id', $job->id)->score);

        $this->actingAs($seeker)->put('/resume/profile', [
            'skills' => ['php', 'laravel'], 'years_experience' => 3, 'roles' => [], 'summary' => '',
        ]);

        $this->assertSame(100, JobMatch::firstWhere('job_listing_id', $job->id)->score);
    }

    public function test_updating_a_job_recomputes_scores(): void
    {
        $seeker = $this->seekerWith(['php', 'laravel']);
        $owner = $this->employerUser();
        $job = $this->jobFor($owner);
        $php = Tag::create(['name' => 'php']);
        $aws = Tag::create(['name' => 'aws']);

        $this->actingAs($owner)->patch("/jobs/{$job->id}", [
            'title' => $job->title, 'description' => 'x', 'salary' => 1, 'employer_id' => $job->employer_id,
            'tags' => [$php->id, $aws->id],
        ])->assertRedirect();

        $this->assertSame(50, JobMatch::where('jobseeker_id', $seeker->jobseeker->id)->where('job_listing_id', $job->id)->value('score'));
    }

    public function test_explanation_is_grounded_and_cached(): void
    {
        $ai = $this->fakeAi()->pushJson([
            'summary' => 'You already know PHP and Laravel, which covers half of what this role needs.',
            'tips' => [
                ['skill' => 'aws', 'tip' => 'Deploy a small Laravel app on AWS.'],
                ['skill' => 'kubernetes', 'tip' => 'Learn Kubernetes.'],
                ['skill' => 'docker', 'tip' => 'Containerise your projects with Docker, then learn Terraform.'],
            ],
            'mentioned_skills' => ['php', 'laravel', 'aws'],
        ]);
        Tag::create(['name' => 'terraform']);
        Tag::create(['name' => 'kubernetes']);
        $seeker = $this->seekerWith(['php', 'laravel']);
        $job = $this->jobWithSkills(['php', 'laravel', 'aws', 'docker']);

        $this->actingAs($seeker)->postJson("/jobs/{$job->id}/match/explain")
            ->assertOk()
            ->assertJsonPath('score', 50)
            ->assertJsonPath('explanation.source', 'ai')
            ->assertJsonPath('explanation.tips', [['skill' => 'aws', 'tip' => 'Deploy a small Laravel app on AWS.']]);

        // Second request is served from cache: no new AI call.
        $this->actingAs($seeker)->postJson("/jobs/{$job->id}/match/explain")->assertOk();
        $this->assertCount(1, $ai->calls);
    }

    public function test_explanation_mentioning_invented_skills_falls_back_to_template(): void
    {
        $this->fakeAi()->pushJson([
            'summary' => 'Your Kubernetes expertise makes you a strong fit.',
            'tips' => [],
            'mentioned_skills' => ['php'],
        ]);
        Tag::create(['name' => 'kubernetes']);
        $seeker = $this->seekerWith(['php']);
        $job = $this->jobWithSkills(['php', 'laravel']);

        $this->actingAs($seeker)->postJson("/jobs/{$job->id}/match/explain")
            ->assertOk()
            ->assertJsonPath('explanation.source', 'template')
            ->assertJsonPath('explanation.summary', 'You have 1 of the 2 skills this job lists. Missing: laravel.');
    }

    public function test_explanation_prompt_contains_no_personal_data(): void
    {
        $ai = $this->fakeAi()->pushJson(['summary' => 'Good fit.', 'tips' => [], 'mentioned_skills' => []]);
        $seeker = $this->seekerWith(['php']);
        $seeker->jobseeker->update(['resume_text' => 'SECRET RESUME BODY']);
        $job = $this->jobWithSkills(['php']);

        $this->actingAs($seeker)->postJson("/jobs/{$job->id}/match/explain")->assertOk();

        $sent = $ai->sentText();
        $this->assertStringNotContainsString($seeker->email, $sent);
        $this->assertStringNotContainsString($seeker->full_name, $sent);
        $this->assertStringNotContainsString('SECRET RESUME BODY', $sent);
    }

    public function test_ai_failure_falls_back_to_the_plain_explanation(): void
    {
        $this->fakeAi()->pushFailure('unavailable');
        $seeker = $this->seekerWith(['php']);
        $job = $this->jobWithSkills(['php', 'go']);

        $this->actingAs($seeker)->postJson("/jobs/{$job->id}/match/explain")
            ->assertOk()
            ->assertJsonPath('explanation.source', 'template')
            ->assertJsonPath('explanation.tips.0.skill', 'go')
            ->assertJsonStructure(['notice']);

        // Not cached: the AI is tried again next time.
        $this->assertNull(\App\Models\JobMatch::first()->explanation);
    }

    public function test_seeker_can_add_a_missing_skill_and_gets_the_new_score(): void
    {
        $this->fakeAi();
        $seeker = $this->seekerWith(['php']);
        $job = $this->jobWithSkills(['php', 'go']);

        $this->actingAs($seeker)->postJson("/jobs/{$job->id}/match/skills", ['skill' => 'go'])
            ->assertOk()
            ->assertJsonPath('match.score', 100);

        $this->assertContains('go', $seeker->jobseeker->fresh()->profile['skills']);

        // Only skills this job is missing can be added this way.
        $this->actingAs($seeker)->postJson("/jobs/{$job->id}/match/skills", ['skill' => 'cobol'])->assertUnprocessable();
    }

    public function test_employers_cannot_use_seeker_match_endpoint_and_see_no_match_panel(): void
    {
        $this->fakeAi();
        $employer = $this->employerUser();
        $job = $this->jobWithSkills(['php']);

        $this->actingAs($employer)->postJson("/jobs/{$job->id}/match/explain")->assertForbidden();
        $this->actingAs($employer)->get("/jobs/{$job->id}")->assertInertia(fn (Assert $p) => $p->where('match', null));
    }

    public function test_each_seeker_only_sees_their_own_scores(): void
    {
        $this->fakeAi();
        $job = $this->jobWithSkills(['php', 'laravel']);
        $alice = $this->seekerWith(['php', 'laravel']);
        $bob = $this->seekerWith(['go']);

        $this->actingAs($alice)->get("/jobs/{$job->id}")->assertInertia(fn (Assert $p) => $p->where('match.score', 100));
        $this->actingAs($bob)->get("/jobs/{$job->id}")->assertInertia(fn (Assert $p) => $p->where('match.score', 0));
        $this->actingAs($bob)->getJson('/filter')->assertJsonPath('jobListings.data.0.match_score', 0);
    }
}
