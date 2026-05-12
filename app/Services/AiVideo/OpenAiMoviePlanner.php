<?php

namespace App\Services\AiVideo;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiMoviePlanner
{
    public function plan(string $prompt, array $options = []): ?array
    {
        $apiKey = trim((string) config('ai_video_platform.openai.api_key', ''));
        if ($apiKey === '') {
            return null;
        }

        $payload = [
            'model' => (string) config('ai_video_platform.openai.model', 'gpt-5.5'),
            'input' => [
                [
                    'role' => 'system',
                    'content' => $this->systemPrompt(),
                ],
                [
                    'role' => 'user',
                    'content' => json_encode([
                        'source_prompt' => $prompt,
                        'language' => $options['language'] ?? 'vi',
                        'duration_seconds' => $options['duration_seconds'] ?? 30,
                        'aspect_ratio' => $options['aspect_ratio'] ?? '9:16',
                        'style_preset' => $options['preset'] ?? [],
                        'editor_settings' => $options['editor_settings'] ?? [],
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ],
            ],
            'reasoning' => [
                'effort' => (string) config('ai_video_platform.openai.reasoning_effort', 'medium'),
            ],
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'ai_movie_timeline_plan',
                    'strict' => true,
                    'schema' => $this->schema(),
                ],
            ],
        ];

        $response = Http::baseUrl(rtrim((string) config('ai_video_platform.openai.base_url', 'https://api.openai.com/v1'), '/'))
            ->withToken($apiKey)
            ->asJson()
            ->timeout((int) config('ai_video_platform.openai.timeout', 120))
            ->post('/responses', $payload);

        if (!$response->successful()) {
            throw new RuntimeException('OpenAI movie planner failed: ' . $response->body());
        }

        $decoded = json_decode($this->outputText($response->json() ?? []), true);

        return is_array($decoded) ? $decoded : null;
    }

    private function outputText(array $response): string
    {
        $direct = data_get($response, 'output_text');
        if (is_string($direct) && trim($direct) !== '') {
            return $direct;
        }

        foreach ((array) data_get($response, 'output', []) as $item) {
            foreach ((array) data_get($item, 'content', []) as $content) {
                $text = data_get($content, 'text');
                if (is_string($text) && trim($text) !== '') {
                    return $text;
                }
            }
        }

        return '';
    }

    private function systemPrompt(): string
    {
        return implode("\n", [
            'You are an AI movie generation director for a SaaS video generator.',
            'Generate a cinematic scene-by-scene timeline, not a static template.',
            'Each scene must include shot type, camera movement, visual direction, B-roll direction, subtitle, voice over, transition, sound effect, pacing, emotional tone, duration, motion instructions, and asset plan.',
            'The 0-3 second hook must stop the scroll with a strong visual and large animated caption.',
            'Use concise subtitles suitable for word-by-word TikTok karaoke captions.',
        ]);
    }

    private function schema(): array
    {
        $scene = [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => [
                'scene',
                'type',
                'title',
                'duration_seconds',
                'shot_type',
                'camera',
                'visual',
                'b_roll_direction',
                'voice_over',
                'subtitle',
                'transition',
                'sound_effect',
                'pacing',
                'emotional_tone',
                'asset_plan',
                'motion',
            ],
            'properties' => [
                'scene' => ['type' => 'integer'],
                'type' => ['type' => 'string'],
                'title' => ['type' => 'string'],
                'duration_seconds' => ['type' => 'number'],
                'shot_type' => ['type' => 'string'],
                'camera' => ['type' => 'string'],
                'visual' => ['type' => 'string'],
                'b_roll_direction' => ['type' => 'string'],
                'voice_over' => ['type' => 'string'],
                'subtitle' => ['type' => 'string'],
                'transition' => ['type' => 'string'],
                'sound_effect' => ['type' => 'string'],
                'pacing' => ['type' => 'string'],
                'emotional_tone' => ['type' => 'string'],
                'asset_plan' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => ['primary', 'fallback', 'search_query', 'overlay', 'music_cue'],
                    'properties' => [
                        'primary' => ['type' => 'string'],
                        'fallback' => ['type' => 'string'],
                        'search_query' => ['type' => 'string'],
                        'overlay' => ['type' => 'string'],
                        'music_cue' => ['type' => 'string'],
                    ],
                ],
                'motion' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => ['engine', 'zoom_start', 'zoom_end', 'shake', 'motion_blur', 'speed_ramp'],
                    'properties' => [
                        'engine' => ['type' => 'string'],
                        'zoom_start' => ['type' => 'number'],
                        'zoom_end' => ['type' => 'number'],
                        'shake' => ['type' => 'number'],
                        'motion_blur' => ['type' => 'boolean'],
                        'speed_ramp' => ['type' => 'string'],
                    ],
                ],
            ],
        ];

        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['script', 'scenes'],
            'properties' => [
                'script' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => ['hook', 'problem', 'emotion', 'solution', 'cta', 'full'],
                    'properties' => [
                        'hook' => ['type' => 'string'],
                        'problem' => ['type' => 'string'],
                        'emotion' => ['type' => 'string'],
                        'solution' => ['type' => 'string'],
                        'cta' => ['type' => 'string'],
                        'full' => ['type' => 'string'],
                    ],
                ],
                'scenes' => [
                    'type' => 'array',
                    'minItems' => 4,
                    'maxItems' => 8,
                    'items' => $scene,
                ],
            ],
        ];
    }
}
