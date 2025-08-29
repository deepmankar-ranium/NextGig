<?php

namespace App\Listeners;

use App\Events\JobApplied;
use App\Mail\ApplicationConfirmation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendApplicationConfirmation implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(JobApplied $event): void
    {
        $jobSeekerUser = $event->application->jobSeeker->user;

        if ($jobSeekerUser && $jobSeekerUser->email) {
            Mail::to($jobSeekerUser)->send(new ApplicationConfirmation($event->application));
        }
    }
}
