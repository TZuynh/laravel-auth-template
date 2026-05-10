<?php

namespace App\Services\AI\Providers;

use App\Services\AI\AiProviderResponse;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ElevenLabsProvider extends AbstractAiProvider
{
    public function key(): string
    {
        return 'elevenlabs';
    }

    public function supports(string $capability): bool
    {
        return $capability === 'voice';
    }

    public function generate(string $capability, array $payload): AiProviderResponse
    {
        if (!$this->supports($capability)) {
            throw new \InvalidArgumentException("ElevenLabs does not support {$capability}.");
        }

        $voiceId = $payload['voice_id'] ?? $payload['provider_voice_id'] ?? null;
        if (!$voiceId) {
            throw new RuntimeException('Missing ElevenLabs voice_id.');
        }

        $baseUrl = rtrim((string) $this->config('base_url'), '/');
        $response = Http::withHeaders([
            'xi-api-key' => $this->requireApiKey(),
            'Content-Type' => 'application/json',
        ])->timeout(120)->post($baseUrl . "/text-to-speech/{$voiceId}", [
            'model_id' => $payload['model'] ?? $this->config('voice_model'),
            'text' => $payload['text'] ?? $payload['prompt'] ?? '',
            'voice_settings' => $payload['voice_settings'] ?? [
                'stability' => 0.62,
                'similarity_boost' => 0.78,
            ],
        ]);

        if ($response->failed()) {
            throw new RuntimeException('ElevenLabs provider failed: ' . $response->body());
        }

        return new AiProviderResponse(
            provider: $this->key(),
            capability: 'voice',
            data: [
                'binary' => $response->body(),
                'content_type' => $response->header('Content-Type', 'audio/mpeg'),
            ],
        );
    }
}
