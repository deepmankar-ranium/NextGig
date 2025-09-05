<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatRequest;
use App\Services\ChatService;
use Inertia\Inertia;

class GPTController extends Controller
{
    public function __construct(private ChatService $chatService)
    {
    }

    public function chat(ChatRequest $request)
    {
        $response = $this->chatService->sendMessage($request->validated('message'));

        if ($response) {
            return response()->json([
                'response' => $response,
                'status' => 'success'
            ]);
        }

        return response()->json([
            'response' => 'Sorry, I encountered an error. Please try again.',
            'status' => 'error'
        ], 500);
    }

    public function getChatHistory()
    {
        $messages = $this->chatService->getHistory();

        return response()->json([
            'history' => $messages,
            'status' => 'success'
        ]);
    }

    public function clearChat()
    {
        $this->chatService->clearHistory();

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function getChat()
    {
        return Inertia::render('Chat');
    }

    public function createIntroMessage()
    {
        $introMessage = $this->chatService->createIntroMessage();

        if ($introMessage) {
            return response()->json([
                'status' => 'success',
                'message' => $introMessage->message
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to create intro message'
        ], 500);
    }
}
