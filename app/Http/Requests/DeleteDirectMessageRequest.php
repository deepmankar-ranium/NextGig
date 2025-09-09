<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DeleteDirectMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Get the sender and receiver IDs from the route parameters.
        $senderId = $this->route('sender_id');
        $receiverId = $this->route('receiver_id');
        $currentUserId = Auth::id();

        // The authenticated user must be either the sender or the receiver.
        return $currentUserId == $senderId || $currentUserId == $receiverId;
    }
}
