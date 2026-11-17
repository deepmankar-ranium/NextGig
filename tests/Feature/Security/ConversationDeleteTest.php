<?php

namespace Tests\Feature\Security;

use App\Models\DirectMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class ConversationDeleteTest extends TestCase
{
    use RefreshDatabase, CreatesUsers;

    private User $alice;
    private User $bob;

    protected function setUp(): void
    {
        parent::setUp();

        $this->alice = $this->seekerUser();
        $this->bob = $this->employerUser();

        DirectMessage::create(['sender_id' => $this->alice->id, 'receiver_id' => $this->bob->id, 'message' => 'Hello']);
        DirectMessage::create(['sender_id' => $this->bob->id, 'receiver_id' => $this->alice->id, 'message' => 'Hi back']);
    }

    private function deleteConversation(User $actor, User $other)
    {
        return $this->actingAs($actor)->post("/messages/delete/{$actor->id}/{$other->id}");
    }

    public function test_deleting_a_conversation_hides_it_only_for_me(): void
    {
        $this->deleteConversation($this->alice, $this->bob)->assertRedirect();

        $this->actingAs($this->alice)
            ->get("/messages/{$this->bob->id}")
            ->assertInertia(fn (Assert $page) => $page->has('messages', 0));

        $this->actingAs($this->alice)
            ->get('/messages')
            ->assertInertia(fn (Assert $page) => $page->has('users', 0));

        $this->actingAs($this->bob)
            ->get("/messages/{$this->alice->id}")
            ->assertInertia(fn (Assert $page) => $page->has('messages', 2)->has('users', 1));

        $this->assertDatabaseCount('direct_messages', 2);
    }

    public function test_new_messages_after_delete_are_visible_again(): void
    {
        $this->deleteConversation($this->alice, $this->bob);

        $this->actingAs($this->bob)
            ->post('/messages', ['receiver_id' => $this->alice->id, 'message' => 'Still there?']);

        $this->actingAs($this->alice)
            ->get("/messages/{$this->bob->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->has('messages', 1)
                ->where('messages.0.message', 'Still there?')
            );
    }

    public function test_hidden_unread_messages_no_longer_count_as_unread(): void
    {
        $this->deleteConversation($this->alice, $this->bob);

        $this->actingAs($this->alice)
            ->getJson('/api/unread-messages-count')
            ->assertJson(['unread_count' => 0]);
    }

    public function test_non_participant_cannot_delete_a_conversation(): void
    {
        $intruder = $this->seekerUser();

        $this->actingAs($intruder)
            ->post("/messages/delete/{$this->alice->id}/{$this->bob->id}")
            ->assertForbidden();

        $this->actingAs($this->alice)
            ->get("/messages/{$this->bob->id}")
            ->assertInertia(fn (Assert $page) => $page->has('messages', 2));
    }
}
