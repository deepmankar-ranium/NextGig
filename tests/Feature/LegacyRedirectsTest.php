<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyRedirectsTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_capitalised_urls_redirect_permanently(): void
    {
        $this->get('/Home')->assertStatus(301)->assertRedirect('/home');
        $this->get('/Jobs')->assertStatus(301)->assertRedirect('/jobs');
        $this->get('/Jobs/create')->assertStatus(301)->assertRedirect('/jobs/create');
        $this->get('/Jobs/job/42')->assertStatus(301)->assertRedirect('/jobs/42');
        $this->get('/Jobs/job/42/edit')->assertStatus(301)->assertRedirect('/jobs/42/edit');
    }
}
