<?php

namespace App\Events;

use App\Models\Jobseeker;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a seeker's parsed profile changes (upload or manual edit). Match scores
 * listen to this (M3).
 */
class SeekerProfileUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Jobseeker $seeker)
    {
    }
}
