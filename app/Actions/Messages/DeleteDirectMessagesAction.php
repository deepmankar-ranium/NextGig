<?php

namespace App\Actions\Messages;

use App\Events\MessagesDeleted;
use App\Models\DirectMessage;

class DeleteDirectMessagesAction
{
    /**
     * Delete direct messages between two users and broadcast an event.
     *
     * @param int $sender_id
     * @param int $receiver_id
     * @return void
     */
    public function deleteMessages(int $sender_id, int $receiver_id): void
    {
        DirectMessage::where(function ($query) use ($sender_id, $receiver_id) {
            $query->where('sender_id', $sender_id)
                ->where('receiver_id', $receiver_id);
        })->orWhere(function ($query) use ($sender_id, $receiver_id) {
            $query->where('sender_id', $receiver_id)
                ->where('receiver_id', $sender_id);
        })->delete();

        broadcast(new MessagesDeleted($sender_id, $receiver_id))->toOthers();
    }
}


