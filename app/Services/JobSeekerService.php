<?php

namespace App\Services;

use App\Models\Application;
use Illuminate\Support\Facades\Auth;

class JobSeekerService
{
    public function getAppliedJobs()
    {
        $user = Auth::user();
        $jobSeekerId = $user->jobseeker->id;

        return Application::where('jobseeker_id', $jobSeekerId)
            ->with('jobListing')
            ->get();
    }
}
