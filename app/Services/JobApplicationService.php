<?php
namespace App\Services;
use App\Models\Application;
use Illuminate\Support\Facades\Auth;
use App\Models\JobListing;

class JobApplicationService {
    public function getJobApplication($employerId){
                // Retrieve only applications for jobs posted by this employer
                $applications = Application::whereHas('jobListing', function ($query) use ($employerId) {
                    $query->where('employer_id', $employerId);
                })->with(['jobListing','jobSeeker'])->get();

                return $applications;
    }
    public function checkIfAlreadyApplied ($jobListingId) {
        $existingApplication = Application::where('jobListing_id', $jobListingId)
            ->where('jobSeeker_id', Auth::user()->jobseeker->id)
            ->first();
            return $existingApplication;

    }
    public function AppliedJobs($jobSeekerId){
        // Retrieve all applications for the job seeker
        $appliedJobs = Application::where('jobseeker_id', $jobSeekerId)
            ->with('jobListing') 
            ->get();

        return $appliedJobs;
        
    }
}