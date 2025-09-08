<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DirectMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DirectMessageController extends Controller
{
    /**
     * Get the number of unread messages for the authenticated user.
     */
    public function unreadCount()
    {
        $unreadMessages = DirectMessage::where('receiver_id', Auth::id())
            ->whereNull('read_at')
            ->get();

        $groupedMessages = $unreadMessages->groupBy('sender_id');

        return response()->json([
            'unread_count'    => $unreadMessages->count(),
            'unread_messages' => $groupedMessages,
        ]);
    }

    /**
     * Mark a direct message as read.
     */
    public function markAsRead(DirectMessage $directMessage)
    {
        if ($directMessage->receiver_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $directMessage->read_at = now();
        $directMessage->save();

        return response()->json(['message' => 'Message marked as read.']);
    }
}
