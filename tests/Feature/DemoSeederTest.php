<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\JobMatch;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seed_creates_working_demo_accounts(): void
    {
        $this->seed(DemoSeeder::class);
        $this->seed(DemoSeeder::class); // idempotent

        $seeker = User::where('email', 'seeker@nextgig.test')->firstOrFail();
        $employer = User::where('email', 'employer@nextgig.test')->firstOrFail();

        $this->post('/login', ['email' => $seeker->email, 'password' => DemoSeeder::PASSWORD])->assertRedirect('/home');
        $this->get('/applied-jobs')->assertOk();
        $this->post('/logout');

        $this->post('/login', ['email' => $employer->email, 'password' => DemoSeeder::PASSWORD])->assertRedirect('/home');
        $this->get('/view-applications')->assertOk();

        $this->assertSame(6, Application::count());
        $this->assertGreaterThan(0, JobMatch::whereNotNull('score')->count());
        $this->assertSame('shortlisted', $seeker->jobseeker->applications()->orderBy('id')->first()->application_status);
    }

    public function test_reset_command_refuses_outside_demo_mode(): void
    {
        $this->app['env'] = 'production';
        config(['app.demo_mode' => false]);

        $this->artisan('demo:reset --force')->assertFailed();
    }
}
