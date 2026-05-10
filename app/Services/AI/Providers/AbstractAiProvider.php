<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AiProviderInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

abstract class AbstractAiProvider implements AiProviderInterface
{
    abstract public function key(): string;

    protected function config(string $key, mixed $default = null): mixed
    {
        return config("ai_providers.providers.{$this->key()}.{$key}", $default);
    }

    protected function requireApiKey(): string
    {
        $apiKey = (string) $this->config('api_key', '');

        if ($apiKey === '') {
            throw new RuntimeException("Missing API key for {$this->key()} provider.");
        }

        return $apiKey;
    }

    protected function post(string $path, array $payload, array $headers = []): Response
    {
        $baseUrl = rtrim((string) $this->config('base_url'), '/');
        $response = Http::asJson()
            ->acceptJson()
            ->withHeaders($headers)
            ->timeout(120)
            ->retry(3, 1000)
            ->post($baseUrl . '/' . ltrim($path, '/'), $payload);

        if ($response->failed()) {
            throw new RuntimeException("{$this->key()} provider failed: " . $response->body());
        }

        return $response;
    }
}
