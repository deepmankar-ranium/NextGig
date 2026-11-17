<?php

namespace App\Jobs;

use App\Matching\MatchService;
use App\Models\JobListing;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecomputeJobMatches implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public function __construct(public JobListing $jobListing)
    {
    }

    public function uniqueId(): string
    {
        return (string) $this->jobListing->id;
    }

    public function handle(MatchService $matches): void
    {
        $matches->recomputeForJob($this->jobListing->fresh());
    }
}
