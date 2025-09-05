<?php

namespace App\Services;

use App\Models\JobListing;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class JobListingService
{
    public function getJobListings(array $filters = [])
    {
        $query = JobListing::with(['employer', 'tags'])->latest();

        if (!empty($filters['tag'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->where('name', $filters['tag']);
            });
        }

        if (!empty($filters['q'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'LIKE', "%{$filters['q']}%")
                    ->orWhere('description', 'LIKE', "%{$filters['q']}%");
            });
        }

        return $query->paginate(6);
    }

    public function getJobListing(JobListing $jobListing)
    {
        return $jobListing->load(['employer.user', 'tags']);
    }

    public function createJobListing(array $data)
    {
        $jobListing = JobListing::create($data);

        if (isset($data['tags'])) {
            $jobListing->tags()->sync($data['tags']);
        }

        return $jobListing;
    }

    public function updateJobListing(JobListing $jobListing, array $data)
    {
        $jobListing->update($data);

        if (isset($data['tags'])) {
            $jobListing->tags()->sync($data['tags']);
        }

        return $jobListing;
    }

    public function deleteJobListing(JobListing $jobListing)
    {
        Gate::authorize('delete', $jobListing);
        $jobListing->delete();
    }

    public function getJobDetails(JobListing $jobListing)
    {
        $job = $this->getJobListing($jobListing);
        $user = Auth::user();
        $isJobSeeker = optional($user->role)->name === "Job Seeker";
        $application = $isJobSeeker && $user->jobseeker
            ? $user->jobseeker->applications()->where('joblisting_id', $jobListing->id)->first()
            : null;

        return [
            'job' => $job,
            'isOwner' => Gate::allows('edit', $job),
            'isJobSeeker' => $isJobSeeker,
            'applicationStatus' => $application ? $application->application_status : null,
        ];
    }

    public function getTags()
    {
        return Tag::all();
    }
}
