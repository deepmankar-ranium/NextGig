<?php

namespace App\Jobs;

use App\Matching\MatchService;
use App\Models\Jobseeker;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecomputeSeekerMatches implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public function __construct(public Jobseeker $seeker)
    {
    }

    public function uniqueId(): string
    {
        return (string) $this->seeker->id;
    }

    public function handle(MatchService $matches): void
    {
        $matches->recomputeForSeeker($this->seeker->fresh());
    }
}
