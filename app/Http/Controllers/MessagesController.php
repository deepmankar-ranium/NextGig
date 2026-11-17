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
use App\Messaging\MessagingRules;
use App\Models\JobListing;
use App\Models\UserReport;
use Illuminate\Http\Request;


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
     * The inbox, with one conversation open if $user is given. A conversation can be opened only
     * when messaging is allowed (see MessagingRules) or the two people have already talked.
     */
    public function show(Request $request, MessagingRules $rules, ?User $user = null): Response|RedirectResponse
    {
        $me = $request->user();

        if ($user && $user->is($me)) {
            $user = null;
        }

        if ($user && ! $rules->canContact($me, $user)) {
            return redirect()->route('messages.show')
                ->with('status', 'You can message employers of posted jobs, and employers can message their applicants.');
        }

        if ($user) {
            $this->directMessageService->markConversationRead($me, $user);
        }

        return Inertia::render('Messages/Inbox', [
            'users'        => $this->directMessageService->getUsersForInbox($me, $user),
            'selectedUser' => $user ? $this->directMessageService->publicProfile($user->loadMissing('role')) : null,
            'messages'     => $user ? $this->directMessageService->getMessagesBetween($me, $user) : [],
            'conversation' => $user ? $this->conversationContext($request, $rules, $me, $user) : null,
            'reportReasons' => UserReport::REASONS,
        ]);
    }

    /**
     * What the thread header needs: block state, the applications that link the two people,
     * and which job new messages are about.
     */
    private function conversationContext(Request $request, MessagingRules $rules, User $me, User $other): array
    {
        $applications = $rules->applicationsBetween($me, $other)->map(fn ($a) => [
            'id' => $a->id,
            'job_id' => $a->jobListing?->id,
            'title' => $a->jobListing?->title,
            'status' => $a->application_status,
        ])->filter(fn ($a) => $a['job_id'])->values();

        $about = null;
        $requested = $request->integer('job') ?: null;
        if ($requested && $rules->jobBelongsToEither($requested, $me, $other)) {
            $about = JobListing::select('id', 'title')->find($requested)?->only(['id', 'title']);
        }

        return [
            'block' => $rules->blockState($me, $other),
            'can_send' => $rules->canSend($me, $other),
            'applications' => $applications,
            'about' => $about,
        ];
    }

    /**
     * Send a message; the page re-renders with the conversation open.
     */
    public function store(StoreMessageRequest $request): RedirectResponse
    {
        $jobId = $request->validated('job_listing_id');

        $this->sendMessageAction->execute(
            $request->user(),
            (int) $request->validated('receiver_id'),
            $request->validated('message'),
            $jobId ? (int) $jobId : null,
        );

        return redirect()->route('messages.show', ['user' => $request->validated('receiver_id')]);
    }

    /**
     * Hide the conversation for the current user only; the other participant keeps it.
     */
    public function deleteMessages(DeleteDirectMessageRequest $request, $sender_id, $receiver_id): RedirectResponse
    {
        $me = (int) $request->user()->id;
        $other = $me === (int) $sender_id ? (int) $receiver_id : (int) $sender_id;

        $this->DeleteDirectMessagesAction->hideConversationFor($me, $other);

        return redirect()->route('messages.show', ['user' => $other]);
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

        InBetweenMessageDeleted::dispatch($message->sender_id, $message->receiver_id, $message->id);


        return redirect()->back();
    }
}
