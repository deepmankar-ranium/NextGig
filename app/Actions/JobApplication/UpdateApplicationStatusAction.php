<?php

namespace App\Actions\JobApplication;

use App\Events\ApplicationStatusUpdated;
use App\Models\Application;

class UpdateApplicationStatusAction
{
    public function execute(Application $application, string $status): void
    {
        $application->update([
            'application_status' => $status,
        ]);

        event(new ApplicationStatusUpdated($application));
    }
}
