<?php

namespace App\Resume;

/**
 * Removes direct identifiers before text is sent to an AI provider. Skills and experience
 * are what matter for matching; contact details never need to leave the server.
 */
class PiiRedactor
{
    public function redact(string $text, array $knownValues = []): string
    {
        foreach (array_filter($knownValues) as $value) {
            $text = str_ireplace($value, '[redacted]', $text);
        }

        $text = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', '[email]', $text);
        $text = preg_replace('/(?:\+?\d[\d\s().-]{7,}\d)/', '[phone]', $text);
        $text = preg_replace('#https?://\S+|(?:www\.)?linkedin\.com/\S+#i', '[link]', $text);

        return $text;
    }
}
