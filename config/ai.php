<?php

return [
    /*
    | Which AiProvider implementation to use: "gemini" or "fake" (tests / local without a key).
    */
    'provider' => env('AI_PROVIDER', 'gemini'),

    /*
    | Per-user limits on AI calls (resume parsing, match explanations, JD drafts, chat).
    */
    'per_minute' => (int) env('AI_PER_MINUTE', 10),
    'daily_cap' => (int) env('AI_DAILY_CAP', 50),
];
