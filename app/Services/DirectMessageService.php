<?php

namespace App\Services;

use App\Models\User;
use App\Models\DirectMessage;
use Illuminate\Support\Collection;

class DirectMessageService
{
    /**
     * Get all users formatted for the inbox.
     * In a real-world application, you might want to paginate this
     * or only show users with recent conversations.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getUsersForInbox(): Collection
    {
        return User::with('role')
            ->select('id', 'full_name', 'email', 'role_id')
            ->get()
            ->map(fn(User $user) => [
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role->name ?? 'N/A',
            ]);
    }

    /**
     * Get messages between two users.
     *
     * @param \App\Models\User $user1
     * @param \App\Models\User $user2
     * @return \Illuminate\Support\Collection
     */
    public function getMessagesBetween(User $user1, User $user2): Collection
    {
        return DirectMessage::where(function ($query) use ($user1, $user2) {
            $query->where('sender_id', $user1->id)
                  ->where('receiver_id', $user2->id);
        })->orWhere(function ($query) use ($user1, $user2) {
            $query->where('sender_id', $user2->id)
                  ->where('receiver_id', $user1->id);
        })->orderBy('created_at', 'asc')->get();
    }
}
