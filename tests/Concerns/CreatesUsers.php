<?php

namespace Tests\Concerns;

use App\Models\Employer;
use App\Models\JobListing;
use App\Models\Jobseeker;
use App\Models\Role;
use App\Models\User;

trait CreatesUsers
{
    /**
     * Roles are referenced by id in the registration flow (2 = Employer, 3 = Job Seeker),
     * so create them in a fixed order.
     */
    protected function seedRoles(): void
    {
        foreach (['Admin', 'Employer', 'Job Seeker'] as $name) {
            Role::firstOrCreate(['name' => $name]);
        }
    }

    protected function employerUser(): User
    {
        $this->seedRoles();

        return Employer::factory()->create()->user;
    }

    protected function seekerUser(): User
    {
        $this->seedRoles();

        return Jobseeker::factory()->create()->user;
    }

    /**
     * Give a seeker a resume on file (call Storage::fake('uploads') first).
     */
    protected function withResumeOnFile(User $seekerUser, string $text = "Asha Rao\nLaravel developer\nSkills: PHP, Laravel"): User
    {
        $path = "resumes/seeker-{$seekerUser->jobseeker->id}.pdf";
        \Illuminate\Support\Facades\Storage::disk('uploads')->put($path, \Tests\Support\Pdf::withText(explode("\n", $text)));
        $seekerUser->jobseeker->update(['resume' => $path, 'resume_text' => $text]);

        return $seekerUser;
    }

    protected function jobFor(User $employerUser, array $attributes = []): JobListing
    {
        return JobListing::factory()->create([
            'employer_id' => $employerUser->employer->id,
            ...$attributes,
        ]);
    }
}
