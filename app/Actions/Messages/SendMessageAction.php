<?php

namespace App\Actions\Messages;

use App\Models\User;
use App\Models\DirectMessage;
use App\Events\ChatMessageSent;

class SendMessageAction
{
    /**
     * Execute the action.
     *
     * @param \App\Models\User $sender
     * @param int $receiverId
     * @param string $messageText
     * @return \App\Models\DirectMessage
     */
    public function execute(User $sender, int $receiverId, string $messageText): DirectMessage
    {
        // Create the direct message record
        $message = DirectMessage::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiverId,
            'message' => $messageText,
        ]);

        // Broadcast the event for real-time updates
        ChatMessageSent::dispatch($message, $sender);

        return $message;
    }
}
