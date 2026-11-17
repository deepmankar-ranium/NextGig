<?php

namespace Tests\Unit\Matching;

use App\Matching\MatchScorer;
use App\Resume\SkillNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Golden set for the deterministic scorer. Run after any change to weights or normalisation.
 */
class MatchScorerTest extends TestCase
{
    private function scorer(): MatchScorer
    {
        return new MatchScorer(new SkillNormalizer());
    }

    public static function golden(): array
    {
        return [
            'perfect skills, no min years' => [['php', 'laravel', 'vue'], 5, ['PHP', 'Laravel', 'Vue.js'], null, 100],
            'half skills, no min years' => [['php', 'react'], 5, ['php', 'laravel'], null, 50],
            'aliases count as the same skill' => [['JS', 'NodeJS', 'Postgres'], 2, ['javascript', 'node.js', 'postgresql'], null, 100],
            'no overlap' => [['python'], 10, ['php', 'laravel'], null, 0],
            'all skills, meets years' => [['php', 'laravel'], 5, ['php', 'laravel'], 3, 100],
            'all skills, half the years' => [['php', 'laravel'], 1.5, ['php', 'laravel'], 3, 90],
            'half skills, meets years' => [['php'], 4, ['php', 'laravel'], 3, 60],
            'half skills, zero years' => [['php'], 0, ['php', 'laravel'], 4, 40],
            'one of three, rounding' => [['docker'], null, ['docker', 'aws', 'kubernetes'], null, 33],
            'extra seeker skills do not lower score' => [['php', 'laravel', 'go', 'rust', 'c#'], 3, ['php'], null, 100],
            'job lists no skills' => [['php'], 3, [], 2, null],
            'seeker has no skills' => [[], 3, ['php'], 2, null],
        ];
    }

    #[DataProvider('golden')]
    public function test_golden_set(array $have, ?float $years, array $need, ?int $minYears, ?int $expected): void
    {
        $this->assertSame($expected, $this->scorer()->score($have, $years, $need, $minYears)->score);
    }

    public function test_score_is_deterministic_and_lists_are_sorted(): void
    {
        $a = $this->scorer()->score(['vue', 'php'], 2, ['laravel', 'php', 'aws'], 3);
        $b = $this->scorer()->score(['php', 'Vue.js'], 2, ['aws', 'PHP', 'Laravel'], 3);

        $this->assertSame($a->score, $b->score);
        $this->assertSame($a->inputsHash, $b->inputsHash);
        $this->assertSame(['php'], $a->matched);
        $this->assertSame(['aws', 'laravel'], $a->missing);
    }
}
