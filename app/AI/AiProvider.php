<?php

namespace App\AI;

interface AiProvider
{
    /**
     * Multi-turn chat. Turns are structured (never concatenated into one string), so a user
     * cannot inject fake "assistant" turns.
     *
     * @param  list<array{role: 'user'|'assistant', text: string}>  $turns
     *
     * @throws AiException
     */
    public function chat(string $system, array $turns): string;

    /**
     * Ask for JSON matching $schema (JSON-Schema subset: type, properties, items, required,
     * enum). Callers must still validate the result with AiOutput::validate().
     *
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     *
     * @throws AiException
     */
    public function json(string $system, string $input, array $schema): array;
}
