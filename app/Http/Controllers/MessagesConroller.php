<?php

namespace App\Http\Controllers;

use App\Actions\Messages\SendMessageAction;
use App\Http\Requests\Messages\StoreMessageRequest;
use App\Models\User;
use App\Services\DirectMessageService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MessagesConroller extends Controller
{
    /**
     * The service instance for handling direct message logic.
     *
     * @var DirectMessageService
     */
    protected $directMessageService;

    /**
     * The action instance for sending a message.
     *
     * @var SendMessageAction
     */
    protected $sendMessageAction;

    /**
     * Create a new controller instance.
     *
     * @param  DirectMessageService  $directMessageService The service for handling message-related business logic.
     * @param  SendMessageAction  $sendMessageAction The action for sending a message.
     */
    public function __construct(DirectMessageService $directMessageService, SendMessageAction $sendMessageAction)
    {
        // Inject the service for querying data and the action for executing commands.
        $this->directMessageService = $directMessageService;
        $this->sendMessageAction = $sendMessageAction;
    }

    /**
     * Display the messages inbox page.
     *
     * This method renders the main chat interface. It fetches all users for the sidebar
     * and the conversation messages for the currently selected user.
     *
     * @param  User|null  $user The user currently selected in the chat interface (optional).
     * @return Response The Inertia response for the messages inbox view.
     */
    public function show(User $user = null): Response
    {
        // Render the Inertia view with necessary data from the service.
        return Inertia::render('Messages/Inbox', [
            'users' => $this->directMessageService->getUsersForInbox(),
            'selectedUser' => $user,
            'messages' => $user ? $this->directMessageService->getMessagesBetween(auth()->user(), $user) : [],
        ]);
    }

    /**
     * Store a new message in the database.
     *
     * This method validates the incoming request, uses the action to send the message,
     * and then redirects back to the chat interface with the recipient selected.
     *
     * @param  StoreMessageRequest  $request The validated request containing message data.
     * @return RedirectResponse A redirect to the chat interface.
     */
    public function store(StoreMessageRequest $request): RedirectResponse
    {
        // Use the action to send the message with validated data.
        $this->sendMessageAction->execute(
            $request->user(),
            $request->validated('receiver_id'),
            $request->validated('message')
        );

        // Redirect to the chat view with the recipient selected.
        return redirect()->route('messages.show', ['user' => $request->validated('receiver_id')]);
    }
}
