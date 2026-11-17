<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectMessage extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
    ];

    /**
     * Per-user "deleted" markers are internal; never send them to the client.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'sender_hidden_at',
        'receiver_hidden_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'sender_hidden_at' => 'datetime',
        'receiver_hidden_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Messages between two users, in either direction.
     */
    public function scopeBetween(Builder $query, int $userA, int $userB): Builder
    {
        return $query->where(function (Builder $q) use ($userA, $userB) {
            $q->where(fn (Builder $q) => $q->where('sender_id', $userA)->where('receiver_id', $userB))
              ->orWhere(fn (Builder $q) => $q->where('sender_id', $userB)->where('receiver_id', $userA));
        });
    }

    /**
     * Messages the given user has not hidden from their own view.
     */
    public function scopeVisibleTo(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $q) use ($userId) {
            $q->where(fn (Builder $q) => $q->where('sender_id', $userId)->whereNull('sender_hidden_at'))
              ->orWhere(fn (Builder $q) => $q->where('receiver_id', $userId)->whereNull('receiver_hidden_at'));
        });
    }
}
