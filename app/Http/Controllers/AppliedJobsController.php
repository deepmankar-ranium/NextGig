<?php

namespace App\Http\Controllers;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AppliedJobsController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $jobSeekerId = $user->jobseeker->id;

        $appliedJobs = Application::where('jobseeker_id', $jobSeekerId)
            ->with('jobListing')
            ->get();

            return Inertia::render('AppliedJobs', [
                'appliedJobs' => $appliedJobs,
            ]);
    }
}
