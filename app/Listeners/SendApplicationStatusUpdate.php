<?php

namespace App\Listeners;

use App\Events\ApplicationStatusUpdated;
use App\Mail\ApplicationStatusUpdate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendApplicationStatusUpdate implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(ApplicationStatusUpdated $event): void
    {
        $jobSeekerUser = $event->application->jobSeeker->user;

        // Do not send an email if the status is still pending
        if ($event->application->application_status === 'pending') {
            return;
        }

        if ($jobSeekerUser && $jobSeekerUser->email) {
            Mail::to($jobSeekerUser)->send(new ApplicationStatusUpdate($event->application));
        }
    }
}
