<?php

namespace Tests\Feature\Employer;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\Concerns\FakesAi;
use Tests\TestCase;

class JobDescriptionDraftTest extends TestCase
{
    use RefreshDatabase, CreatesUsers, FakesAi;

    private function draftResponse(): array
    {
        return [
            'overview' => 'Build internal tools with Laravel and Vue.',
            'responsibilities' => ['Ship features', 'Review code'],
            'requirements' => ['3+ years with PHP'],
            'skills' => ['Laravel', 'VueJS', 'Redis'],
        ];
    }

    public function test_draft_returns_text_and_tag_suggestions_without_creating_a_job(): void
    {
        $ai = $this->fakeAi()->pushJson($this->draftResponse());
        $laravel = Tag::create(['name' => 'laravel']);
        $vue = Tag::create(['name' => 'Vue.js']);
        $employer = $this->employerUser();

        $this->actingAs($employer)->postJson('/Jobs/draft', [
            'title' => 'Backend Developer',
            'notes' => 'Laravel, Vue, mentor juniors',
        ])
            ->assertOk()
            ->assertJsonPath('draft.tag_ids', [$laravel->id, $vue->id])
            ->assertJsonPath('draft.new_skills', ['redis'])
            ->assertJsonPath('draft.description', "Build internal tools with Laravel and Vue.\n\nResponsibilities:\n- Ship features\n- Review code\n\nRequirements:\n- 3+ years with PHP");

        $this->assertDatabaseCount('job_listings', 0);

        $sent = $ai->sentText();
        $this->assertStringContainsString('Backend Developer', $sent);
        $this->assertStringNotContainsString($employer->email, $sent);
        $this->assertStringNotContainsString($employer->full_name, $sent);
    }

    public function test_job_seekers_cannot_draft(): void
    {
        $this->fakeAi();

        $this->actingAs($this->seekerUser())
            ->postJson('/Jobs/draft', ['title' => 'x', 'notes' => 'y'])
            ->assertForbidden();
    }

    public function test_invalid_ai_output_gives_friendly_error(): void
    {
        $this->fakeAi()->pushJson(['overview' => '', 'responsibilities' => 'nope']);

        $this->actingAs($this->employerUser())
            ->postJson('/Jobs/draft', ['title' => 'Dev', 'notes' => 'stuff'])
            ->assertStatus(503)
            ->assertJsonPath('status', 'error');
    }
}
