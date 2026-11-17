<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class GuestOnlyPagesTest extends TestCase
{
    use RefreshDatabase, CreatesUsers;

    public static function guestPages(): array
    {
        return [
            'login' => ['/login'],
            'register' => ['/register'],
            'register details' => ['/register-2'],
            'forgot password' => ['/forgot-password'],
            'reset password' => ['/reset-password/some-token'],
            'google sign-in' => ['/auth/google'],
            'landing' => ['/'],
        ];
    }

    /** @dataProvider guestPages */
    public function test_signed_in_users_are_sent_to_their_dashboard(string $url): void
    {
        $this->actingAs($this->seekerUser())->get($url)->assertRedirect('/home');
    }

    public function test_signed_in_users_cannot_post_login_or_register(): void
    {
        $user = $this->employerUser();

        $this->actingAs($user)->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/home');
        $this->actingAs($user)->post('/register-2/register', [])->assertRedirect('/home');
    }

    public function test_guests_still_see_the_pages(): void
    {
        foreach (['/login', '/register', '/forgot-password', '/'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_logout_requires_being_signed_in_and_works(): void
    {
        $this->post('/logout')->assertRedirect('/login');

        $this->actingAs($this->seekerUser())->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }
}
