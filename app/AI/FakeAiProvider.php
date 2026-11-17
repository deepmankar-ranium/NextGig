<?php

namespace App\AI;

/**
 * Deterministic provider for tests and for running locally without an API key.
 * Queue responses with pushJson()/pushText()/pushFailure(); every call is recorded in $calls.
 */
class FakeAiProvider implements AiProvider
{
    /** @var list<array{method: string, system: string, input: mixed, schema?: array}> */
    public array $calls = [];

    /** @var list<mixed> */
    private array $queue = [];

    public function pushJson(array $data): static
    {
        $this->queue[] = $data;

        return $this;
    }

    public function pushText(string $text): static
    {
        $this->queue[] = $text;

        return $this;
    }

    public function pushFailure(string $reason = 'timeout'): static
    {
        $this->queue[] = new AiException($reason);

        return $this;
    }

    public function chat(string $system, array $turns): string
    {
        $this->calls[] = ['method' => 'chat', 'system' => $system, 'input' => $turns];
        $next = $this->next();

        return is_string($next) ? $next : json_encode($next);
    }

    public function json(string $system, string $input, array $schema): array
    {
        $this->calls[] = ['method' => 'json', 'system' => $system, 'input' => $input, 'schema' => $schema];
        $next = $this->next();

        if (! is_array($next)) {
            throw new AiException('invalid_json');
        }

        return $next;
    }

    /** Everything sent to the provider, flattened to one string (for "not in prompt" assertions). */
    public function sentText(): string
    {
        return collect($this->calls)
            ->map(fn ($c) => $c['system']."\n".(is_string($c['input']) ? $c['input'] : json_encode($c['input'])))
            ->implode("\n---\n");
    }

    private function next(): mixed
    {
        if ($this->queue === []) {
            return 'This is a placeholder AI response (AI_PROVIDER=fake).';
        }

        $next = array_shift($this->queue);

        if ($next instanceof AiException) {
            throw $next;
        }

        return $next;
    }
}
