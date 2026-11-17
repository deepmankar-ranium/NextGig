<?php

namespace Tests\Feature;

use App\Events\ChatMessageSent;
use App\Models\DirectMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class InboxConversationsTest extends TestCase
{
    use RefreshDatabase, CreatesUsers;

    public function test_list_has_previews_unread_counts_and_newest_first(): void
    {
        $me = $this->seekerUser();
        $old = $this->employerUser();
        $recent = $this->employerUser();

        DirectMessage::create(['sender_id' => $old->id, 'receiver_id' => $me->id, 'message' => 'Older chat']);
        $this->travel(5)->minutes();
        DirectMessage::create(['sender_id' => $recent->id, 'receiver_id' => $me->id, 'message' => 'First']);
        DirectMessage::create(['sender_id' => $recent->id, 'receiver_id' => $me->id, 'message' => 'Are you free Tuesday?']);

        $this->actingAs($me)->get('/messages')
            ->assertInertia(fn (Assert $p) => $p
                ->where('users.0.id', $recent->id)
                ->where('users.0.last_message', 'Are you free Tuesday?')
                ->where('users.0.last_from_me', false)
                ->where('users.0.unread', 2)
                ->where('users.1.id', $old->id)
                ->missing('users.0.email'));
    }

    public function test_opening_a_conversation_marks_it_read(): void
    {
        $me = $this->seekerUser();
        $them = $this->employerUser();
        DirectMessage::create(['sender_id' => $them->id, 'receiver_id' => $me->id, 'message' => 'Hi']);

        $this->actingAs($me)->get("/messages/{$them->id}")
            ->assertInertia(fn (Assert $p) => $p->where('users.0.unread', 0));

        $this->assertNotNull(DirectMessage::first()->read_at);
        $this->actingAs($me)->getJson('/api/unread-messages-count')->assertJson(['unread_count' => 0]);
    }

    public function test_opening_does_not_mark_my_own_messages_read(): void
    {
        $me = $this->seekerUser();
        $them = $this->employerUser();
        DirectMessage::create(['sender_id' => $me->id, 'receiver_id' => $them->id, 'message' => 'Hello']);

        $this->actingAs($me)->get("/messages/{$them->id}");

        $this->assertNull(DirectMessage::first()->read_at);
    }

    public function test_realtime_message_payload_has_no_email_or_account_data(): void
    {
        $sender = $this->seekerUser();
        $message = DirectMessage::create(['sender_id' => $sender->id, 'receiver_id' => $this->employerUser()->id, 'message' => 'Hi']);

        $payload = (new ChatMessageSent($message, $sender))->broadcastWith();

        $this->assertSame(['id' => $sender->id, 'full_name' => $sender->full_name], $payload['user']);
        $this->assertStringNotContainsString($sender->email, json_encode($payload));
        $this->assertArrayNotHasKey('sender_hidden_at', $payload['message']);
    }
}
