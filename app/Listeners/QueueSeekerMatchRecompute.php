<?php

namespace App\Listeners;

use App\Events\SeekerProfileUpdated;
use App\Jobs\RecomputeSeekerMatches;

class QueueSeekerMatchRecompute
{
    public function handle(SeekerProfileUpdated $event): void
    {
        RecomputeSeekerMatches::dispatch($event->seeker);
    }
}
