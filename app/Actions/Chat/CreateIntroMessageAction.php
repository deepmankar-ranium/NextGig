<?php

namespace App\Actions\Chat;

use App\Models\ChatMessage;
use App\Models\User;

class CreateIntroMessageAction
{
    public function execute(User $user): ChatMessage
    {
        return ChatMessage::create([
            'user_id' => $user->id,
            'sender' => 'bot',
            'message' => "Hi! 👋 I'm your NextGig career assistant. Ask me about job searching, your resume, cover letters or interview prep.",
        ]);
    }
}
