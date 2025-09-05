<?php

namespace App\Actions\Chat;

use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CreateIntroMessageAction
{
    public function execute(): ?ChatMessage
    {
        try {
            $userId = Auth::user()->id;
            $userName = Auth::user()->full_name;

            $introMessage = "Hello {$userName}! 👋 I'm your AI assistant. I'm here to help answer your questions and assist with any tasks you have. How can I help you today?";

            return ChatMessage::create([
                'user_id' => $userId,
                'sender' => 'bot',
                'message' => $introMessage
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating intro message: ' . $e->getMessage());
            return null;
        }
    }
}
