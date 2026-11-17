<?php

namespace App\Actions\Resume;

use App\Events\SeekerProfileUpdated;
use App\Models\Jobseeker;
use App\Resume\ResumeProfileParser;
use App\Resume\ResumeTextExtractor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadResumeAction
{
    public function __construct(
        private readonly ResumeTextExtractor $extractor,
        private readonly ResumeProfileParser $parser,
    ) {
    }

    /**
     * Extract text, parse it with AI, and store the file privately. Nothing is saved unless
     * every step succeeds.
     *
     * @throws \App\Resume\ResumeException
     * @throws \App\AI\AiException
     */
    public function execute(Jobseeker $seeker, UploadedFile $file): Jobseeker
    {
        $text = $this->extractor->extract($file->getRealPath());
        $profile = $this->parser->parse($text, [$seeker->user->full_name, $seeker->user->email, $seeker->email]);

        $path = $file->storeAs('resumes', "seeker-{$seeker->id}.pdf", 'local');

        $seeker->update([
            'resume' => $path,
            'resume_text' => $text,
            'profile' => $profile,
            'skills' => implode(', ', $profile['skills']),
            'profile_updated_at' => now(),
        ]);

        SeekerProfileUpdated::dispatch($seeker);

        return $seeker;
    }
}
