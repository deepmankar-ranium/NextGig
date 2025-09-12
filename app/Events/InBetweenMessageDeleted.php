<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InBetweenMessageDeleted implements ShouldBroadcast
{
use Dispatchable, InteractsWithSockets, SerializesModels;

    public $senderId;
    public $receiverId;
    public $messageId;

    /**
     * Create a new event instance.
     */
    public function __construct($senderId, $receiverId, $messageId)
    {
        $this->senderId = $senderId;
        $this->receiverId = $receiverId;
        $this->messageId = $messageId;

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $participants = [$this->senderId, $this->receiverId];
        sort($participants); // Ensure consistent channel name

        return [
            new PrivateChannel('chat.' . implode('.', $participants)),
            new PrivateChannel('user.' . $this->receiverId),
            new PrivateChannel('user.' . $this->senderId),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'sender_id' => $this->senderId,
            'receiver_id' => $this->receiverId,
            'message_id' => $this->messageId,
        ];
    }
}
