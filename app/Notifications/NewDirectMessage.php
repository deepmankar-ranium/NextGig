<?php

namespace App\Notifications;

use App\Models\DirectMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewDirectMessage extends Notification
{
    use Queueable;

    public function __construct(public DirectMessage $message)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $from = $this->message->sender->full_name;
        $about = $this->message->jobListing ? " about {$this->message->jobListing->title}" : '';

        return (new MailMessage)
            ->subject("{$from} sent you a message on NextGig")
            ->greeting('Hi '.($notifiable->full_name ?? 'there').',')
            ->line("{$from} sent you a message{$about}:")
            ->line('“'.Str::limit($this->message->message, 200).'”')
            ->action('Reply', url("/messages/{$this->message->sender_id}"))
            ->line("We email you only when a message is still unread after a few minutes, and at most once every few hours per person.");
    }
}
