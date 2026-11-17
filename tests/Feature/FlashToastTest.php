<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class FlashToastTest extends TestCase
{
    use RefreshDatabase, CreatesUsers;

    public function test_success_flash_reaches_the_page_with_an_id(): void
    {
        $employer = $this->employerUser();
        $job = $this->jobFor($employer);

        $this->actingAs($employer)->delete("/jobs/{$job->id}")->assertRedirect('/jobs');

        $this->actingAs($employer)->get('/jobs')->assertInertia(fn (Assert $page) => $page
            ->where('flash.success', 'Job deleted successfully!')
            ->whereType('flash.id', 'string'));
    }

    public function test_pages_without_a_toast_have_no_flash_id(): void
    {
        $this->actingAs($this->seekerUser())->get('/jobs')
            ->assertInertia(fn (Assert $page) => $page->where('flash.id', null)->where('flash.success', null));
    }

    public function test_rate_limited_inertia_form_goes_back_with_an_error_toast(): void
    {
        $employer = $this->employerUser();
        $this->jobFor($employer);
        $seeker = $this->seekerUser();

        $limited = null;
        for ($i = 0; $i < 80 && ! $limited; $i++) {
            $res = $this->actingAs($seeker)->from('/messages')
                ->withHeader('X-Inertia', 'true')
                ->post('/messages', ['receiver_id' => $employer->id, 'message' => "hi {$i}"]);
            if ($res->isRedirect() && session('error')) {
                $limited = $res;
            }
        }

        $this->assertNotNull($limited, 'expected the writes limiter to kick in');
        $limited->assertRedirect('/messages')->assertSessionHas('error', "You're doing that too often. Please wait a minute and try again.");
    }

    public function test_forbidden_inertia_form_goes_back_with_an_error_toast(): void
    {
        $job = $this->jobFor($this->employerUser());

        $this->actingAs($this->seekerUser())->from("/jobs/{$job->id}")
            ->withHeader('X-Inertia', 'true')
            ->delete("/jobs/{$job->id}")
            ->assertRedirect("/jobs/{$job->id}")
            ->assertSessionHas('error', "You don't have permission to do that.");
    }

    public function test_non_inertia_requests_keep_normal_error_responses(): void
    {
        $job = $this->jobFor($this->employerUser());

        $this->actingAs($this->seekerUser())->delete("/jobs/{$job->id}")->assertForbidden();
    }
}
