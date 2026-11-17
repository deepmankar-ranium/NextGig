<?php

namespace App\Actions\Messages;

use App\Models\DirectMessage;
use Illuminate\Support\Facades\DB;

class DeleteDirectMessagesAction
{
    /**
     * Hide the conversation with $otherId for $userId only. The other participant keeps
     * their copy. Hidden incoming messages are marked read so they stop counting as unread.
     */
    public function hideConversationFor(int $userId, int $otherId): void
    {
        $now = now();

        DirectMessage::where('sender_id', $userId)
            ->where('receiver_id', $otherId)
            ->whereNull('sender_hidden_at')
            ->update(['sender_hidden_at' => $now]);

        DirectMessage::where('sender_id', $otherId)
            ->where('receiver_id', $userId)
            ->whereNull('receiver_hidden_at')
            ->update(['receiver_hidden_at' => $now, 'read_at' => DB::raw('COALESCE(read_at, CURRENT_TIMESTAMP)')]);
    }
}
