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

    protected function jobFor(User $employerUser, array $attributes = []): JobListing
    {
        return JobListing::factory()->create([
            'employer_id' => $employerUser->employer->id,
            ...$attributes,
        ]);
    }
}
