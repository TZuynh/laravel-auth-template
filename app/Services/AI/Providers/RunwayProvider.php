<?php

namespace App\Services\AI\Providers;

use App\Services\AI\AiProviderResponse;

class RunwayProvider extends AbstractAiProvider
{
    public function key(): string
    {
        return 'runway';
    }

    public function supports(string $capability): bool
    {
        return $capability === 'video';
    }

    public function generate(string $capability, array $payload): AiProviderResponse
    {
        if (!$this->supports($capability)) {
            throw new \InvalidArgumentException("Runway does not support {$capability}.");
        }

        $promptImage = $payload['prompt_image'] ?? $payload['image_url'] ?? null;
        $hasPromptImage = is_string($promptImage) && trim($promptImage) !== '';
        $request = [
            'model' => $this->model($payload['model'] ?? null, $hasPromptImage),
            'promptText' => $payload['prompt'] ?? '',
            'duration' => $this->duration($payload['duration'] ?? 5),
            'ratio' => $this->ratio($payload['ratio'] ?? $payload['aspect_ratio'] ?? '9:16'),
        ];

        if ($hasPromptImage) {
            $request['promptImage'] = $promptImage;
        }

        $response = $this->post('v1/image_to_video', $request, [
            'Authorization' => 'Bearer ' . $this->requireApiKey(),
            'Content-Type' => 'application/json',
            'X-Runway-Version' => (string) $this->config('api_version', '2024-11-06'),
        ])->json();

        return new AiProviderResponse(
            provider: $this->key(),
            capability: 'video',
            data: [
                'job_id' => data_get($response, 'id'),
                'url' => data_get($response, 'output.0'),
                'status' => data_get($response, 'status', 'submitted'),
                'request' => $request,
                'raw' => $response,
            ],
        );
    }

    private function ratio(string $aspectRatio): string
    {
        return match ($aspectRatio) {
            '16:9', '1920:1080' => '1280:720',
            '1:1', '1080:1080' => '960:960',
            '720:1280', '832:1104', '1104:832', '1280:720', '960:960' => $aspectRatio,
            default => '720:1280',
        };
    }

    private function model(mixed $model, bool $hasPromptImage): string
    {
        $model = is_string($model) && $model !== ''
            ? $model
            : (string) $this->config('video_model', 'gen4.5');

        if (!in_array($model, ['gen4.5', 'gen4_turbo'], true)) {
            return 'gen4.5';
        }

        return $model === 'gen4_turbo' && !$hasPromptImage ? 'gen4.5' : $model;
    }

    private function duration(mixed $duration): int
    {
        return ((float) $duration) <= 5 ? 5 : 8;
    }
}
