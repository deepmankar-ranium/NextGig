<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class GPTController extends Controller
{
    // Maximum conversation turns to keep for context
    private $maxContextLength = 10;
    
    // System prompt to help guide the model's behavior
    private $systemPrompt = "You are a helpful AI assistant. Be concise and relevant. Avoid repeating yourself.";

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $history = session('chat_history', []);
        $userMessage = ['role' => 'user', 'content' => $request->input('message')];
        
        // Add user message to history
        $history[] = $userMessage;
        
        // Trim history if it gets too long
        if (count($history) > $this->maxContextLength * 2) {
            // Keep the most recent conversations but always retain the first system message
            $systemMessage = $history[0]['role'] === 'system' ? array_shift($history) : null;
            
            // Removing older messages (keep pairs of messages to maintain context)
            $history = array_slice($history, -($this->maxContextLength * 2));
            
            // Re-add system message at the beginning if it existed
            if ($systemMessage) {
                array_unshift($history, $systemMessage);
            }
        }

        try {
            // Format history with the system prompt
            $formattedPrompt = $this->formatChatHistory($history);
            
            $response = Http::timeout(30)->post('http://localhost:11434/api/generate', [
                'model' => 'llama3.2:latest',
                'prompt' => $formattedPrompt,
                'stream' => false,
                // Additional parameters to reduce repetition
                'temperature' => 0.7,
                'top_p' => 0.9,
                'frequency_penalty' => 0.7, // Penalize repetition
                'presence_penalty' => 0.6   // Encourage new topics
            ]);

            if ($response->successful()) {
                $botMessage = $response->json('response');

                // Add bot message to history
                $history[] = ['role' => 'bot', 'content' => $botMessage];
                
                // Update session
                session(['chat_history' => $history]);

                return response()->json([
                    'response' => $botMessage,
                    'history' => $history,
                    'status' => 'success'
                ]);
            }

            return response()->json([
                'response' => 'Error connecting to the language model.',
                'status' => 'error'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'response' => 'Server error: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    public function clearChat()
    {
        session(['chat_history' => []]);
        
        return response()->json([
            'status' => 'success'
        ]);
    }

    public function getChatHistory()
    {
        return response()->json([
            'history' => session('chat_history', [])
        ]);
    }



    /**
     * Format chat history in a way that helps the model maintain context
     */
    private function formatChatHistory($history)
    {
        // Start with system instructions
        $formatted = $this->systemPrompt . "\n\n";
        $formatted .= "Previous conversation:\n";
        
        foreach ($history as $message) {
            // Skip system messages as they're already included above
            if ($message['role'] === 'system') continue;
            
            // Format each message with clear role indicators
            $role = $message['role'] === 'user' ? 'Human' : 'Assistant';
            $formatted .= $role . ": " . $message['content'] . "\n\n";
        }
        
        // Add a prompt to help the model understand it should respond now
        $formatted .= "Assistant:";
        
        return $formatted;
    }

    public function getChat()
    {
        // Initialize the chat with a system message if it doesn't exist
        if (!session()->has('chat_history')) {
            session(['chat_history' => [
                ['role' => 'system', 'content' => $this->systemPrompt]
            ]]);
        }
        
        return Inertia::render('Chat');
    }
}