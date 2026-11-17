<?php

namespace App\Actions\JobApplication;

use App\Events\JobApplied;
use App\Models\Application;
use App\Models\JobListing;
use Illuminate\Support\Facades\Auth;

class ApplyForJobAction
{
    public function execute(JobListing $jobListing, array $data)
    {
        $user = Auth::user();
        $jobSeeker = $user->jobseeker;

        if (! $jobSeeker) {
            return ['error' => 'Job Seeker profile not found.'];
        }

        $application = Application::where('jobListing_id', $jobListing->id)
            ->where('jobSeeker_id', $jobSeeker->id)
            ->first();

        if ($application && $application->application_status !== Application::STATUS_REJECTED) {
            return ['error' => 'You have already applied for this job.'];
        }

        $resubmitted = $application !== null;

        $application ??= new Application([
            'jobListing_id' => $jobListing->id,
            'jobSeeker_id' => $jobSeeker->id,
        ]);
        $application->fill([
            'resume_text' => $data['resume_text'],
            'cover_letter' => $data['cover_letter'],
        ]);
        $application->transitionTo(Application::STATUS_PENDING, $user);

        event(new JobApplied($application));

        return ['success' => $resubmitted ? 'Application resubmitted successfully.' : 'Application submitted successfully.'];
    }
}
