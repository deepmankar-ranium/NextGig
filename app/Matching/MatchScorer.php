<?php

namespace App\Matching;

use App\Resume\SkillNormalizer;

/**
 * Deterministic match score, 0-100. No AI involved.
 *
 *   skills     = |job skills the seeker has| / |job skills|
 *   experience = min(1, seeker years / job min years)        (only if the job sets a minimum)
 *
 *   score = round(100 * skills)                              when the job has no minimum
 *   score = round(100 * (0.8 * skills + 0.2 * experience))   otherwise
 *
 * Skills are compared exactly after normalisation (lower-case + aliases in config/skills.php).
 * The score is null when the job lists no skills or the seeker has no profile skills: there
 * is nothing to compare, and "0%" would be misleading.
 */
class MatchScorer
{
    public const SKILL_WEIGHT = 0.8;
    public const EXPERIENCE_WEIGHT = 0.2;

    public function __construct(private readonly SkillNormalizer $normalizer)
    {
    }

    /**
     * @param  iterable<string>  $seekerSkills
     * @param  iterable<string>  $jobSkills
     */
    public function score(iterable $seekerSkills, ?float $seekerYears, iterable $jobSkills, ?int $minYears): MatchResult
    {
        $have = $this->normalizer->normalizeAll($seekerSkills);
        $need = $this->normalizer->normalizeAll($jobSkills);
        sort($have);
        sort($need);

        $matched = array_values(array_intersect($need, $have));
        $missing = array_values(array_diff($need, $have));

        $hash = hash('sha256', json_encode([$have, $seekerYears, $need, $minYears, self::SKILL_WEIGHT]));

        if ($need === [] || $have === []) {
            return new MatchResult(null, $matched, $missing, $hash);
        }

        $skills = count($matched) / count($need);

        if ($minYears === null || $minYears <= 0) {
            $score = $skills;
        } else {
            $experience = min(1.0, max(0.0, (float) $seekerYears) / $minYears);
            $score = self::SKILL_WEIGHT * $skills + self::EXPERIENCE_WEIGHT * $experience;
        }

        return new MatchResult((int) round($score * 100), $matched, $missing, $hash);
    }
}
