<?php

namespace App\Services\AiVideo;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PythonAiWorkerClient
{
    public function generateScript(array $payload): array
    {
        return $this->post('/api/v1/script/generate', $payload);
    }

    public function generateBulkVideos(array $payload): array
    {
        return $this->post('/api/v1/bulk/generate', $payload);
    }

    public function splitScenes(array $payload): array
    {
        return $this->post('/api/v1/scenes/split', $payload);
    }

    public function generateImage(array $payload): array
    {
        return $this->post('/api/v1/assets/image', $payload);
    }

    public function generateVideo(array $payload): array
    {
        return $this->post('/api/v1/assets/video', $payload);
    }

    public function generateVoice(array $payload): array
    {
        return $this->post('/api/v1/voice/generate', $payload);
    }

    public function generateSubtitles(array $payload): array
    {
        return $this->post('/api/v1/subtitles/generate', $payload);
    }

    public function renderTimeline(array $payload): array
    {
        return $this->post('/api/v1/render/timeline', $payload);
    }

    private function post(string $path, array $payload): array
    {
        $response = $this->http()
            ->post($path, $payload)
            ->throw()
            ->json();

        if (!is_array($response)) {
            throw new RuntimeException('AI worker returned an invalid JSON response.');
        }

        return $response;
    }

    private function http(): PendingRequest
    {
        $baseUrl = rtrim((string) config('ai_video_platform.python_worker.base_url'), '/');
        $apiKey = (string) config('ai_video_platform.python_worker.api_key');
        $headers = ['Accept' => 'application/json'];

        if ($apiKey !== '') {
            $headers['X-AI-Worker-Key'] = $apiKey;
        }

        return Http::baseUrl($baseUrl)
            ->asJson()
            ->withHeaders($headers)
            ->timeout((int) config('ai_video_platform.python_worker.timeout', 180))
            ->retry(
                (int) config('ai_video_platform.python_worker.retry_times', 2),
                (int) config('ai_video_platform.python_worker.retry_sleep', 750)
            );
    }
}
