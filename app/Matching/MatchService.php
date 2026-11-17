<?php

namespace App\Matching;

use App\Models\JobListing;
use App\Models\JobMatch;
use App\Models\Jobseeker;

class MatchService
{
    public function __construct(private readonly MatchScorer $scorer)
    {
    }

    public function compute(Jobseeker $seeker, JobListing $job): JobMatch
    {
        $job->loadMissing('tags');

        $result = $this->scorer->score(
            $seeker->profileSkills(),
            isset($seeker->profile['years_experience']) ? (float) $seeker->profile['years_experience'] : null,
            $job->tags->pluck('name'),
            $job->min_years_experience,
        );

        $match = JobMatch::firstOrNew(['jobseeker_id' => $seeker->id, 'job_listing_id' => $job->id]);
        $match->fill([
            'score' => $result->score,
            'matched' => $result->matched,
            'missing' => $result->missing,
            'inputs_hash' => $result->inputsHash,
        ])->save();

        return $match;
    }

    /**
     * Cached match, computed (cheaply, no AI) if missing or stale.
     */
    public function get(Jobseeker $seeker, JobListing $job): JobMatch
    {
        $match = JobMatch::where('jobseeker_id', $seeker->id)->where('job_listing_id', $job->id)->first();

        return $match ?? $this->compute($seeker, $job);
    }

    public function recomputeForSeeker(Jobseeker $seeker): void
    {
        JobListing::with('tags')->chunkById(200, function ($jobs) use ($seeker) {
            foreach ($jobs as $job) {
                $this->compute($seeker, $job);
            }
        });
    }

    public function recomputeForJob(JobListing $job): void
    {
        $job->load('tags');

        Jobseeker::where(fn ($q) => $q->whereNotNull('profile')->orWhereNotNull('skills'))
            ->chunkById(200, function ($seekers) use ($job) {
                foreach ($seekers as $seeker) {
                    $this->compute($seeker, $job);
                }
            });
    }
}
