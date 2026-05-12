<?php

namespace App\Services\AI\Providers;

use App\Services\AI\AiProviderResponse;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ReplicateProvider extends AbstractAiProvider
{
    public function key(): string
    {
        return 'replicate';
    }

    public function supports(string $capability): bool
    {
        return in_array($capability, ['image', 'video'], true);
    }

    public function generate(string $capability, array $payload): AiProviderResponse
    {
        if (!$this->supports($capability)) {
            throw new \InvalidArgumentException("Replicate does not support {$capability}.");
        }

        $prediction = $this->createPrediction($capability, $payload);
        $result = $this->waitForPrediction($prediction);
        $url = $this->extractMediaUrl($result);

        if (!$url) {
            throw new RuntimeException('Replicate completed but did not return a media URL.');
        }

        return new AiProviderResponse(
            provider: $this->key(),
            capability: $capability,
            data: [
                'job_id' => data_get($result, 'id'),
                'url' => $url,
                'status' => data_get($result, 'status', 'succeeded'),
                'raw' => $result,
            ],
        );
    }

    private function createPrediction(string $capability, array $payload): array
    {
        $model = (string) ($payload['model'] ?? $this->config($capability === 'image' ? 'image_model' : 'video_model', ''));
        $version = (string) ($payload['version'] ?? $this->config($capability === 'image' ? 'image_version' : 'video_version', ''));
        $input = [
            'prompt' => $payload['prompt'] ?? '',
            'aspect_ratio' => $payload['aspect_ratio'] ?? '9:16',
        ];

        if ($capability === 'image' && !empty($payload['negative_prompt'])) {
            $input['negative_prompt'] = $payload['negative_prompt'];
        }

        if ($capability === 'video') {
            $input['duration'] = $payload['duration'] ?? 5;
            if (!empty($payload['image_url'])) {
                $input['image'] = $payload['image_url'];
            }
        }

        $input = array_replace($input, (array) ($payload['provider_payload'] ?? []));
        $body = ['input' => $input];
        $path = '/v1/predictions';

        if ($version !== '') {
            $body['version'] = $version;
        } elseif ($model !== '' && str_contains($model, '/')) {
            $path = '/v1/models/' . trim($model, '/') . '/predictions';
        } else {
            throw new RuntimeException('Missing Replicate model or version for ' . $capability . ' generation.');
        }

        $response = Http::asJson()
            ->acceptJson()
            ->withToken($this->requireApiKey())
            ->withHeaders(['Prefer' => 'wait=60'])
            ->timeout(120)
            ->retry(2, 1000)
            ->post(rtrim((string) $this->config('base_url'), '/') . $path, $body);

        if ($response->failed()) {
            throw new RuntimeException('Replicate provider failed: ' . $response->body());
        }

        return $response->json() ?: [];
    }

    private function waitForPrediction(array $prediction): array
    {
        $status = (string) data_get($prediction, 'status', '');
        if (in_array($status, ['succeeded', 'failed', 'canceled'], true)) {
            return $prediction;
        }

        $getUrl = (string) data_get($prediction, 'urls.get', '');
        if ($getUrl === '') {
            return $prediction;
        }

        $attempts = (int) $this->config('max_poll_attempts', 90);
        $sleepMs = (int) $this->config('poll_interval_ms', 3000);

        for ($attempt = 0; $attempt < $attempts; $attempt++) {
            usleep($sleepMs * 1000);

            $response = Http::acceptJson()
                ->withToken($this->requireApiKey())
                ->timeout(60)
                ->get($getUrl);

            if ($response->failed()) {
                throw new RuntimeException('Replicate status poll failed: ' . $response->body());
            }

            $prediction = $response->json() ?: [];
            $status = (string) data_get($prediction, 'status', '');

            if ($status === 'succeeded') {
                return $prediction;
            }

            if (in_array($status, ['failed', 'canceled'], true)) {
                throw new RuntimeException('Replicate generation failed: ' . json_encode($prediction));
            }
        }

        throw new RuntimeException('Replicate generation timed out before returning media.');
    }

    private function extractMediaUrl(array $data): ?string
    {
        $output = data_get($data, 'output');

        if (is_string($output) && str_starts_with($output, 'http')) {
            return $output;
        }

        if (is_array($output)) {
            foreach ($output as $item) {
                if (is_string($item) && str_starts_with($item, 'http')) {
                    return $item;
                }

                if (is_array($item)) {
                    $url = data_get($item, 'url');
                    if (is_string($url) && str_starts_with($url, 'http')) {
                        return $url;
                    }
                }
            }
        }

        return null;
    }
}
