<?php

namespace App\Actions\Messages;

use App\Models\DirectMessage;
use Illuminate\Support\Facades\DB;

class DeleteDirectMessagesAction
{
    /**
     * Delete direct messages between two users.
     * Delete message from both users
     *
     * @param int $user1Id
     * @param int $user2Id
     * @return bool
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
    }
}


