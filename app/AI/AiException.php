<?php

namespace App\AI;

use RuntimeException;

/**
 * A failed AI call. `reason` is a short machine code (safe to log); the message shown to
 * users comes from userMessage(), never from the provider.
 */
class AiException extends RuntimeException
{
    public function __construct(public readonly string $reason, ?\Throwable $previous = null)
    {
        parent::__construct("AI call failed: {$reason}", 0, $previous);
    }

    public function userMessage(): string
    {
        return match ($this->reason) {
            'not_configured' => 'AI features are not configured on this server yet.',
            'timeout', 'unavailable' => 'The AI service is busy right now. Please try again in a minute.',
            default => "We couldn't process that with AI. Please try again.",
        };
    }
}
