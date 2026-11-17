<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class JobListingAuthorizationTest extends TestCase
{
    use RefreshDatabase, CreatesUsers;

    private function payload(int $employerId): array
    {
        return [
            'title' => 'Backend Developer',
            'description' => 'Build APIs.',
            'salary' => 90000,
            'employer_id' => $employerId,
        ];
    }

    public function test_employer_can_open_create_job_page(): void
    {
        $this->actingAs($this->employerUser())
            ->get('/Jobs/create')
            ->assertOk();
    }

    public function test_job_seeker_cannot_open_create_job_page(): void
    {
        $this->actingAs($this->seekerUser())
            ->get('/Jobs/create')
            ->assertForbidden();
    }

    public function test_job_seeker_cannot_store_job(): void
    {
        $employer = $this->employerUser();

        $this->actingAs($this->seekerUser())
            ->post('/Jobs/create', $this->payload($employer->employer->id))
            ->assertForbidden();

        $this->assertDatabaseCount('job_listings', 0);
    }

    public function test_employer_cannot_post_a_job_as_another_employer(): void
    {
        $victim = $this->employerUser();

        $this->actingAs($this->employerUser())
            ->post('/Jobs/create', $this->payload($victim->employer->id))
            ->assertForbidden();

        $this->assertDatabaseCount('job_listings', 0);
    }

    public function test_job_seeker_cannot_edit_update_or_delete_a_job(): void
    {
        $job = $this->jobFor($this->employerUser());
        $seeker = $this->seekerUser();

        $this->actingAs($seeker)->get("/Jobs/job/{$job->id}/edit")->assertForbidden();
        $this->actingAs($seeker)
            ->patch("/Jobs/job/{$job->id}", $this->payload($job->employer_id))
            ->assertForbidden();
        $this->actingAs($seeker)->delete("/Jobs/job/{$job->id}")->assertForbidden();

        $this->assertDatabaseHas('job_listings', ['id' => $job->id, 'title' => $job->title]);
    }

    public function test_other_employer_cannot_edit_update_or_delete_a_job(): void
    {
        $job = $this->jobFor($this->employerUser());
        $intruder = $this->employerUser();

        $this->actingAs($intruder)->get("/Jobs/job/{$job->id}/edit")->assertForbidden();
        $this->actingAs($intruder)
            ->patch("/Jobs/job/{$job->id}", $this->payload($intruder->employer->id))
            ->assertForbidden();
        $this->actingAs($intruder)->delete("/Jobs/job/{$job->id}")->assertForbidden();

        $this->assertDatabaseHas('job_listings', ['id' => $job->id, 'employer_id' => $job->employer_id]);
    }

    public function test_owner_can_delete_their_job(): void
    {
        $owner = $this->employerUser();
        $job = $this->jobFor($owner);

        $this->actingAs($owner)->delete("/Jobs/job/{$job->id}")->assertRedirect('/Jobs');

        $this->assertDatabaseMissing('job_listings', ['id' => $job->id]);
    }
}
