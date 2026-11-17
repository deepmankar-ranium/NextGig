<?php

namespace App\Events;

use App\Models\Application;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast only to the applicant, on their private `user.{id}` channel.
 */
class ApplicationStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Application $application)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.'.$this->application->jobSeeker->user_id)];
    }

    public function broadcastAs(): string
    {
        return 'application.status';
    }

    public function broadcastWith(): array
    {
        $latest = $this->application->statusHistory()->latest('id')->first();

        return [
            'application_id' => $this->application->id,
            'job_title' => $this->application->jobListing->title,
            'status' => $this->application->application_status,
            'at' => ($latest?->created_at ?? now())->toIso8601String(),
        ];
    }
}
