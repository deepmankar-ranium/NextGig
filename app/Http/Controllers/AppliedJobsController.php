<?php

namespace App\Http\Controllers;

use App\Services\JobSeekerService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppliedJobsController extends Controller
{
    public function __construct(private JobSeekerService $jobSeekerService)
    {
    }

    public function show(Request $request)
    {
        abort_unless($request->user()->role?->name === 'Job Seeker' && $request->user()->jobseeker, 403);

        $appliedJobs = $this->jobSeekerService->getAppliedJobs($request->user());

        return Inertia::render('AppliedJobs', [
            'appliedJobs' => $appliedJobs,
        ]);
    }
}