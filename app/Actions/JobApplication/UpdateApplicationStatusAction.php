<?php

namespace App\Actions\JobApplication;

use App\Events\ApplicationStatusUpdated;
use App\Models\Application;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UpdateApplicationStatusAction
{
    /**
     * Status changes only happen through an explicit request by a person ($actor).
     */
    public function execute(Application $application, string $status, User $actor, bool $reopen = false): void
    {
        if (! $application->canTransitionTo($status, $reopen)) {
            throw ValidationException::withMessages(['application_status' => 'That status change is not allowed.']);
        }

        $application->transitionTo($status, $actor);

        ApplicationStatusUpdated::dispatch($application->fresh(['jobListing', 'jobSeeker', 'statusHistory']));
    }

    /**
     * Record "viewed" the first time the owning employer opens an application. Idempotent.
     */
    public function markViewed(Application $application, User $employer): bool
    {
        if ($application->application_status !== Application::STATUS_APPLIED) {
            return false;
        }

        $this->execute($application, Application::STATUS_VIEWED, $employer);

        return true;
    }
}
