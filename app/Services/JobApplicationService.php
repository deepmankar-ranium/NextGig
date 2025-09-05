<?php

namespace App\Services;

use App\Events\ApplicationStatusUpdated;
use App\Events\JobApplied;
use App\Models\Application;
use App\Models\JobListing;
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

    public function applyForJob(JobListing $jobListing, array $data)
    {
        $user = Auth::user();
        $jobSeeker = $user->jobSeeker;

        if (!$jobSeeker) {
            return ['error' => 'Job Seeker profile not found.'];
        }

        $existingApplication = Application::where('jobListing_id', $jobListing->id)
            ->where('jobSeeker_id', $jobSeeker->id)
            ->first();

        if ($existingApplication) {
            if ($existingApplication->application_status === 'rejected') {
                $existingApplication->update([
                    'resume_text' => $data['resume_text'],
                    'cover_letter' => $data['cover_letter'],
                    'application_status' => 'pending',
                ]);

                event(new JobApplied($existingApplication));

                return ['success' => 'Application resubmitted successfully.'];
            } else {
                return ['error' => 'You have already applied for this job.'];
            }
        }

        $newApplication = Application::create([
            'jobListing_id' => $jobListing->id,
            'jobSeeker_id' => $jobSeeker->id,
            'resume_text' => $data['resume_text'],
            'cover_letter' => $data['cover_letter'],
            'application_status' => 'Pending',
        ]);

        event(new JobApplied($newApplication));

        return ['success' => 'Application submitted successfully.'];
    }

    public function updateApplicationStatus(Application $application, string $status): void
    {
        $application->update([
            'application_status' => $status,
        ]);

        event(new ApplicationStatusUpdated($application));
    }
}
