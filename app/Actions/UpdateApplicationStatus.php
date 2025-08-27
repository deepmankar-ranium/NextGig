<?php

namespace App\Actions;

use App\Models\Application;

class UpdateApplicationStatus
{
    public function handle(Application $application, string $status): bool
    {
        return $application->update([
            'application_status' => $status,
        ]);
    }
} 