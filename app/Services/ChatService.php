<?php

namespace App\Services;

use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;

class ChatService
{
    public function getHistory()
    {
        $userId = Auth::user()->id;
        return ChatMessage::where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->select('sender', 'message', 'created_at')
            ->get();
    }
}