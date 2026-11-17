<?php

namespace App\Resume;

use RuntimeException;

class ResumeException extends RuntimeException
{
    public function __construct(public readonly string $reason)
    {
        parent::__construct("Resume processing failed: {$reason}");
    }

    public function userMessage(): string
    {
        return match ($this->reason) {
            'unreadable_pdf' => "We couldn't read text from that PDF. Is it a scanned image? Try exporting it from Word or Google Docs.",
            default => "We couldn't process that resume. Please try again.",
        };
    }
}
