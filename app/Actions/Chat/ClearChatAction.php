<?php

namespace App\Actions\Chat;

use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;

class ClearChatAction
{
    public function execute(): void
    {
        $userId = Auth::user()->id;
        ChatMessage::where('user_id', $userId)->delete();
    }
}
