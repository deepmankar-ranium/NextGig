<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email for the important stages (shortlisted, interview, offer). Always queued, never sent
 * inside the request.
 */
class ApplicationStageReached extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Application $application)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->application->jobListing->title;

        $line = match ($this->application->application_status) {
            Application::STATUS_SHORTLISTED => "Good news: you've been shortlisted for {$title}.",
            Application::STATUS_INTERVIEW => "The employer would like to interview you for {$title}. They'll be in touch with details.",
            Application::STATUS_OFFER => "Congratulations! You've received an offer for {$title}.",
            default => "Your application for {$title} has been updated.",
        };

        return (new MailMessage)
            ->subject("Update on your application: {$title}")
            ->greeting('Hi '.($notifiable->full_name ?? 'there').',')
            ->line($line)
            ->action('View your applications', url('/applied-jobs'))
            ->line('Thank you for using NextGig!');
    }
}
