<?php

namespace App\Policies;

use App\Models\JobListing;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class JobPolicy
{
    /**
     * Determine if the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view their own job listings.
     */
    public function viewEmployerJobs(User $user): bool
    {
        return $user->role->name === 'Employer';
    }

    /**
     * Determine if the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // You might want to add additional checks here
    }

    /**
     * Determine if the user can edit the model.
     */
    public function edit(User $user, JobListing $jobListing): bool
    {
        return $jobListing->employer?->user_id === $user->id;
    }

    /**
     * Determine if the user can update the model.
     */
    public function update(User $user, JobListing $jobListing): bool
    {
        return $this->edit($user, $jobListing);
    }

    /**
     * Determine if the user can delete the model.
     */
    public function delete(User $user, JobListing $jobListing): bool
    {
        return $this->edit($user, $jobListing);
    }
}
