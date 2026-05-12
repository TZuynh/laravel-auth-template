<?php

namespace App\Services\AI\Providers;

use App\Services\AI\AiProviderResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class ComfyUiProvider extends AbstractAiProvider
{
    public function key(): string
    {
        return 'comfyui';
    }

    public function supports(string $capability): bool
    {
        return $capability === 'image';
    }

    public function generate(string $capability, array $payload): AiProviderResponse
    {
        if (!$this->supports($capability)) {
            throw new \InvalidArgumentException("ComfyUI does not support {$capability}.");
        }

        $model = $this->modelKey($payload);
        $workflow = $this->workflow($model, $payload);
        $clientId = (string) Str::uuid();
        $promptId = $this->submit($workflow, $clientId);
        $history = $this->waitForHistory($promptId);
        $image = $this->extractImage($history);

        if (!$image) {
            throw new RuntimeException('ComfyUI completed but did not return an output image.');
        }

        return new AiProviderResponse(
            provider: $this->key(),
            capability: 'image',
            data: [
                'job_id' => $promptId,
                'url' => $this->viewUrl($image),
                'status' => 'ready',
                'model' => $model,
                'raw' => [
                    'prompt_id' => $promptId,
                    'image' => $image,
                ],
            ],
        );
    }

    private function submit(array $workflow, string $clientId): string
    {
        $response = Http::asJson()
            ->acceptJson()
            ->timeout(30)
            ->post($this->baseUrl() . '/prompt', [
                'prompt' => $workflow,
                'client_id' => $clientId,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('ComfyUI prompt submit failed: ' . $response->body());
        }

        $promptId = (string) data_get($response->json(), 'prompt_id', '');
        if ($promptId === '') {
            throw new RuntimeException('ComfyUI did not return a prompt_id.');
        }

        return $promptId;
    }

    private function waitForHistory(string $promptId): array
    {
        $attempts = (int) $this->config('max_poll_attempts', 180);
        $sleepMs = (int) $this->config('poll_interval_ms', 1500);

        for ($attempt = 0; $attempt < $attempts; $attempt++) {
            $response = Http::acceptJson()
                ->timeout(20)
                ->get($this->baseUrl() . '/history/' . $promptId);

            if ($response->failed()) {
                throw new RuntimeException('ComfyUI history poll failed: ' . $response->body());
            }

            $data = $response->json() ?: [];
            $history = (array) data_get($data, $promptId, []);
            if ($history !== [] && $this->extractImage($history)) {
                return $history;
            }

            usleep($sleepMs * 1000);
        }

        throw new RuntimeException('ComfyUI timed out before returning an image. Check the local queue and GPU memory.');
    }

    private function extractImage(array $history): ?array
    {
        foreach ((array) data_get($history, 'outputs', []) as $output) {
            foreach ((array) data_get($output, 'images', []) as $image) {
                if (!empty($image['filename'])) {
                    return [
                        'filename' => (string) $image['filename'],
                        'subfolder' => (string) ($image['subfolder'] ?? ''),
                        'type' => (string) ($image['type'] ?? 'output'),
                    ];
                }
            }
        }

        return null;
    }

    private function viewUrl(array $image): string
    {
        return $this->baseUrl() . '/view?' . http_build_query([
            'filename' => $image['filename'],
            'subfolder' => $image['subfolder'],
            'type' => $image['type'],
        ]);
    }

    private function workflow(string $model, array $payload): array
    {
        $prompt = trim((string) ($payload['prompt'] ?? ''));
        $negative = trim((string) ($payload['negative_prompt'] ?? ''));
        [$width, $height] = $this->dimensions((string) ($payload['aspect_ratio'] ?? '9:16'), $model);
        $steps = $model === 'flux_schnell'
            ? (int) $this->config('flux_steps', 4)
            : (int) $this->config('sd15_steps', 24);
        $cfg = $model === 'flux_schnell'
            ? (float) $this->config('flux_cfg', 1)
            : (float) $this->config('sd15_cfg', 7);
        $sampler = $model === 'flux_schnell'
            ? (string) $this->config('flux_sampler', 'euler')
            : (string) $this->config('sd15_sampler', 'dpmpp_2m');
        $scheduler = $model === 'flux_schnell'
            ? (string) $this->config('flux_scheduler', 'simple')
            : (string) $this->config('sd15_scheduler', 'karras');

        return [
            '1' => [
                'class_type' => 'CheckpointLoaderSimple',
                'inputs' => [
                    'ckpt_name' => $this->checkpoint($model),
                ],
            ],
            '2' => [
                'class_type' => 'CLIPTextEncode',
                'inputs' => [
                    'text' => $prompt,
                    'clip' => ['1', 1],
                ],
            ],
            '3' => [
                'class_type' => 'CLIPTextEncode',
                'inputs' => [
                    'text' => $negative !== '' ? $negative : 'text, watermark, logo, blurry, low quality',
                    'clip' => ['1', 1],
                ],
            ],
            '4' => [
                'class_type' => $model === 'flux_schnell' ? 'EmptySD3LatentImage' : 'EmptyLatentImage',
                'inputs' => [
                    'width' => $width,
                    'height' => $height,
                    'batch_size' => 1,
                ],
            ],
            '5' => [
                'class_type' => 'KSampler',
                'inputs' => [
                    'seed' => random_int(1, PHP_INT_MAX),
                    'steps' => $steps,
                    'cfg' => $cfg,
                    'sampler_name' => $sampler,
                    'scheduler' => $scheduler,
                    'denoise' => 1,
                    'model' => ['1', 0],
                    'positive' => ['2', 0],
                    'negative' => ['3', 0],
                    'latent_image' => ['4', 0],
                ],
            ],
            '6' => [
                'class_type' => 'VAEDecode',
                'inputs' => [
                    'samples' => ['5', 0],
                    'vae' => ['1', 2],
                ],
            ],
            '7' => [
                'class_type' => 'SaveImage',
                'inputs' => [
                    'filename_prefix' => $model === 'flux_schnell' ? 'laravel_flux' : 'laravel_sd15',
                    'images' => ['6', 0],
                ],
            ],
        ];
    }

    private function dimensions(string $aspectRatio, string $model): array
    {
        $large = $model === 'flux_schnell';

        return match ($aspectRatio) {
            '16:9' => $large ? [1344, 768] : [768, 512],
            '1:1' => $large ? [1024, 1024] : [640, 640],
            '4:5' => $large ? [1024, 1280] : [640, 800],
            default => $large ? [768, 1344] : [512, 768],
        };
    }

    private function modelKey(array $payload): string
    {
        $model = strtolower((string) ($payload['model'] ?? 'flux_schnell'));

        return str_contains($model, 'sd15') || str_contains($model, 'sd1.5') || str_contains($model, 'stable')
            ? 'sd15'
            : 'flux_schnell';
    }

    private function checkpoint(string $model): string
    {
        return $model === 'sd15'
            ? (string) $this->config('sd15_checkpoint', 'v1-5-pruned-emaonly.safetensors')
            : (string) $this->config('flux_checkpoint', 'flux1-schnell-fp8.safetensors');
    }

    private function baseUrl(): string
    {
        return rtrim((string) $this->config('base_url', 'http://127.0.0.1:8188'), '/');
    }
}
