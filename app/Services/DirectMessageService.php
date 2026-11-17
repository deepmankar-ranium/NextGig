<?php

namespace App\Services;

use App\Models\DirectMessage;
use App\Models\User;
use Illuminate\Support\Collection;

class DirectMessageService
{
    /**
     * People the user has a visible conversation with, plus the user they are about to
     * message (if any). Only public fields are returned: no emails.
     */
    public function getUsersForInbox(User $me, ?User $selected = null): Collection
    {
        $partnerIds = DirectMessage::query()
            ->visibleTo($me->id)
            ->where(fn ($q) => $q->where('sender_id', $me->id)->orWhere('receiver_id', $me->id))
            ->get(['sender_id', 'receiver_id'])
            ->toBase()
            ->map(fn (DirectMessage $m) => $m->sender_id === $me->id ? $m->receiver_id : $m->sender_id)
            ->unique();

        if ($selected && $selected->id !== $me->id) {
            $partnerIds->push($selected->id);
        }

        return User::with('role')
            ->whereIn('id', $partnerIds->unique()->values())
            ->orderBy('full_name')
            ->get()
            ->map(fn (User $user) => $this->publicProfile($user));
    }

    /**
     * Messages between two users that $viewer has not hidden.
     */
    public function getMessagesBetween(User $viewer, User $other): Collection
    {
        return DirectMessage::between($viewer->id, $other->id)
            ->visibleTo($viewer->id)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * @return array{id:int, name:string, role:string}
     */
    public function publicProfile(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->full_name,
            'role' => $user->role->name ?? 'N/A',
        ];
    }
}
