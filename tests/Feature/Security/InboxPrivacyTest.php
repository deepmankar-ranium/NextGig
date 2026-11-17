<?php

namespace Tests\Feature\Security;

use App\Models\DirectMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class InboxPrivacyTest extends TestCase
{
    use RefreshDatabase, CreatesUsers;

    public function test_inbox_lists_only_conversation_partners_without_emails(): void
    {
        $me = $this->seekerUser();
        $partner = $this->employerUser();
        $stranger = $this->employerUser();

        DirectMessage::create(['sender_id' => $partner->id, 'receiver_id' => $me->id, 'message' => 'Hi']);

        $this->actingAs($me)
            ->get('/messages')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Messages/Inbox')
                ->has('users', 1)
                ->where('users.0.id', $partner->id)
                ->missing('users.0.email')
            );

        $this->assertStringNotContainsString($stranger->email, $this->get('/messages')->getContent());
    }

    public function test_opening_a_new_conversation_shows_that_user_without_email(): void
    {
        $me = $this->seekerUser();
        $employer = $this->employerUser();

        $this->actingAs($me)
            ->get("/messages/{$employer->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('users', 1)
                ->where('users.0.id', $employer->id)
                ->where('selectedUser.id', $employer->id)
                ->missing('selectedUser.email')
                ->missing('users.0.email')
            );
    }

    public function test_job_page_does_not_expose_the_employers_login_email(): void
    {
        $employer = $this->employerUser();
        $job = $this->jobFor($employer);

        $this->actingAs($this->seekerUser())
            ->get("/Jobs/job/{$job->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('job.employer.user_id', $employer->id)
                ->missing('job.employer.user')
            );
    }

    public function test_user_cannot_message_themselves(): void
    {
        $me = $this->seekerUser();

        $this->actingAs($me)
            ->post('/messages', ['receiver_id' => $me->id, 'message' => 'hello me'])
            ->assertSessionHasErrors('receiver_id');

        $this->assertDatabaseCount('direct_messages', 0);
    }
}
