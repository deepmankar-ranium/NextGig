<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase, CreatesUsers;

    public function test_login_is_throttled_after_five_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'nobody@example.com', 'password' => 'wrong'])
                ->assertStatus(302);
        }

        $this->post('/login', ['email' => 'nobody@example.com', 'password' => 'wrong'])
            ->assertStatus(429);
    }

    public function test_registration_is_throttled(): void
    {
        $this->seedRoles();

        for ($i = 0; $i < 10; $i++) {
            $this->post('/register/store-role', ['role_id' => 3])->assertStatus(302);
        }

        $this->post('/register/store-role', ['role_id' => 3])->assertStatus(429);
    }

    public function test_password_reset_link_is_throttled(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/send-reset-link', ['email' => 'nobody@example.com']);
        }

        $this->post('/send-reset-link', ['email' => 'nobody@example.com'])->assertStatus(429);
    }

    public function test_sending_messages_is_throttled(): void
    {
        $me = $this->seekerUser();
        $other = $this->employerUser();

        for ($i = 0; $i < 30; $i++) {
            $this->actingAs($me)
                ->post('/messages', ['receiver_id' => $other->id, 'message' => "msg {$i}"])
                ->assertStatus(302);
        }

        $this->actingAs($me)
            ->post('/messages', ['receiver_id' => $other->id, 'message' => 'one too many'])
            ->assertStatus(429);
    }

    public function test_ai_chat_is_throttled(): void
    {
        $me = $this->seekerUser();

        // The limiter runs before the controller, so the AI backend is never reached
        // once the limit is hit. Clear chat is cheap and shares the same limiter.
        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($me)->post('/clear-chat');
        }

        $this->actingAs($me)->post('/clear-chat')->assertStatus(429);
    }
}
