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

        return JobListing::with(['employer'])
            ->where('employer_id', $employerId)
            ->latest()
            ->paginate(6);
    }
}
