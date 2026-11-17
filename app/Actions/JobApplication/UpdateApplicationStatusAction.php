<?php

namespace App\Actions\JobApplication;

use App\Events\ApplicationStatusUpdated;
use App\Models\Application;
use App\Models\User;

class UpdateApplicationStatusAction
{
    /**
     * Status changes only happen through an explicit request by a person ($actor).
     */
    public function execute(Application $application, string $status, User $actor): void
    {
        if ($application->application_status === $status) {
            return;
        }

        $application->transitionTo($status, $actor);

        event(new ApplicationStatusUpdated($application));
    }
}
