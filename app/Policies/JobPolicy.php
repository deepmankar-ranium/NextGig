<?php

namespace App\Policies;

use App\Models\JobListing;
use App\Models\User;

class JobPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view their own job listings.
     */
    public function viewEmployerJobs(User $user): bool
    {
        return $user->role?->name === 'Employer';
    }

    /**
     * Only employers with an employer profile can post jobs.
     */
    public function create(User $user): bool
    {
        return $user->role?->name === 'Employer' && $user->employer !== null;
    }

    /**
     * Only the employer who owns the listing can edit it.
     */
    public function edit(User $user, JobListing $jobListing): bool
    {
        return $jobListing->employer?->user_id === $user->id;
    }

    public function update(User $user, JobListing $jobListing): bool
    {
        return $this->edit($user, $jobListing);
    }

    public function delete(User $user, JobListing $jobListing): bool
    {
        return $this->edit($user, $jobListing);
    }
}
