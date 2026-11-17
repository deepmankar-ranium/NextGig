<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class JobApplicationPolicy
{
    /**
     * The "view applications" page lists applications to the employer's own jobs.
     */
    public function viewAny(User $user): bool
    {
        return $user->role?->name === 'Employer' && $user->employer !== null;
    }

    /**
     * The applicant, or the employer who owns the job.
     */
    public function view(User $user, Application $application): bool
    {
        return $this->ownsJob($user, $application)
            || $application->jobSeeker?->user_id === $user->id;
    }

    /**
     * Only the employer who owns the application's job may change its status.
     */
    public function update(User $user, Application $application): bool
    {
        return $user->role?->name === 'Employer' && $this->ownsJob($user, $application);
    }

    public function delete(User $user, Application $application): bool
    {
        return false;
    }

    private function ownsJob(User $user, Application $application): bool
    {
        return $application->jobListing?->employer?->user_id === $user->id;
    }
}
