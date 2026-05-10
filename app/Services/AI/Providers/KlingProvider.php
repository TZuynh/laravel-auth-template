<?php

namespace App\Services\AI\Providers;

use App\Services\AI\AiProviderResponse;

class KlingProvider extends AbstractAiProvider
{
    public function key(): string
    {
        return 'kling';
    }

    public function supports(string $capability): bool
    {
        return $capability === 'video';
    }

    public function generate(string $capability, array $payload): AiProviderResponse
    {
        if (!$this->supports($capability)) {
            throw new \InvalidArgumentException("Kling does not support {$capability}.");
        }

        $response = $this->post('v1/videos/generations', [
            'model' => $payload['model'] ?? $this->config('video_model'),
            'prompt' => $payload['prompt'] ?? '',
            'image_url' => $payload['image_url'] ?? null,
            'duration' => $payload['duration'] ?? 5,
            'aspect_ratio' => $payload['aspect_ratio'] ?? '9:16',
            'camera_control' => $payload['camera_control'] ?? null,
        ], [
            'Authorization' => 'Bearer ' . $this->requireApiKey(),
            'Content-Type' => 'application/json',
        ])->json();

        return new AiProviderResponse(
            provider: $this->key(),
            capability: 'video',
            data: [
                'job_id' => data_get($response, 'id'),
                'url' => data_get($response, 'video_url'),
                'status' => data_get($response, 'status', 'submitted'),
                'raw' => $response,
            ],
        );
    }
}
