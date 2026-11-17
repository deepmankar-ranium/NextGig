<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationStatusChange;
use App\Models\User;
use Illuminate\Support\Collection;

class JobSeekerService
{
    /**
     * The seeker's applications with their timeline. Who changed a status is not shown to the
     * seeker; only what changed and when.
     */
    public function getAppliedJobs(User $user): Collection
    {
        return Application::where('jobSeeker_id', $user->jobseeker->id)
            ->with(['jobListing.employer', 'jobListing.tags', 'statusHistory'])
            ->latest('updated_at')
            ->get()
            ->map(fn (Application $a) => [
                'id' => $a->id,
                'application_status' => $a->application_status,
                'job_listing' => $a->jobListing,
                'timeline' => $a->statusHistory->map(fn (ApplicationStatusChange $c) => [
                    'status' => $c->to_status,
                    'at' => $c->created_at?->toIso8601String(),
                ])->all(),
            ]);
    }
}
