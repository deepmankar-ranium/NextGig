<?php

namespace App\Services;

use App\Models\DirectMessage;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DirectMessageService
{
    /**
     * Conversations for the inbox list, newest first: the other person's public profile plus
     * a preview of the last visible message and how many are unread. No emails.
     * The user being opened ($selected) is included even before any message exists.
     */
    public function getUsersForInbox(User $me, ?User $selected = null): Collection
    {
        $messages = DirectMessage::query()
            ->visibleTo($me->id)
            ->where(fn ($q) => $q->where('sender_id', $me->id)->orWhere('receiver_id', $me->id))
            ->orderBy('id')
            ->get(['id', 'sender_id', 'receiver_id', 'message', 'read_at', 'created_at'])
            ->toBase()
            ->groupBy(fn (DirectMessage $m) => $m->sender_id === $me->id ? $m->receiver_id : $m->sender_id);

        $partnerIds = $messages->keys();
        if ($selected && $selected->id !== $me->id && ! $partnerIds->contains($selected->id)) {
            $partnerIds->push($selected->id);
        }

        $users = User::with('role')->whereIn('id', $partnerIds)->get()->keyBy('id');

        return $partnerIds
            ->filter(fn ($id) => $users->has($id))
            ->map(function ($id) use ($users, $messages, $me) {
                $thread = $messages->get($id, collect());
                $last = $thread->last();

                return [
                    ...$this->publicProfile($users[$id]),
                    'last_message' => $last ? Str::limit($last->message, 80) : null,
                    'last_from_me' => $last ? $last->sender_id === $me->id : false,
                    'last_at' => $last?->created_at?->toIso8601String(),
                    'unread' => $thread->filter(fn ($m) => $m->receiver_id === $me->id && $m->read_at === null)->count(),
                ];
            })
            ->sortByDesc(fn ($c) => $c['last_at'] ?? '9999') // a new, empty conversation goes first
            ->values();
    }

    /**
     * Messages between two users that $viewer has not hidden.
     */
    public function getMessagesBetween(User $viewer, User $other): Collection
    {
        return DirectMessage::between($viewer->id, $other->id)
            ->visibleTo($viewer->id)
            ->with('jobListing:id,title')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }

    /**
     * Opening a conversation reads it.
     */
    public function markConversationRead(User $viewer, User $other): void
    {
        DirectMessage::where('sender_id', $other->id)
            ->where('receiver_id', $viewer->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * @return array{id:int, name:string, role:string, photo_url:?string}
     */
    public function publicProfile(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->full_name,
            'role' => $user->role->name ?? 'N/A',
            'photo_url' => $user->photo_url,
        ];
    }
}
