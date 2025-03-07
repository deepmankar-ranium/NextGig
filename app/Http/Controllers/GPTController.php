<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class GPTController extends Controller
{
    private $maxContextLength = 10;
    private $systemPrompt = "You are Chat Assistant, a helpful and friendly AI assistant.  
    - User's name: {USERNAME}  
    - Engage in a conversational and approachable manner.  
    - Maintain context throughout the conversation.  
    - Provide concise, relevant, and focused responses.  
    - Ensure consistency in your personality and tone.  
    - Remember previous messages to maintain continuity.  
    - Do NOT mention the user's name repeatedly.  
    - Keep messages short and to the point unless a longer response is absolutely necessary.";  




    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userId = Auth::user()->id;
        $userName = Auth::user()->full_name;

        try {
            // Save user message without user_name
            $userMessage = ChatMessage::create([
                'user_id' => $userId,
                'sender' => 'user',
                'message' => $request->input('message')
            ]);

            // Get only recent chat history
            $history = ChatMessage::where('user_id', $userId)
                ->latest()
                ->limit(5)
                ->get()
                ->reverse();

            $formattedPrompt = $this->formatChatHistory($history, $userName);
            

            // Make API call
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

            // Save bot response without user_name
            ChatMessage::create([
                'user_id' => $userId,
                'sender' => 'bot',
                'message' => $botMessage
            ]);

            return response()->json([
                'response' => $botMessage,
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'response' => 'Sorry, I encountered an error. Please try again.',
                'status' => 'error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function formatChatHistory($history, $userName)
    {
        // Prepare system prompt with user's name
        $formattedPrompt = str_replace('{USERNAME}', $userName, $this->systemPrompt) . "\n\n";
        
        // Get only the last 4 messages for context
        $recentHistory = $history->take(4);
        
        // Add recent messages only
        foreach ($recentHistory as $message) {
            $role = $message->sender === 'user' ? 'Human' : 'Assistant';
            $formattedPrompt .= "{$role}: {$message->message}\n\n";
        }

        // Add the current user message separately
        $currentMessage = $history->last()->message;
        return $formattedPrompt . "Human: {$currentMessage}\nAssistant:";
    }

    public function getChatHistory()
    {
        $userId = Auth::user()->id;
        
        $messages = ChatMessage::where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->select('sender', 'message', 'created_at')
            ->get();

        return response()->json([
            'history' => $messages,
            'status' => 'success'
        ]);
    }

    public function clearChat()
    {
        $userId = Auth::user()->id;
 
        ChatMessage::where('user_id', $userId)->delete();

        // After clearing, create a new intro message
        $this->createIntroMessage();

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function getChat()
    {
        $userName = Auth::user()->full_name;

        return Inertia::render('Chat');
    }

    public function createIntroMessage()
    {
        try {
            $userId = Auth::user()->id;
            $userName = Auth::user()->full_name;
     
    
            $introMessage = "Hello {$userName}! 👋 I'm your AI assistant. I'm here to help answer your questions and assist with any tasks you have. How can I help you today?";
            
            // Save the intro message to the database
            ChatMessage::create([
                'user_id' => $userId,
                'sender' => 'bot',
                'message' => $introMessage
            ]);

            return response()->json([
                'status' => 'success',
                'message' => $introMessage
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating intro message: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create intro message'
            ], 500);
        }
    }
}