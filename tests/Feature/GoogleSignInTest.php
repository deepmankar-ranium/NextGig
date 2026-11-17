<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class GoogleSignInTest extends TestCase
{
    use RefreshDatabase, CreatesUsers;

    private function googleReturns(string $email, string $id = 'g-123', string $name = 'Neha Kapoor'): void
    {
        $user = (new GoogleUser())->map(['id' => $id, 'name' => $name, 'email' => $email]);
        $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('user')->andReturn($user);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_existing_user_signs_in_and_google_id_is_linked(): void
    {
        $user = $this->seekerUser();
        $user->forceFill(['google_id' => null])->save();
        $this->googleReturns($user->email, 'g-999');

        $this->get('/auth/google/callback')->assertRedirect('/home');

        $this->assertAuthenticatedAs($user);
        $this->assertSame('g-999', $user->fresh()->google_id);
    }

    public function test_new_user_without_a_role_is_asked_to_pick_one_then_account_is_created(): void
    {
        $this->seedRoles();
        $this->googleReturns('neha@example.com');

        $this->get('/auth/google/callback')->assertRedirect('/register');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'neha@example.com']);

        $this->post('/register/store-role', ['role_id' => 2])->assertRedirect('/home');

        $user = User::where('email', 'neha@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('Employer', $user->role->name);
        $this->assertNotNull($user->employer);
        $this->assertSame('g-123', $user->google_id);
    }

    public function test_new_user_who_already_picked_a_role_is_created_straight_away(): void
    {
        $this->seedRoles();
        $this->post('/register/store-role', ['role_id' => 3]);
        $this->googleReturns('ravi@example.com');

        $this->get('/auth/google/callback')->assertRedirect('/home');

        $user = User::where('email', 'ravi@example.com')->firstOrFail();
        $this->assertSame('Job Seeker', $user->role->name);
        $this->assertNotNull($user->jobseeker);
    }

    public function test_google_errors_show_a_message_instead_of_failing_silently(): void
    {
        $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('user')->andThrow(new InvalidStateException());
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get('/auth/google/callback')
            ->assertRedirect('/login')
            ->assertSessionHasErrors('google');
        $this->assertGuest();
    }

    public function test_redirect_explains_when_google_is_not_configured(): void
    {
        config(['services.google.client_id' => null]);

        $this->get('/auth/google')->assertRedirect('/login')->assertSessionHasErrors('google');
    }

    public function test_callback_url_follows_the_current_host_by_default(): void
    {
        config(['services.google.redirect' => '/auth/google/callback', 'services.google.client_id' => 'x', 'services.google.client_secret' => 'y']);

        $location = $this->get('/auth/google')->headers->get('Location');

        $this->assertStringContainsString(urlencode(url('/auth/google/callback')), $location);
    }
}
