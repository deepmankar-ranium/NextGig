<?php

namespace App\Actions\Chat;

use App\Models\ChatMessage;
use App\Models\User;

class ClearChatAction
{
    public function execute(User $user): void
    {
        ChatMessage::where('user_id', $user->id)->delete();
    }
}
