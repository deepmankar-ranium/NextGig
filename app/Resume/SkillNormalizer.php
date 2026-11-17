<?php

namespace App\Resume;

/**
 * Canonical skill names so "Vue", "vue.js" and "VueJS" compare equal. Exact match after
 * normalisation; aliases live in config/skills.php.
 */
class SkillNormalizer
{
    public function normalize(string $skill): string
    {
        $key = strtolower(trim($skill));
        $key = preg_replace('/\s+/', ' ', $key);
        $aliases = config('skills.aliases', []);

        return $aliases[$key] ?? $key;
    }

    /**
     * @param  iterable<string>  $skills
     * @return list<string>
     */
    public function normalizeAll(iterable $skills): array
    {
        $out = [];
        foreach ($skills as $skill) {
            $n = $this->normalize((string) $skill);
            if ($n !== '') {
                $out[$n] = true;
            }
        }

        return array_keys($out);
    }
}
