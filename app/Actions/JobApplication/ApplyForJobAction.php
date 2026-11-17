<?php

namespace App\Actions\JobApplication;

use App\Actions\Resume\UploadResumeAction;
use App\Events\JobApplied;
use App\Models\Application;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ApplyForJobAction
{
    public function __construct(private readonly UploadResumeAction $resumes)
    {
    }

    /**
     * @return array{success?: string, error?: string}
     *
     * @throws \App\Resume\ResumeException  the uploaded PDF has no readable text
     */
    public function execute(User $user, JobListing $jobListing, ?UploadedFile $resume, ?string $coverLetter): array
    {
        $seeker = $user->jobseeker;

        $application = Application::where('jobListing_id', $jobListing->id)
            ->where('jobSeeker_id', $seeker->id)
            ->first();

        if ($application && $application->application_status !== Application::STATUS_REJECTED) {
            return ['error' => 'You have already applied for this job.'];
        }

        if ($resume) {
            $this->resumes->storeForApplication($seeker, $resume);
        }

        $resubmitted = $application !== null;

        $application ??= new Application([
            'jobListing_id' => $jobListing->id,
            'jobSeeker_id' => $seeker->id,
        ]);
        $application->fill([
            'resume_text' => (string) $seeker->resume_text,
            'cover_letter' => trim((string) $coverLetter),
        ]);
        $application->transitionTo(Application::STATUS_APPLIED, $user);

        // Snapshot the PDF so later resume changes don't alter what this employer received.
        $snapshot = "applications/application-{$application->id}.pdf";
        if ($seeker->resume && Storage::disk('uploads')->exists($seeker->resume)) {
            Storage::disk('uploads')->delete($snapshot);
            Storage::disk('uploads')->copy($seeker->resume, $snapshot);
            $application->forceFill(['resume_path' => $snapshot])->save();
        }

        event(new JobApplied($application));

        return ['success' => $resubmitted ? 'Application resubmitted successfully.' : 'Application submitted successfully.'];
    }
}
