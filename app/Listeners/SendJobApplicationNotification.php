<?php

namespace App\Listeners;

use App\Events\JobApplied;
use App\Mail\JobApplicationReceived;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendJobApplicationNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(JobApplied $event): void
    {
        $employerUser = $event->application->jobListing->employer->user;
        
        // Ensure the user is valid and has an email before sending
        if ($employerUser && $employerUser->email) {
            Mail::to($employerUser)->send(new JobApplicationReceived($event->application));
        }
    }
}
