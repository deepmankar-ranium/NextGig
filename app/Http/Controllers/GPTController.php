<?php

namespace App\Http\Controllers;

use App\Actions\Chat\ClearChatAction;
use App\Actions\Chat\CreateIntroMessageAction;
use App\Actions\Chat\SendMessageAction;
use App\Http\Requests\ChatRequest;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GPTController extends Controller
{
    public function __construct(private ChatService $chatService)
    {
    }

    public function chat(ChatRequest $request, SendMessageAction $action)
    {
        $response = $action->execute($request->user(), $request->validated('message'));

        if ($response) {
            return response()->json([
                'response' => $response,
                'status' => 'success'
            ]);
        }

        return response()->json([
            'response' => 'The assistant is unavailable right now. Please try again in a minute.',
            'status' => 'error'
        ], 503);
    }

    public function getChatHistory()
    {
        $messages = $this->chatService->getHistory();

        return response()->json([
            'history' => $messages,
            'status' => 'success'
        ]);
    }

    public function clearChat(Request $request, ClearChatAction $action, CreateIntroMessageAction $createIntroMessageAction)
    {
        $action->execute($request->user());
        $createIntroMessageAction->execute($request->user());

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function getChat()
    {
        return Inertia::render('Chat');
    }

    public function createIntroMessage(Request $request, CreateIntroMessageAction $action)
    {
        return response()->json([
            'status' => 'success',
            'message' => $action->execute($request->user())->message,
        ]);
    }
}