<?php

namespace App\Actions\Chat;

use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendMessageAction
{
    private $maxContextLength = 10;
    private $systemPrompt = "You are Chat Assistant, a helpful and friendly AI assistant.  \n    - User's name: {USERNAME}  \n    - Engage in a conversational and approachable manner.  \n    - Maintain context throughout the conversation.  \n    - Provide concise, relevant, and focused responses.  \n    - Ensure consistency in your personality and tone.  \n    - Remember previous messages to maintain continuity.  \n    - Do NOT mention the user's name repeatedly.  \n    - Keep messages short and to the point unless a longer response is absolutely necessary.";

    public function execute(string $message): ?string
    {
        $userId = Auth::user()->id;
        $userName = Auth::user()->full_name;

        try {
            ChatMessage::create([
                'user_id' => $userId,
                'sender' => 'user',
                'message' => $message
            ]);

            $history = ChatMessage::where('user_id', $userId)
                ->latest()
                ->limit(5)
                ->get()
                ->reverse();

            $formattedPrompt = $this->formatChatHistory($history, $userName);

            $response = Http::timeout(30)->withOptions([
                'verify' => false
            ])->post('http://localhost:11434/api/generate', [
                'model' => 'llama3.2:latest',
                'prompt' => $formattedPrompt,
                'stream' => false,
                'temperature' => 0.7,
                'top_p' => 0.9
            ]);

            if (!$response->successful()) {
                throw new \Exception('API request failed: ' . $response->body());
            }

            $botMessage = $response->json('response');

            if (empty($botMessage)) {
                throw new \Exception('Empty response from API');
            }

            ChatMessage::create([
                'user_id' => $userId,
                'sender' => 'bot',
                'message' => $botMessage
            ]);

            return $botMessage;
        } catch (\Exception $e) {
            Log::error('Error in sendMessage: ' . $e->getMessage());
            return null;
        }
    }

    private function formatChatHistory($history, $userName)
    {
        $formattedPrompt = str_replace('{USERNAME}', $userName, $this->systemPrompt) . "\n\n";
        $recentHistory = $history->take(4);

        foreach ($recentHistory as $message) {
            $role = $message->sender === 'user' ? 'Human' : 'Assistant';
            $formattedPrompt .= "{$role}: {$message->message}\n\n";
        }

        $currentMessage = $history->last()->message;
        return $formattedPrompt . "Human: {$currentMessage}\nAssistant:";
    }
}
