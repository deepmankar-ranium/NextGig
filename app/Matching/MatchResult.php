<?php

namespace App\Matching;

final class MatchResult
{
    /**
     * @param  list<string>  $matched
     * @param  list<string>  $missing
     */
    public function __construct(
        public readonly ?int $score,
        public readonly array $matched,
        public readonly array $missing,
        public readonly string $inputsHash,
    ) {
    }
}
