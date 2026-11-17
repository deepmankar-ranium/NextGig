<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class RegistrationRoleTest extends TestCase
{
    use RefreshDatabase, CreatesUsers;

    public function test_users_cannot_register_as_admin(): void
    {
        $this->seedRoles();

        $this->post('/register/store-role', ['role_id' => 1])
            ->assertSessionHasErrors('role_id');

        $this->assertNull(session('user_role'));
    }

    public function test_users_can_pick_employer_or_job_seeker(): void
    {
        $this->seedRoles();

        $this->post('/register/store-role', ['role_id' => 2])->assertRedirect(route('register-2'));
        $this->post('/register/store-role', ['role_id' => 3])->assertRedirect(route('register-2'));
    }
}
