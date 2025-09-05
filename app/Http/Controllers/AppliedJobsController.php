<?php

namespace App\Http\Controllers;

use App\Services\JobSeekerService;
use Inertia\Inertia;

class AppliedJobsController extends Controller
{
    public function __construct(private JobSeekerService $jobSeekerService)
    {
    }

    public function show()
    {
        $appliedJobs = $this->jobSeekerService->getAppliedJobs();

        return Inertia::render('AppliedJobs', [
            'appliedJobs' => $appliedJobs,
        ]);
    }
}