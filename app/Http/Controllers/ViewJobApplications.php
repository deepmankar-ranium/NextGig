<?php

namespace App\Http\Controllers;

use App\Actions\ApplyJobAction;
use App\Http\Requests\ApplyJobrequest;
use App\Models\Application;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Services\JobApplicationService;
use App\Http\Requests\UpdateApplicationStatusRequest;
use App\Actions\UpdateApplicationStatus;

class ViewJobApplications extends Controller
{
    public function show(JobApplicationService $jobApplicationService)
    {
        $user = Auth::user();
        $isEmployer = $user->isEmployer();
    
        // Ensure user is authenticated and is an Employer
        if (!$isEmployer) {
            abort(403, 'Unauthorized action.');
        }
    
        // Retrieve only applications for jobs posted by this employer
        $applications = $jobApplicationService->getJobApplication($user->employer->id);

    
        return Inertia::render('Applications/ViewApplication', [
            'applications' => $applications
        ]);
    }
    

    public function apply(ApplyJobrequest $applyJobrequest, $jobListing, JobApplicationService $jobApplicationService, ApplyJobAction $applyJobAction)
    {
        $user = Auth::user();
        $isJobSeeker = $user->isJobSeeker();

    
        // Ensure user has a role and it's a Job Seeker
        if (!$isJobSeeker) {
            abort(403, 'Unauthorized action.');
        }
        $jobSeeker = $user->jobseeker;

    
        // Validate input
        $validatedData = $applyJobrequest->validated();
    
        // Check if the user has already applied for this job
        $existingApplication = $jobApplicationService->checkIfAlreadyApplied($jobListing);

        if ($existingApplication) {
            $applyJobAction -> ReApplyJob($existingApplication, $validatedData);

        }
        // If not, create a new application
        else {
            $applyJobAction->applyJob($jobListing, $jobSeeker->id, $validatedData);
        }

    
        return redirect()->route('Jobs')->with('success', 'Application submitted successfully.');
    }
    public function update( UpdateApplicationStatusRequest $request, Application $application,UpdateApplicationStatus $updateApplicationStatus) {
        $updateApplicationStatus->handle($application, $request->validated()['application_status']);

        return redirect()->back()->with('success', 'Application status updated successfully.');
    }
    
}
