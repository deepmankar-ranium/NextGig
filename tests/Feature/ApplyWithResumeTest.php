<?php

namespace Tests\Feature;

use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUsers;
use Tests\Concerns\FakesAi;
use Tests\Support\Pdf;
use Tests\TestCase;

class ApplyWithResumeTest extends TestCase
{
    use RefreshDatabase, CreatesUsers, FakesAi;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('uploads');
    }

    private function pdf(string ...$lines): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('my-resume.pdf', Pdf::withText($lines ?: ['Neha Kapoor', 'Backend developer, 4 years', 'Skills: PHP, Laravel, MySQL']));
    }

    public function test_seeker_with_resume_on_file_applies_in_one_click_without_cover_letter(): void
    {
        $seeker = $this->withResumeOnFile($this->seekerUser());
        $job = $this->jobFor($this->employerUser());

        $this->actingAs($seeker)->post("/apply/{$job->id}")->assertRedirect()->assertSessionHasNoErrors();

        $application = Application::firstOrFail();
        $this->assertSame('', $application->cover_letter);
        $this->assertStringContainsString('Laravel developer', $application->resume_text);
        Storage::disk('uploads')->assertExists($application->resume_path);
    }

    public function test_seeker_without_a_resume_must_upload_one(): void
    {
        $seeker = $this->seekerUser();
        $job = $this->jobFor($this->employerUser());

        $this->actingAs($seeker)->post("/apply/{$job->id}", ['cover_letter' => 'Hi'])
            ->assertSessionHasErrors(['resume' => 'Add your resume (PDF) to apply.']);

        $this->assertDatabaseCount('applications', 0);
    }

    public function test_uploading_in_the_apply_panel_saves_it_to_the_profile_even_if_ai_is_down(): void
    {
        $this->fakeAi()->pushFailure('unavailable');
        $seeker = $this->seekerUser();
        $job = $this->jobFor($this->employerUser());

        $this->actingAs($seeker)->post("/apply/{$job->id}", ['resume' => $this->pdf()])->assertSessionHasNoErrors();

        $js = $seeker->jobseeker->fresh();
        Storage::disk('uploads')->assertExists($js->resume);
        $this->assertStringContainsString('Backend developer', $js->resume_text);
        $this->assertNull($js->profile, 'AI was down, so no parsed profile, but the application still went through');
        $this->assertSame(1, Application::count());
    }

    public function test_uploading_parses_the_profile_when_ai_works(): void
    {
        $this->fakeAi()->pushJson(['skills' => ['PHP', 'Laravel'], 'years_experience' => 4, 'roles' => ['Backend developer'], 'summary' => 'Backend dev.']);
        $seeker = $this->seekerUser();

        $this->actingAs($seeker)->post("/apply/{$this->jobFor($this->employerUser())->id}", ['resume' => $this->pdf()]);

        $this->assertSame(['php', 'laravel'], $seeker->jobseeker->fresh()->profile['skills']);
    }

    public function test_unreadable_or_non_pdf_resume_is_rejected(): void
    {
        $seeker = $this->seekerUser();
        $job = $this->jobFor($this->employerUser());

        $this->actingAs($seeker)->post("/apply/{$job->id}", ['resume' => UploadedFile::fake()->create('cv.docx', 10, 'application/msword')])
            ->assertSessionHasErrors(['resume' => 'Your resume must be a PDF.']);

        $this->actingAs($seeker)->post("/apply/{$job->id}", ['resume' => UploadedFile::fake()->createWithContent('cv.pdf', '%PDF-1.4 junk')])
            ->assertSessionHasErrors('resume');

        $this->assertDatabaseCount('applications', 0);
    }

    public function test_application_keeps_its_own_copy_when_the_seeker_replaces_their_resume(): void
    {
        $seeker = $this->withResumeOnFile($this->seekerUser(), "Version one\nOld resume text here");
        $this->actingAs($seeker)->post("/apply/{$this->jobFor($this->employerUser())->id}");
        $application = Application::firstOrFail();
        $original = Storage::disk('uploads')->get($application->resume_path);

        $this->withResumeOnFile($seeker, "Version two\nCompletely new resume");

        $this->assertSame($original, Storage::disk('uploads')->get($application->fresh()->resume_path));
        $this->assertStringContainsString('Version one', $application->fresh()->resume_text);
    }

    public function test_owner_opens_the_pdf_which_marks_viewed_others_cannot(): void
    {
        $owner = $this->employerUser();
        $seeker = $this->withResumeOnFile($this->seekerUser());
        $this->actingAs($seeker)->post("/apply/{$this->jobFor($owner)->id}");
        $application = Application::firstOrFail();

        $this->actingAs($this->employerUser())->get("/applications/{$application->id}/resume")->assertForbidden();
        $this->assertSame('applied', $application->fresh()->application_status);

        $this->actingAs($owner)->get("/applications/{$application->id}/resume")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
        $this->assertSame('viewed', $application->fresh()->application_status);

        $this->actingAs($seeker)->get("/applications/{$application->id}/resume")->assertOk();
    }

    public function test_job_page_tells_the_apply_panel_about_the_resume_on_file(): void
    {
        $job = $this->jobFor($this->employerUser());
        $without = $this->seekerUser();
        $with = $this->withResumeOnFile($this->seekerUser());

        $this->actingAs($without)->get("/jobs/{$job->id}")->assertInertia(fn (Assert $p) => $p->where('resumeOnFile', null));
        $this->actingAs($with)->get("/jobs/{$job->id}")->assertInertia(fn (Assert $p) => $p->has('resumeOnFile.updated_at'));
    }

    public function test_resume_storage_path_is_never_sent_to_the_browser(): void
    {
        $owner = $this->employerUser();
        $seeker = $this->withResumeOnFile($this->seekerUser());
        $this->actingAs($seeker)->post("/apply/{$this->jobFor($owner)->id}");

        $this->actingAs($owner)->get('/view-applications')->assertInertia(fn (Assert $p) => $p
            ->where('applications.0.has_resume_file', true)
            ->missing('applications.0.resume_path'));
    }
}
