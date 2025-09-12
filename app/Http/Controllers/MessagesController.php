<?php

namespace App\Http\Controllers;

use App\Actions\Messages\DeleteDirectMessagesAction;
use App\Actions\Messages\SendMessageAction;
use App\Http\Requests\DeleteDirectMessageRequest;
use App\Http\Requests\Messages\StoreMessageRequest;
use App\Models\User;
use App\Services\DirectMessageService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\DirectMessage;
use App\Events\InBetweenMessageDeleted;


use Illuminate\Support\Facades\Log;


class MessagesController extends Controller
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
     * The action instance for deleting direct messages.
     *
     * @var DeleteDirectMessagesAction
     */
    protected $DeleteDirectMessagesAction;

    /**
     * Create a new controller instance.
     *
     * @param  DirectMessageService  $directMessageService The service for handling message-related business logic.
     * @param  SendMessageAction  $sendMessageAction The action for sending a message.
     * @param  DeleteDirectMessagesAction  $DeleteDirectMessagesAction The action for deleting messages.
     */
    public function __construct(
        DirectMessageService $directMessageService,
        SendMessageAction $sendMessageAction,
        DeleteDirectMessagesAction $DeleteDirectMessagesAction
    ) {
        $this->directMessageService       = $directMessageService;
        $this->sendMessageAction          = $sendMessageAction;
        $this->DeleteDirectMessagesAction = $DeleteDirectMessagesAction;
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
            'users'        => $this->directMessageService->getUsersForInbox(),
            'selectedUser' => $user,
            'messages'     => $user ? $this->directMessageService->getMessagesBetween(auth()->user(), $user) : [],
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

    public function deleteMessages(DeleteDirectMessageRequest $request, $sender_id, $receiver_id): RedirectResponse
    {
        $request->authorize();
        $this->DeleteDirectMessagesAction->deleteMessages($sender_id, $receiver_id);

        return redirect()->route('messages.show', ['user' => $receiver_id]);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($message_id)
    {
        $message = DirectMessage::find($message_id);
        $user = auth()->user();


        if (!$message) {
            return abort(404, 'Message not found.');
        }


        if ($message->sender_id !== $user->id) {
            return abort(403, 'Unauthorized action.');
        }

        $message->delete();

        Log::info('Dispatching InBetweenMessageDeleted event', ['message_id' => $message->id, 'sender_id' => $message->sender_id, 'receiver_id' => $message->receiver_id]);
        InBetweenMessageDeleted::dispatch($message->sender_id, $message->receiver_id, $message->id);


        return redirect()->back()->with('status', 'Message deleted successfully.');
    }
}
