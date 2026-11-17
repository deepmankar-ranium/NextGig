<?php

namespace App\Actions\Resume;

use App\AI\AiException;
use App\Events\SeekerProfileUpdated;
use App\Models\Jobseeker;
use App\Resume\ResumeProfileParser;
use App\Resume\ResumeTextExtractor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class UploadResumeAction
{
    public function __construct(
        private readonly ResumeTextExtractor $extractor,
        private readonly ResumeProfileParser $parser,
    ) {
    }

    /**
     * Resume page: extract text, parse it with AI, store the file. Nothing is saved unless
     * every step succeeds, so the seeker can review a complete profile.
     *
     * @throws \App\Resume\ResumeException
     * @throws AiException
     */
    public function execute(Jobseeker $seeker, UploadedFile $file): Jobseeker
    {
        $text = $this->extractor->extract($file->getRealPath());
        $profile = $this->parser->parse($text, $this->identifiers($seeker));

        $this->store($seeker, $file, $text);
        $this->saveProfile($seeker, $profile);

        return $seeker;
    }

    /**
     * Apply panel: the PDF must be readable, but AI parsing is best-effort. An outage or a
     * missing API key must never block someone from applying.
     *
     * @throws \App\Resume\ResumeException
     */
    public function storeForApplication(Jobseeker $seeker, UploadedFile $file): Jobseeker
    {
        $text = $this->extractor->extract($file->getRealPath());
        $this->store($seeker, $file, $text);

        try {
            $this->saveProfile($seeker, $this->parser->parse($text, $this->identifiers($seeker)));
        } catch (AiException $e) {
            Log::info('resume.parse_skipped', ['jobseeker_id' => $seeker->id, 'reason' => $e->reason]);
        }

        return $seeker;
    }

    private function store(Jobseeker $seeker, UploadedFile $file, string $text): void
    {
        $seeker->update([
            'resume' => $file->storeAs('resumes', "seeker-{$seeker->id}.pdf", 'local'),
            'resume_text' => $text,
        ]);
    }

    private function saveProfile(Jobseeker $seeker, array $profile): void
    {
        $seeker->update([
            'profile' => $profile,
            'skills' => implode(', ', $profile['skills']),
            'profile_updated_at' => now(),
        ]);

        SeekerProfileUpdated::dispatch($seeker);
    }

    private function identifiers(Jobseeker $seeker): array
    {
        return [$seeker->user->full_name, $seeker->user->email, $seeker->email];
    }
}
