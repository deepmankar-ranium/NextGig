<?php
namespace App\Actions;
use App\Models\Application;
use App\Models\Job;
use Illuminate\Support\Facades\Auth;

class ApplyJobAction{
    public function ReApplyJob($existingApplication, $validatedData){
        if ($existingApplication->application_status === 'rejected') {
            // If the application was rejected, allow reapplying by updating it
            $existingApplication->update([
                'resume_text' => $validatedData['resume_text'],
                'cover_letter' => $validatedData['cover_letter'],
                'application_status' => 'pending', // Change status to pending
            ]);

            return redirect()->route('Jobs')->with('success', 'Application resubmitted successfully.');
        } else {
            // If the application is not rejected, prevent multiple applications
            return redirect()->back()->withErrors(['error' => 'You have already applied for this job.']);
        }

    }
    public function ApplyJob($jobListing, $jobSeekerId, $validatedData){    
        // If no previous application exists, create a new one
        Application::create([
            'jobListing_id' => intval($jobListing), // Ensure it's an integer
            'jobSeeker_id' => $jobSeekerId,
            'resume_text' => $validatedData['resume_text'],
            'cover_letter' => $validatedData['cover_letter'],
            'application_status' => 'Pending', // Default application status
        ]);

    }
}