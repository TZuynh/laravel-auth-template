<?php

namespace App\Services\AI\Providers;

use App\Services\AI\AiProviderResponse;

class OpenAiProvider extends AbstractAiProvider
{
    public function key(): string
    {
        return 'openai';
    }

    public function supports(string $capability): bool
    {
        return in_array($capability, ['text', 'image'], true);
    }

    public function generate(string $capability, array $payload): AiProviderResponse
    {
        return match ($capability) {
            'text' => $this->generateText($payload),
            'image' => $this->generateImage($payload),
            default => throw new \InvalidArgumentException("OpenAI does not support {$capability}."),
        };
    }

    private function generateText(array $payload): AiProviderResponse
    {
        $response = $this->post('chat/completions', [
            'model' => $payload['model'] ?? $this->config('text_model'),
            'messages' => [
                ['role' => 'system', 'content' => $payload['system'] ?? 'You are a cinematic ecommerce video director.'],
                ['role' => 'user', 'content' => $payload['prompt'] ?? ''],
            ],
            'temperature' => $payload['temperature'] ?? 0.8,
        ], [
            'Authorization' => 'Bearer ' . $this->requireApiKey(),
            'Content-Type' => 'application/json',
        ])->json();

        return new AiProviderResponse(
            provider: $this->key(),
            capability: 'text',
            data: [
                'text' => data_get($response, 'choices.0.message.content', ''),
                'raw' => $response,
            ],
            tokensUsed: (int) data_get($response, 'usage.total_tokens', 0),
        );
    }

    private function generateImage(array $payload): AiProviderResponse
    {
        $response = $this->post('images/generations', [
            'model' => $payload['model'] ?? $this->config('image_model'),
            'prompt' => $payload['prompt'] ?? '',
            'size' => $payload['size'] ?? '1024x1792',
            'n' => 1,
        ], [
            'Authorization' => 'Bearer ' . $this->requireApiKey(),
            'Content-Type' => 'application/json',
        ])->json();

        return new AiProviderResponse(
            provider: $this->key(),
            capability: 'image',
            data: [
                'url' => data_get($response, 'data.0.url'),
                'b64_json' => data_get($response, 'data.0.b64_json'),
                'raw' => $response,
            ],
        );
    }
}
