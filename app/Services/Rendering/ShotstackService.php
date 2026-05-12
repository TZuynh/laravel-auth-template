<?php

namespace App\Services\Rendering;

use App\Jobs\RenderShotstackVideoJob;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ShotstackService
{
    /**
     * Queue a Shotstack render and return the render task id.
     *
     * @throws ValidationException
     * @throws RequestException
     */
    public function render(array $payload): string
    {
        $payload = $this->validatePayload($payload);

        $response = Http::asJson()
            ->acceptJson()
            ->withHeaders([
                'x-api-key' => $this->apiKey(),
            ])
            ->timeout((int) config('shotstack.timeout', 120))
            ->retry(
                (int) config('shotstack.retry_times', 3),
                (int) config('shotstack.retry_sleep', 1000),
                fn (): bool => true
            )
            ->post($this->renderEndpoint(), $payload)
            ->throw()
            ->json();

        if (!data_get($response, 'success') || !is_string(data_get($response, 'response.id'))) {
            throw new RuntimeException('Shotstack render response did not include a render id.');
        }

        return data_get($response, 'response.id');
    }

    /**
     * Return the Shotstack render status response for a queued render id.
     *
     * @throws RequestException
     */
    public function status(string $renderId, bool $includeData = false): array
    {
        $response = Http::acceptJson()
            ->withHeaders([
                'x-api-key' => $this->apiKey(),
            ])
            ->timeout((int) config('shotstack.timeout', 120))
            ->retry(
                (int) config('shotstack.retry_times', 3),
                (int) config('shotstack.retry_sleep', 1000),
                fn (): bool => true
            )
            ->get($this->renderEndpoint() . '/' . $renderId, [
                'data' => $includeData ? 'true' : 'false',
            ])
            ->throw()
            ->json();

        if (!is_array($response)) {
            throw new RuntimeException('Shotstack status response was not valid JSON.');
        }

        return $response;
    }

    /**
     * Dispatch a queued render job. The job calls render() and logs the render id.
     *
     * @throws ValidationException
     */
    public function queue(array $payload, array $context = []): PendingDispatch
    {
        $this->validatePayload($payload);

        return RenderShotstackVideoJob::dispatch($payload, $context)
            ->onQueue((string) config('shotstack.queue', 'render'));
    }

    /**
     * Validate the official Shotstack Render Asset payload shape without
     * stripping Shotstack-specific nested asset, clip, output or merge keys.
     *
     * @throws ValidationException
     */
    public function validatePayload(array $payload): array
    {
        Validator::make($payload, [
            'timeline' => ['required', 'array'],
            'timeline.soundtrack' => ['nullable', 'array'],
            'timeline.soundtrack.src' => ['required_with:timeline.soundtrack', 'url'],
            'timeline.soundtrack.effect' => ['nullable', 'string'],
            'timeline.soundtrack.volume' => ['nullable', 'numeric', 'between:0,1'],
            'timeline.background' => ['nullable', 'string'],
            'timeline.fonts' => ['nullable', 'array'],
            'timeline.fonts.*.src' => ['required_with:timeline.fonts', 'url'],
            'timeline.tracks' => ['required', 'array', 'min:1'],
            'timeline.tracks.*' => ['required', 'array'],
            'timeline.tracks.*.clips' => ['required', 'array', 'min:1'],
            'timeline.tracks.*.clips.*' => ['required', 'array'],
            'timeline.tracks.*.clips.*.asset' => ['required', 'array'],
            'timeline.tracks.*.clips.*.asset.type' => [
                'required',
                'string',
                'in:video,image,text,html,title,audio,shape,luma,caption,rich-text,text-to-image,image-to-video,svg',
            ],
            'timeline.tracks.*.clips.*.asset.src' => ['nullable', 'url'],
            'timeline.tracks.*.clips.*.start' => ['required', 'numeric', 'min:0'],
            'timeline.tracks.*.clips.*.length' => ['required', 'numeric', 'gt:0'],
            'timeline.cache' => ['nullable', 'boolean'],

            'output' => ['required', 'array'],
            'output.format' => ['required', 'string', 'in:mp4,gif,jpg,png,mp3'],
            'output.resolution' => ['nullable', 'string', 'in:preview,mobile,sd,hd,1080,4k'],
            'output.aspectRatio' => ['nullable', 'string', 'in:16:9,9:16,1:1,4:5,4:3'],
            'output.fps' => ['nullable', 'integer', 'min:1', 'max:60'],
            'output.quality' => ['nullable', 'string', 'in:low,medium,high'],
            'output.mute' => ['nullable', 'boolean'],
            'output.poster' => ['nullable', 'array'],
            'output.thumbnail' => ['nullable', 'array'],
            'output.destinations' => ['nullable', 'array'],

            'merge' => ['nullable', 'array'],
            'merge.*.find' => ['required_with:merge', 'string'],
            'merge.*.replace' => ['required_with:merge'],
            'callback' => ['nullable', 'url'],
            'disk' => ['nullable', 'string', 'in:local,mount'],
        ])->validate();

        return $payload;
    }

    private function renderEndpoint(): string
    {
        $baseUrl = rtrim((string) config('shotstack.base_url', 'https://api.shotstack.io/edit'), '/');
        $version = trim((string) config('shotstack.version', 'stage'), '/');

        return "{$baseUrl}/{$version}/render";
    }

    private function apiKey(): string
    {
        $apiKey = trim((string) config('shotstack.api_key', ''));

        if ($apiKey === '') {
            throw new RuntimeException('Missing SHOTSTACK_API_KEY. Add it to .env before rendering with Shotstack.');
        }

        return $apiKey;
    }
}
