<?php

namespace App\Services;

use App\Models\Application;
use Illuminate\Support\Facades\Auth;

class JobApplicationService
{
    public function getApplicationsForEmployer()
    {
        $user = Auth::user();
        return Application::whereHas('jobListing', function ($query) use ($user) {
            $query->where('employer_id', $user->employer->id);
        })->with(['jobListing', 'jobSeeker'])->get();
    }
}