<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * The app's own login / registration flow (replaces the unused Breeze scaffolding tests).
 */
class AuthFlowTest extends TestCase
{
    use RefreshDatabase, CreatesUsers;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/home')->assertRedirect('/login');
    }

    public function test_user_can_log_in_and_out(): void
    {
        $user = $this->seekerUser();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = $this->seekerUser();

        $this->post('/login', ['email' => $user->email, 'password' => 'nope'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_job_seeker_can_register_in_two_steps(): void
    {
        Event::fake();
        $this->seedRoles();

        $this->post('/register/store-role', ['role_id' => 3])->assertRedirect(route('register-2'));
        $this->post('/register-2/register', [
            'full_name' => 'Asha Rao',
            'email' => 'asha@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertRedirect('/home');

        $user = User::where('email', 'asha@example.com')->firstOrFail();
        $this->assertSame('Job Seeker', $user->role->name);
        $this->assertNotNull($user->jobseeker);
        $this->assertAuthenticatedAs($user);
    }

    public function test_employer_registration_creates_employer_profile(): void
    {
        Event::fake();
        $this->seedRoles();

        $this->post('/register/store-role', ['role_id' => 2]);
        $this->post('/register-2/register', [
            'full_name' => 'Ravi Shah',
            'email' => 'ravi@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'name' => 'Acme Ltd',
        ])->assertRedirect('/home');

        $user = User::where('email', 'ravi@example.com')->firstOrFail();
        $this->assertSame('Employer', $user->role->name);
        $this->assertSame('Acme Ltd', $user->employer->name);
    }

    public function test_registration_requires_a_role_first(): void
    {
        $this->post('/register-2/register', [
            'full_name' => 'No Role',
            'email' => 'norole@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertSessionHasErrors('role_id');

        $this->assertDatabaseMissing('users', ['email' => 'norole@example.com']);
    }
}
