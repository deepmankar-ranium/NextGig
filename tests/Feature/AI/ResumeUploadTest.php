<?php

namespace Tests\Feature\AI;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUsers;
use Tests\Concerns\FakesAi;
use Tests\Support\Pdf;
use Tests\TestCase;

class ResumeUploadTest extends TestCase
{
    use RefreshDatabase, CreatesUsers, FakesAi;

    private function resumeFor(User $user): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('resume.pdf', Pdf::withText([
            $user->full_name.' | '.$user->email.' | +91 98765 43210',
            'Senior Laravel Developer at Acme, 2019 - 2025',
            'Skills: PHP, Laravel, VueJS, MySQL, Docker',
        ]));
    }

    private function validProfile(): array
    {
        return [
            'skills' => ['PHP', 'Laravel', 'VueJS', 'MySQL', 'Docker', 'php'],
            'years_experience' => 6,
            'roles' => ['Senior Laravel Developer'],
            'summary' => 'Backend developer with six years of Laravel experience.',
        ];
    }

    public function test_upload_parses_and_stores_a_reviewable_profile(): void
    {
        Storage::fake('local');
        $ai = $this->fakeAi()->pushJson($this->validProfile());
        $seeker = $this->seekerUser();

        $this->actingAs($seeker)
            ->post('/resume', ['resume' => $this->resumeFor($seeker)])
            ->assertRedirect('/resume')
            ->assertSessionHasNoErrors();

        $js = $seeker->jobseeker->fresh();
        $this->assertSame(['php', 'laravel', 'vue.js', 'mysql', 'docker'], $js->profile['skills']);
        $this->assertSame(6.0, (float) $js->profile['years_experience']);
        $this->assertStringContainsString('Senior Laravel Developer', $js->resume_text);
        Storage::disk('local')->assertExists($js->resume);

        $this->actingAs($seeker)->get('/resume')->assertInertia(fn (Assert $p) => $p
            ->component('Resume/Edit')
            ->where('hasResume', true)
            ->where('profile.skills.1', 'laravel'));

        $sent = $ai->sentText();
        $this->assertStringContainsString('Senior Laravel Developer', $sent);
        $this->assertStringNotContainsString($seeker->email, $sent);
        $this->assertStringNotContainsString($seeker->full_name, $sent);
        $this->assertStringNotContainsString('98765', $sent);
    }

    public function test_bad_pdf_is_rejected_with_a_friendly_error(): void
    {
        Storage::fake('local');
        $ai = $this->fakeAi();
        $seeker = $this->seekerUser();

        $this->actingAs($seeker)
            ->from('/resume')
            ->post('/resume', ['resume' => UploadedFile::fake()->createWithContent('resume.pdf', '%PDF-1.4 garbage')])
            ->assertRedirect('/resume')
            ->assertSessionHasErrors('resume');

        $this->assertNull($seeker->jobseeker->fresh()->profile);
        $this->assertSame([], $ai->calls);
    }

    public function test_provider_timeout_saves_nothing_and_shows_friendly_error(): void
    {
        Storage::fake('local');
        $this->fakeAi()->pushFailure('timeout');
        $seeker = $this->seekerUser();

        $this->actingAs($seeker)->from('/resume')
            ->post('/resume', ['resume' => $this->resumeFor($seeker)])
            ->assertSessionHasErrors(['resume' => 'The AI service is busy right now. Please try again in a minute.']);

        $this->assertNull($seeker->jobseeker->fresh()->profile);
        $this->assertEmpty(Storage::disk('local')->allFiles());
    }

    public function test_invalid_ai_output_is_never_shown_or_saved(): void
    {
        Storage::fake('local');
        $this->fakeAi()->pushJson(['skills' => 'not-a-list', 'years_experience' => 'lots', 'roles' => [], 'summary' => '<script>']);
        $seeker = $this->seekerUser();

        $response = $this->actingAs($seeker)->from('/resume')
            ->post('/resume', ['resume' => $this->resumeFor($seeker)])
            ->assertSessionHasErrors('resume');

        $this->assertStringNotContainsString('not-a-list', session('errors')->first('resume'));
        $this->assertNull($seeker->jobseeker->fresh()->profile);
    }

    public function test_non_pdf_upload_fails_validation(): void
    {
        $this->fakeAi();

        $this->actingAs($this->seekerUser())
            ->post('/resume', ['resume' => UploadedFile::fake()->create('resume.docx', 10, 'application/msword')])
            ->assertSessionHasErrors('resume');
    }

    public function test_seeker_can_edit_their_parsed_profile(): void
    {
        $seeker = $this->seekerUser();

        $this->actingAs($seeker)->put('/resume/profile', [
            'skills' => ['React.js', 'TypeScript', 'react'],
            'years_experience' => 3.5,
            'roles' => ['Frontend Developer'],
            'summary' => 'Frontend dev.',
        ])->assertRedirect('/resume');

        $profile = $seeker->jobseeker->fresh()->profile;
        $this->assertSame(['react', 'typescript'], $profile['skills']);
        $this->assertSame(3.5, (float) $profile['years_experience']);
    }

    public function test_employers_cannot_reach_resume_pages(): void
    {
        $this->fakeAi();
        $employer = $this->employerUser();

        $this->actingAs($employer)->get('/resume')->assertForbidden();
        $this->actingAs($employer)->post('/resume', ['resume' => UploadedFile::fake()->create('r.pdf', 1, 'application/pdf')])->assertForbidden();
        $this->actingAs($employer)->put('/resume/profile', ['skills' => [], 'years_experience' => 1, 'roles' => []])->assertForbidden();
    }

    public function test_profile_is_not_exposed_to_employers_through_applications(): void
    {
        $seeker = $this->seekerUser();
        $seeker->jobseeker->update(['profile' => ['skills' => ['secret-skill']], 'resume_text' => 'PRIVATE RESUME TEXT']);

        $json = $seeker->jobseeker->fresh()->toJson();
        $this->assertStringNotContainsString('PRIVATE RESUME TEXT', $json);
        $this->assertStringNotContainsString('secret-skill', $json);
    }

    public function test_ai_daily_cap_returns_friendly_429(): void
    {
        config(['ai.daily_cap' => 2, 'ai.per_minute' => 100]);
        $this->fakeAi();
        $user = $this->seekerUser();

        $this->actingAs($user)->postJson('/chat', ['message' => 'one'])->assertOk();
        $this->actingAs($user)->postJson('/chat', ['message' => 'two'])->assertOk();
        $this->actingAs($user)->postJson('/chat', ['message' => 'three'])
            ->assertStatus(429)
            ->assertJson(['status' => 'error', 'message' => "You've reached the AI usage limit. Please try again later."]);
    }
}
