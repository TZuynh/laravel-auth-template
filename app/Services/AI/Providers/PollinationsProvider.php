<?php

namespace App\Services\AI\Providers;

use App\Services\AI\AiProviderResponse;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PollinationsProvider extends AbstractAiProvider
{
    public function key(): string
    {
        return 'pollinations';
    }

    public function supports(string $capability): bool
    {
        return $capability === 'image';
    }

    public function generate(string $capability, array $payload): AiProviderResponse
    {
        if (!$this->supports($capability)) {
            throw new \InvalidArgumentException("Pollinations does not support {$capability}.");
        }

        $model = $this->model($payload);
        [$width, $height] = $this->dimensions((string) ($payload['aspect_ratio'] ?? '1:1'));
        $seed = (int) ($payload['seed'] ?? $this->config('seed', 0));
        $enhance = filter_var($payload['enhance'] ?? $this->config('enhance', false), FILTER_VALIDATE_BOOLEAN);
        $url = $this->imageUrl(
            prompt: trim((string) ($payload['prompt'] ?? '')),
            model: $model,
            width: $width,
            height: $height,
            seed: $seed,
            enhance: $enhance,
        );

        $response = Http::timeout((int) $this->config('timeout', 180))
            ->retry(2, 1000)
            ->get($url);

        if ($response->failed()) {
            throw new RuntimeException('Pollinations image request failed: HTTP ' . $response->status());
        }

        $binary = $response->body();
        if ($binary === '') {
            throw new RuntimeException('Pollinations returned an empty image response.');
        }

        return new AiProviderResponse(
            provider: $this->key(),
            capability: 'image',
            data: [
                'binary' => $binary,
                'mime' => (string) ($response->header('Content-Type') ?: 'image/jpeg'),
                'status' => 'ready',
                'model' => $model,
                'width' => $width,
                'height' => $height,
                'seed' => $seed,
                'enhance' => $enhance,
            ],
        );
    }

    private function imageUrl(string $prompt, string $model, int $width, int $height, int $seed, bool $enhance): string
    {
        if ($prompt === '') {
            throw new RuntimeException('Pollinations image prompt is empty.');
        }

        return rtrim((string) $this->config('base_url', 'https://gen.pollinations.ai'), '/')
            . '/image/' . rawurlencode($prompt)
            . '?' . http_build_query([
                'model' => $model,
                'width' => $width,
                'height' => $height,
                'seed' => $seed,
                'enhance' => $enhance ? 'true' : 'false',
                'key' => $this->requireApiKey(),
            ]);
    }

    private function model(array $payload): string
    {
        $configured = strtolower(trim((string) $this->config('image_model', 'flux'))) ?: 'flux';
        $model = strtolower(trim((string) ($payload['model'] ?? $configured)));

        return in_array($model, ['flux', 'turbo'], true) ? $model : $configured;
    }

    private function dimensions(string $aspectRatio): array
    {
        return match ($aspectRatio) {
            '9:16' => [576, 1024],
            '16:9' => [1024, 576],
            '4:5' => [768, 960],
            default => [1024, 1024],
        };
    }
}
