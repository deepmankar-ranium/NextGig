<?php

namespace App\Services;

use App\Models\Application;
use App\Matching\MatchService;
use App\Models\JobListing;
use App\Models\Jobseeker;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class JobListingService
{
    /**
     * Paginated jobs. For a seeker, each job carries `match_score` from the job_matches cache
     * (never a live AI call), and `sort=match` orders by it.
     */
    public function getJobListings(array $filters = [], ?Jobseeker $seeker = null)
    {
        $query = JobListing::with(['employer', 'tags'])->select('job_listings.*');

        if ($seeker) {
            $query->leftJoin('job_matches', function ($join) use ($seeker) {
                $join->on('job_matches.job_listing_id', '=', 'job_listings.id')
                    ->where('job_matches.jobseeker_id', '=', $seeker->id);
            })->addSelect('job_matches.score as match_score');
        }

        if ($seeker && ($filters['sort'] ?? null) === 'match') {
            $query->orderByRaw('job_matches.score IS NULL')->orderByDesc('job_matches.score');
        }

        $query->latest('job_listings.created_at');

        if (!empty($filters['tag'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->where('name', $filters['tag']);
            });
        }

        if (!empty($filters['q'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('job_listings.title', 'LIKE', "%{$filters['q']}%")
                    ->orWhere('job_listings.description', 'LIKE', "%{$filters['q']}%");
            });
        }

        return $query->paginate(6)->withQueryString();
    }

    public function getJobListing(JobListing $jobListing)
    {
        return $jobListing->load(['employer', 'tags']);
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
            'match' => $isJobSeeker && $user->jobseeker ? $this->matchPayload($user->jobseeker, $job) : null,
        ];
    }

    /**
     * The seeker's own match for this job: score + matched/missing from code, plus the cached
     * AI explanation if it is still valid. Never triggers an AI call.
     */
    public function matchPayload(Jobseeker $seeker, JobListing $job): array
    {
        $match = app(MatchService::class)->get($seeker, $job);

        return [
            'score' => $match->score,
            'matched' => $match->matched,
            'missing' => $match->missing,
            'explanation' => $match->hasFreshExplanation() ? $match->explanation : null,
            'hasProfile' => filled($seeker->profile),
        ];
    }

    public function getTags()
    {
        return Tag::all();
    }
}