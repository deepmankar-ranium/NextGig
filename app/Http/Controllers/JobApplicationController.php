<?php

namespace App\Http\Controllers;

use App\Actions\JobApplication\ApplyForJobAction;
use App\Actions\JobApplication\UpdateApplicationStatusAction;
use App\Http\Requests\ApplyForJobRequest;
use App\Http\Requests\UpdateApplicationStatusRequest;
use App\Models\Application;
use App\Models\JobListing;
use App\Services\JobApplicationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JobApplicationController extends Controller
{
    public function __construct(private JobApplicationService $jobApplicationService)
    {
    }

    public function show()
    {
        $this->authorize('viewAny', Application::class);
        $applications = $this->jobApplicationService->getApplicationsForEmployer();

        return Inertia::render('Applications/ViewApplication', [
            'applications' => $applications
        ]);
    }

    public function apply(ApplyForJobRequest $request, JobListing $jobListing, ApplyForJobAction $action)
    {
        $result = $action->execute($jobListing, $request->validated());

        if (isset($result['error'])) {
            return redirect()->back()->withErrors($result);
        }

        return redirect()->route('Jobs')->with($result);
    }

    public function update(UpdateApplicationStatusRequest $request, Application $application, UpdateApplicationStatusAction $action)
    {
        $action->execute($application, $request->validated('application_status'));

        return redirect()->back()->with('success', 'Application status updated successfully.');
    }
}
