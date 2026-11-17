<?php

namespace Tests\Feature\Security;

use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class ApplicationAuthorizationTest extends TestCase
{
    use RefreshDatabase, CreatesUsers;

    private function applicationTo(\App\Models\User $employer): Application
    {
        $seeker = $this->seekerUser();

        return Application::factory()->create([
            'jobListing_id' => $this->jobFor($employer)->id,
            'jobSeeker_id' => $seeker->jobseeker->id,
        ]);
    }

    public function test_job_owner_can_update_application_status(): void
    {
        $owner = $this->employerUser();
        $application = $this->applicationTo($owner);

        $this->actingAs($owner)
            ->put("/applications/{$application->id}", ['application_status' => 'shortlisted'])
            ->assertRedirect();

        $this->assertSame('shortlisted', $application->fresh()->application_status);
    }

    public function test_other_employer_cannot_update_application_status(): void
    {
        $application = $this->applicationTo($this->employerUser());
        $intruder = $this->employerUser();

        $this->actingAs($intruder)
            ->put("/applications/{$application->id}", ['application_status' => 'rejected'])
            ->assertForbidden();

        $this->assertSame('applied', $application->fresh()->application_status);
    }

    public function test_job_seeker_cannot_update_application_status(): void
    {
        $application = $this->applicationTo($this->employerUser());

        $this->actingAs($application->jobSeeker->user)
            ->put("/applications/{$application->id}", ['application_status' => 'shortlisted'])
            ->assertForbidden();

        $this->assertSame('applied', $application->fresh()->application_status);
    }

    public function test_job_seeker_cannot_view_employer_applications_page(): void
    {
        $this->actingAs($this->seekerUser())
            ->get('/view-applications')
            ->assertForbidden();
    }
}
