<?php

namespace App\Services\AI\Providers;

use App\Services\AI\AiProviderResponse;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FalProvider extends AbstractAiProvider
{
    public function key(): string
    {
        return 'fal';
    }

    public function supports(string $capability): bool
    {
        return in_array($capability, ['image', 'video'], true);
    }

    public function generate(string $capability, array $payload): AiProviderResponse
    {
        if (!$this->supports($capability)) {
            throw new \InvalidArgumentException("fal.ai does not support {$capability}.");
        }

        $model = $this->model($capability, $payload);
        $submission = $this->submit($model, $this->requestPayload($capability, $payload));
        $result = $this->waitForResult($submission);
        $url = $this->extractMediaUrl($result, $capability);

        if (!$url) {
            throw new RuntimeException('fal.ai completed but did not return a media URL.');
        }

        return new AiProviderResponse(
            provider: $this->key(),
            capability: $capability,
            data: [
                'job_id' => data_get($submission, 'request_id'),
                'url' => $url,
                'status' => 'ready',
                'model' => $model,
                'raw' => $result,
            ],
        );
    }

    private function submit(string $model, array $payload): array
    {
        $response = Http::asJson()
            ->acceptJson()
            ->withHeaders([
                'Authorization' => 'Key ' . $this->requireApiKey(),
            ])
            ->timeout(120)
            ->retry(2, 1000)
            ->post(rtrim((string) $this->config('base_url'), '/') . '/' . ltrim($model, '/'), $payload);

        if ($response->failed()) {
            throw new RuntimeException('fal.ai provider failed: ' . $response->body());
        }

        $data = $response->json();

        return is_array($data) ? $data : [];
    }

    private function waitForResult(array $submission): array
    {
        $responseUrl = (string) data_get($submission, 'response_url', '');
        if ($responseUrl === '' && $this->extractMediaUrl($submission, 'video')) {
            return $submission;
        }

        $statusUrl = (string) data_get($submission, 'status_url', '');
        $attempts = (int) $this->config('max_poll_attempts', 90);
        $sleepMs = (int) $this->config('poll_interval_ms', 2500);

        for ($attempt = 0; $attempt < $attempts; $attempt++) {
            if ($statusUrl !== '') {
                $status = Http::acceptJson()
                    ->withHeaders(['Authorization' => 'Key ' . $this->requireApiKey()])
                    ->timeout(60)
                    ->get($statusUrl);

                if ($status->failed()) {
                    throw new RuntimeException('fal.ai status poll failed: ' . $status->body());
                }

                $statusData = $status->json() ?: [];
                $state = strtoupper((string) data_get($statusData, 'status', ''));
                if (in_array($state, ['FAILED', 'ERROR'], true)) {
                    throw new RuntimeException('fal.ai generation failed: ' . json_encode($statusData));
                }

                if ($state !== 'COMPLETED') {
                    usleep($sleepMs * 1000);
                    continue;
                }
            }

            if ($responseUrl === '') {
                return is_array($statusData ?? null) ? $statusData : $submission;
            }

            $result = Http::acceptJson()
                ->withHeaders(['Authorization' => 'Key ' . $this->requireApiKey()])
                ->timeout(120)
                ->get($responseUrl);

            if ($result->failed()) {
                throw new RuntimeException('fal.ai result fetch failed: ' . $result->body());
            }

            return $result->json() ?: [];
        }

        throw new RuntimeException('fal.ai generation timed out before returning media.');
    }

    private function requestPayload(string $capability, array $payload): array
    {
        $request = [
            'prompt' => $payload['prompt'] ?? '',
        ];

        if ($capability === 'image') {
            $request['aspect_ratio'] = $payload['aspect_ratio'] ?? '9:16';
            if (!empty($payload['negative_prompt'])) {
                $request['negative_prompt'] = $payload['negative_prompt'];
            }
            if (!empty($payload['image_size'])) {
                $request['image_size'] = $payload['image_size'];
            }
        }

        if ($capability === 'video') {
            $request['aspect_ratio'] = $payload['aspect_ratio'] ?? '9:16';
            $request['duration'] = (string) ($payload['duration'] ?? 5);
            if (!empty($payload['image_url'])) {
                $request['image_url'] = $payload['image_url'];
            }
        }

        return array_replace($request, (array) ($payload['provider_payload'] ?? []));
    }

    private function model(string $capability, array $payload): string
    {
        $model = (string) ($payload['model'] ?? '');
        if ($model !== '') {
            return $model;
        }

        return (string) $this->config($capability === 'image' ? 'image_model' : 'video_model');
    }

    private function extractMediaUrl(array $data, string $capability): ?string
    {
        $candidates = $capability === 'image'
            ? ['image.url', 'images.0.url', 'data.images.0.url', 'url', 'output.0']
            : ['video.url', 'videos.0.url', 'data.video.url', 'url', 'output.0'];

        foreach ($candidates as $candidate) {
            $value = data_get($data, $candidate);
            if (is_string($value) && str_starts_with($value, 'http')) {
                return $value;
            }
        }

        return null;
    }
}
