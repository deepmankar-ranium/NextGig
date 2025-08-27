<?php

namespace App\Http\Controllers;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Services\JobApplicationService;


class AppliedJobsController extends Controller
{
    public function show(JobApplicationService $jobApplicationService)
    {
        $user = Auth::user();
        $jobSeekerId = $user->jobseeker->id;

        $appliedJobs = $jobApplicationService->AppliedJobs($jobSeekerId);

            return Inertia::render('AppliedJobs', [
                'appliedJobs' => $appliedJobs,
            ]);
    }

}
