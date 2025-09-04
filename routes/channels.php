<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat', function ($user) {
    return Auth::check();
});

Broadcast::channel('chat.{user1Id}.{user2Id}', function ($user, $user1Id, $user2Id) {
    // Authorize that the current user is one of the participants in the chat
    return (int) $user->id === (int) $user1Id || (int) $user->id === (int) $user2Id;
});
