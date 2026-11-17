<?php

namespace App\Services;

use App\Models\JobListing;
use Illuminate\Support\Facades\Auth;

class EmployerJobListingService
{
    public function getJobListings()
    {
        $user = Auth::user();
        $employerId = $user->employer->id;

        return JobListing::with(['employer', 'tags'])
            ->withCount([
                'applications',
                'applications as new_count' => fn ($q) => $q->where('application_status', \App\Models\Application::STATUS_APPLIED),
            ])
            ->where('employer_id', $employerId)
            ->latest()
            ->paginate(9);
    }
}
