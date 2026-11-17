<?php

namespace App\AI;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Google Gemini via the REST generateContent endpoint. Logs only status codes and reasons,
 * never prompts or outputs.
 */
class GeminiProvider implements AiProvider
{
    public function __construct(
        private readonly ?string $apiKey,
        private readonly string $model,
        private readonly string $baseUrl,
        private readonly int $timeout,
    ) {
    }

    public function chat(string $system, array $turns): string
    {
        $contents = array_map(fn (array $t) => [
            'role' => $t['role'] === 'assistant' ? 'model' : 'user',
            'parts' => [['text' => $t['text']]],
        ], $turns);

        return $this->generate($system, $contents, ['temperature' => 0.6]);
    }

    public function json(string $system, string $input, array $schema): array
    {
        $text = $this->generate($system, [['role' => 'user', 'parts' => [['text' => $input]]]], [
            'temperature' => 0.2,
            'responseMimeType' => 'application/json',
            'responseSchema' => self::toGeminiSchema($schema),
        ]);

        $decoded = json_decode($text, true);

        if (! is_array($decoded)) {
            throw new AiException('invalid_json');
        }

        return $decoded;
    }

    /**
     * @param  list<array<string, mixed>>  $contents
     * @param  array<string, mixed>  $generationConfig
     */
    private function generate(string $system, array $contents, array $generationConfig): string
    {
        if (blank($this->apiKey)) {
            throw new AiException('not_configured');
        }

        try {
            $response = $this->http()->post("models/{$this->model}:generateContent", [
                'systemInstruction' => ['parts' => [['text' => $system]]],
                'contents' => $contents,
                'generationConfig' => $generationConfig,
            ]);
        } catch (ConnectionException $e) {
            Log::warning('ai.gemini.failed', ['reason' => 'timeout']);
            throw new AiException('timeout', $e);
        }

        if (! $response->successful()) {
            Log::warning('ai.gemini.failed', ['reason' => 'http', 'status' => $response->status()]);
            throw new AiException($response->status() === 429 || $response->serverError() ? 'unavailable' : 'provider_error');
        }

        $text = collect($response->json('candidates.0.content.parts', []))
            ->pluck('text')
            ->filter()
            ->implode('');

        if (trim($text) === '') {
            Log::warning('ai.gemini.failed', ['reason' => 'empty', 'finish' => $response->json('candidates.0.finishReason')]);
            throw new AiException('empty_response');
        }

        return $text;
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl(rtrim($this->baseUrl, '/').'/')
            ->withHeaders(['x-goog-api-key' => $this->apiKey])
            ->acceptJson()
            ->timeout($this->timeout)
            ->retry(2, 500, fn ($e) => $e instanceof ConnectionException, throw: false);
    }

    /**
     * Gemini's responseSchema uses upper-case OpenAPI type names.
     *
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    public static function toGeminiSchema(array $schema): array
    {
        $out = [];
        foreach ($schema as $key => $value) {
            if ($key === 'type' && is_string($value)) {
                $out[$key] = strtoupper($value);
            } elseif ($key === 'properties' && is_array($value)) {
                $out[$key] = array_map(fn ($v) => self::toGeminiSchema($v), $value);
            } elseif ($key === 'items' && is_array($value)) {
                $out[$key] = self::toGeminiSchema($value);
            } else {
                $out[$key] = $value;
            }
        }

        return $out;
    }
}
