<?php

namespace App\Jobs;

use App\Messaging\MessagingRules;
use App\Models\DirectMessage;
use App\Notifications\NewDirectMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;

/**
 * Runs a while after a message is sent. If the receiver still hasn't read it (they weren't on
 * the site), email them once. At most one email per sender→receiver pair every few hours, so a
 * burst of messages doesn't become a burst of emails.
 */
class EmailUnreadMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public const THROTTLE_HOURS = 6;

    public function __construct(public int $messageId)
    {
    }

    public function handle(MessagingRules $rules): void
    {
        $message = DirectMessage::with(['sender', 'receiver', 'jobListing:id,title'])->find($this->messageId);

        if (! $message || $message->read_at || $message->receiver_hidden_at || ! $message->receiver || ! $message->sender) {
            return;
        }

        if ($rules->blockState($message->receiver, $message->sender) !== null) {
            return;
        }

        $key = "dm-email:{$message->receiver_id}:{$message->sender_id}";
        if (! Cache::add($key, true, now()->addHours(self::THROTTLE_HOURS))) {
            return;
        }

        $message->receiver->notify(new NewDirectMessage($message));
    }
}
