<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DirectMessageController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('unread-messages-count', [DirectMessageController::class, 'unreadCount']);
    Route::post('direct-messages/{directMessage}/read', [DirectMessageController::class, 'markAsRead']);
});
