<?php

namespace Tests\Feature;

use App\Matching\MatchService;
use App\Models\Application;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase, CreatesUsers;

    public function test_seeker_dashboard_shows_top_unapplied_matches_and_application_counts(): void
    {
        $owner = $this->employerUser();
        $php = Tag::create(['name' => 'php']);
        $go = Tag::create(['name' => 'go']);
        $great = $this->jobFor($owner, ['title' => 'Great fit']);
        $great->tags()->sync([$php->id]);
        $applied = $this->jobFor($owner, ['title' => 'Already applied']);
        $applied->tags()->sync([$php->id]);
        $poor = $this->jobFor($owner, ['title' => 'Poor fit']);
        $poor->tags()->sync([$go->id]);

        $seeker = $this->seekerUser();
        $seeker->jobseeker->update(['profile' => ['skills' => ['php'], 'years_experience' => 2, 'roles' => [], 'summary' => '']]);
        app(MatchService::class)->recomputeForSeeker($seeker->jobseeker);
        $app = Application::factory()->create(['jobListing_id' => $applied->id, 'jobSeeker_id' => $seeker->jobseeker->id]);
        $app->transitionTo('interview', $owner);

        $this->actingAs($seeker)->get('/home')
            ->assertInertia(fn (Assert $p) => $p
                ->component('Dashboard')
                ->where('role', 'seeker')
                ->where('hasProfile', true)
                ->where('topMatches.0.title', 'Great fit')
                ->where('topMatches.0.score', 100)
                ->has('topMatches', 2)
                ->where('counts.active', 1)
                ->where('counts.interviews', 1)
                ->where('recentApplications.0.status', 'interview'));
    }

    public function test_employer_dashboard_shows_stats_roles_and_latest_applicants(): void
    {
        $owner = $this->employerUser();
        $job = $this->jobFor($owner, ['title' => 'Backend Dev']);
        $a1 = Application::factory()->create(['jobListing_id' => $job->id, 'jobSeeker_id' => $this->seekerUser()->jobseeker->id]);
        $a2 = Application::factory()->create(['jobListing_id' => $job->id, 'jobSeeker_id' => $this->seekerUser()->jobseeker->id]);
        $a2->transitionTo('shortlisted', $owner);

        // Another employer's applicants never show up.
        $other = $this->jobFor($this->employerUser());
        Application::factory()->create(['jobListing_id' => $other->id, 'jobSeeker_id' => $this->seekerUser()->jobseeker->id]);

        $this->actingAs($owner)->get('/home')
            ->assertInertia(fn (Assert $p) => $p
                ->component('Dashboard')
                ->where('role', 'employer')
                ->where('stats.roles', 1)
                ->where('stats.applicants', 2)
                ->where('stats.new', 1)
                ->where('stats.inProgress', 1)
                ->has('recentApplicants', 2)
                ->where('roles.0.title', 'Backend Dev')
                ->where('roles.0.applicants', 2)
                ->where('roles.0.new', 1));
    }

    public function test_my_postings_include_applicant_counts(): void
    {
        $owner = $this->employerUser();
        $job = $this->jobFor($owner);
        Application::factory()->create(['jobListing_id' => $job->id, 'jobSeeker_id' => $this->seekerUser()->jobseeker->id]);

        $this->actingAs($owner)->get('/posted-jobs')
            ->assertInertia(fn (Assert $p) => $p
                ->where('jobListings.data.0.applications_count', 1)
                ->where('jobListings.data.0.new_count', 1));
    }
}
