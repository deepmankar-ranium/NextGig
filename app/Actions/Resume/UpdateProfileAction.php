<?php

namespace App\Actions\Resume;

use App\Events\SeekerProfileUpdated;
use App\Models\Jobseeker;
use App\Resume\ResumeProfileParser;
use App\Resume\SkillNormalizer;

class UpdateProfileAction
{
    public function __construct(private readonly SkillNormalizer $skills)
    {
    }

    public function execute(Jobseeker $seeker, array $data): Jobseeker
    {
        $profile = ResumeProfileParser::clean($data, $this->skills);

        $seeker->update([
            'profile' => $profile,
            'skills' => implode(', ', $profile['skills']),
            'profile_updated_at' => now(),
        ]);

        SeekerProfileUpdated::dispatch($seeker);

        return $seeker;
    }
}
