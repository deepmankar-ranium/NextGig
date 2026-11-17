<?php

namespace App\Listeners;

use App\Events\ApplicationStatusUpdated;
use App\Models\Application;
use App\Notifications\ApplicationStageReached;

/**
 * Emails the seeker for important stages only. The notification itself is queued.
 */
class SendApplicationStatusUpdate
{
    public function handle(ApplicationStatusUpdated $event): void
    {
        $application = $event->application;

        if (! in_array($application->application_status, Application::EMAIL_STAGES, true)) {
            return;
        }

        $application->jobSeeker?->user?->notify(new ApplicationStageReached($application));
    }
}
