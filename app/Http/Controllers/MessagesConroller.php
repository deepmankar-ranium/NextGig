<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\DirectMessage;
use App\Events\ChatMessageSent;
use Illuminate\Support\Facades\Log;

class MessagesConroller extends Controller
{
    public function show(User $user = null){
        return Inertia::render('Messages/Inbox', [
            'users' => $this->getUsers(),
            'selectedUser' => $user,
            'messages' => $user ? $this->getMessages($user) : [],
        ]);
    }

    public function getUsers(){
        // Eager load the role relationship to avoid N+1 query problem
        // Select specific columns to reduce memory usage if not all columns are needed
        $users = User::with('role')->select('id', 'full_name', 'email', 'role_id')->get();
        return $users->map(fn($user) => ['id' => $user->id, 'name' => $user->full_name, 'email' => $user->email, 'role' => $user->role->name ?? 'N/A']);
    }

    private function getMessages(User $user){
        $authUserId = auth()->id();

        // Fetch messages between the authenticated user and the selected user
        $messages = DirectMessage::where(function ($query) use ($authUserId, $user) {
            $query->where('sender_id', $authUserId)
                  ->where('receiver_id', $user->id);
        })->orWhere(function ($query) use ($authUserId, $user) {
            $query->where('sender_id', $user->id)
                  ->where('receiver_id', $authUserId);
        })->orderBy('created_at', 'asc')->get();

        return $messages;
    }

    public function fetchInboxData(User $user){
        return Inertia::render('Messages/Inbox', [
            'users' => $this->getUsers(),
            'selectedUser' => $user,
            'messages' => $this->getMessages($user),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        $message = DirectMessage::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);

        ChatMessageSent::dispatch($message, auth()->user());

        return Inertia::render('Messages/Inbox', [
            'users' => $this->getUsers(),
            'selectedUser' => User::find($request->receiver_id),
            'messages' => $this->getMessages(User::find($request->receiver_id)),
        ]);
    }
}
