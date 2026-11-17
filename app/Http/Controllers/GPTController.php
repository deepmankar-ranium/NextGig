<?php

namespace App\Http\Controllers;

use App\Actions\Chat\ClearChatAction;
use App\Actions\Chat\CreateIntroMessageAction;
use App\Actions\Chat\SendMessageAction;
use App\Http\Requests\ChatRequest;
use App\Services\ChatService;
use Illuminate\Http\Request;
use App\Assistant\AssistantContext;
use App\Models\JobListing;
use Illuminate\Support\Str;
use Inertia\Inertia;

class GPTController extends Controller
{
    public function __construct(private ChatService $chatService)
    {
    }

    public function chat(ChatRequest $request, SendMessageAction $action, AssistantContext $context)
    {
        $focus = $request->validated('job_id') ? JobListing::find($request->validated('job_id')) : null;
        abort_if($focus && ! $context->canFocus($request->user(), $focus), 403);

        $response = $action->execute($request->user(), $request->validated('message'), $focus);

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

    public function clearChat(Request $request, ClearChatAction $action)
    {
        // The page shows its own welcome screen with suggestions once the history is empty.
        $action->execute($request->user());

        return response()->json([
            'status' => 'success'
        ]);
    }

    /**
     * ?job= focuses the assistant on a job (from "Ask the assistant" links); ?q= pre-fills the box.
     * Employers also get their postings to pick from.
     */
    public function getChat(Request $request, AssistantContext $context)
    {
        $user = $request->user();
        $focus = $request->integer('job') ? JobListing::find($request->integer('job')) : null;

        return Inertia::render('Chat', [
            'focusJob' => $focus && $context->canFocus($user, $focus) ? $focus->only(['id', 'title']) : null,
            'prefill' => Str::limit((string) $request->query('q', ''), 1000, ''),
            'myJobs' => $user->employer
                ? JobListing::where('employer_id', $user->employer->id)->latest('id')->get(['id', 'title'])
                : [],
        ]);
    }

    public function createIntroMessage(Request $request, CreateIntroMessageAction $action)
    {
        return response()->json([
            'status' => 'success',
            'message' => $action->execute($request->user())->message,
        ]);
    }
}