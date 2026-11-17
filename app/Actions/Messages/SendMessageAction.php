<?php

namespace App\Actions\Messages;

use App\Events\ChatMessageSent;
use App\Jobs\EmailUnreadMessage;
use App\Models\DirectMessage;
use App\Models\User;

class SendMessageAction
{
    /** How long a message must stay unread before the receiver gets an email about it. */
    public const EMAIL_AFTER_MINUTES = 15;

    public function execute(User $sender, int $receiverId, string $messageText, ?int $jobListingId = null): DirectMessage
    {
        $message = DirectMessage::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiverId,
            'job_listing_id' => $jobListingId,
            'message' => $messageText,
        ]);

        ChatMessageSent::dispatch($message, $sender);

        EmailUnreadMessage::dispatch($message->id)->delay(now()->addMinutes(self::EMAIL_AFTER_MINUTES));

        return $message;
    }
}
