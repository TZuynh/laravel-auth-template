<?php

namespace App\Services\Rendering;

use App\DTOs\AiVideo\TimelineManifestData;
use App\Models\SceneAsset;
use App\Models\VideoVersion;
use Illuminate\Support\Facades\Storage;

class TimelineBuilder
{
    public function buildForVersion(VideoVersion $version): TimelineManifestData
    {
        $version->loadMissing(['videoProject.scenes.sceneAssets', 'videoProject.scenes.transition', 'voiceTracks', 'subtitleTracks']);
        $format = $this->format($version->aspect_ratio ?: '9:16');
        $cursor = 0.0;
        $voiceTrack = $version->voiceTracks
            ->sortByDesc('id')
            ->first(fn ($track): bool => is_string($track->audio_path) && trim($track->audio_path) !== '');

        $scenes = $version->videoProject?->scenes?->map(function ($scene) use (&$cursor, $version): array {
            $duration = (float) $scene->duration_seconds;
            $asset = $scene->sceneAssets
                ->sortBy(fn (SceneAsset $asset): int => match ((string) $asset->type?->value) {
                    'generated_video' => 0,
                    'generated_image' => 1,
                    'video' => 2,
                    'image' => 3,
                    default => 9,
                })
                ->first(fn (SceneAsset $asset): bool => in_array((string) $asset->type?->value, ['generated_video', 'generated_image', 'video', 'image'], true));

            $payload = [
                'id' => $scene->id,
                'sort_order' => $scene->sort_order,
                'type' => data_get($scene->metadata, 'scene_type'),
                'start' => round($cursor, 3),
                'duration' => round($duration, 3),
                'title' => $scene->title,
                'subtitle' => $scene->subtitle_text ?: $scene->voice_over_text,
                'voice_over' => $scene->voice_over_text,
                'shot_type' => data_get($scene->metadata, 'shot_type'),
                'camera' => $scene->camera_movement,
                'transition' => data_get($scene->metadata, 'shotstack_transition')
                    ?: $scene->transition?->slug
                    ?: data_get($scene->metadata, 'transition_type', 'fade'),
                'visual_url' => $asset?->path ? $this->assetUrl($asset->path) : null,
                'visual' => data_get($scene->metadata, 'visual'),
                'b_roll_direction' => data_get($scene->metadata, 'b_roll_direction'),
                'asset_plan' => data_get($scene->metadata, 'asset_plan', []),
                'motion' => data_get($scene->metadata, 'motion', []),
                'subtitle_style' => data_get($scene->metadata, 'subtitle_style', $version->subtitle_style),
                'subtitle_cues' => data_get($scene->metadata, 'subtitle_cues', []),
                'sound_effect' => data_get($scene->metadata, 'sound_effect'),
                'pacing' => data_get($scene->metadata, 'pacing', $version->pacing),
                'emotional_tone' => data_get($scene->metadata, 'emotional_tone'),
                'prompts' => [
                    'scene' => data_get($scene->metadata, 'scene_prompt'),
                    'image' => data_get($scene->metadata, 'image_prompt'),
                    'video' => data_get($scene->metadata, 'video_prompt'),
                    'negative' => data_get($scene->metadata, 'negative_prompt'),
                ],
                'style' => $version->style_slug,
                'visual_direction' => $version->visual_direction,
            ];

            $cursor += $duration;

            return $payload;
        })->values()->all() ?? [];

        return new TimelineManifestData(
            projectId: (int) $version->video_project_id,
            aspectRatio: $version->aspect_ratio,
            width: $format['width'],
            height: $format['height'],
            fps: (int) config('ai_video.ffmpeg.fps', 30),
            scenes: $scenes,
            musicUrl: data_get($version->style_payload, 'music_url'),
            voiceUrl: $voiceTrack?->audio_path ? $this->assetUrl((string) $voiceTrack->audio_path) : null,
            title: $version->title,
            style: $version->style_name,
            music: $version->music,
            subtitleStyle: $version->subtitle_style,
        );
    }

    public function buildShotstackPayload(VideoVersion $version): array
    {
        $manifest = $this->buildForVersion($version)->toArray();
        $style = $version->style_payload ?? [];
        $visualClips = [];
        $subtitleClips = [];

        foreach ($manifest['scenes'] as $index => $scene) {
            $visualUrl = $this->remoteUrl($scene['visual_url'] ?? null);
            $transition = $style['transitions'][$index] ?? $scene['transition'] ?? 'fade';
            $effect = $style['effects'][$index % max(count($style['effects'] ?? []), 1)] ?? 'zoomIn';

            $visualClips[] = [
                'asset' => $visualUrl
                    ? ['type' => str_ends_with(parse_url($visualUrl, PHP_URL_PATH) ?: '', '.mp4') ? 'video' : 'image', 'src' => $visualUrl]
                    : ['type' => 'title', 'text' => $scene['title'], 'style' => 'minimal'],
                'start' => $scene['start'],
                'length' => $scene['duration'],
                'fit' => 'cover',
                'transition' => [
                    'in' => $index === 0 ? 'fade' : $transition,
                    'out' => $transition,
                ],
                'effect' => $effect,
            ];

            $subtitleClips[] = [
                'asset' => [
                    'type' => 'title',
                    'text' => $scene['subtitle'] ?: $scene['title'],
                    'style' => 'subtitle',
                ],
                'start' => $scene['start'],
                'length' => $scene['duration'],
                'position' => 'bottom',
            ];
        }

        $payload = [
            'timeline' => [
                'background' => '#020617',
                'tracks' => [
                    ['clips' => $subtitleClips],
                    ['clips' => $visualClips],
                ],
                'cache' => true,
            ],
            'output' => [
                'format' => 'mp4',
                'resolution' => 'hd',
                'aspectRatio' => $version->aspect_ratio,
                'fps' => min(30, max(24, (int) $manifest['fps'])),
                'quality' => 'medium',
                'destinations' => [
                    ['provider' => 'shotstack', 'exclude' => false],
                ],
            ],
        ];

        $musicUrl = $this->remoteUrl($manifest['music_url'] ?? null);
        if ($musicUrl) {
            $payload['timeline']['soundtrack'] = [
                'src' => $musicUrl,
                'effect' => 'fadeInFadeOut',
                'volume' => 0.32,
            ];
        }

        $callback = trim((string) config('shotstack.callback_url', ''));
        if ($callback !== '') {
            $payload['callback'] = $callback;
        }

        return $payload;
    }

    private function format(string $aspectRatio): array
    {
        $formats = config('ai_video.formats', []);
        $format = $formats[$aspectRatio] ?? $formats['9:16'] ?? ['width' => 1080, 'height' => 1920];

        return [
            'width' => (int) $format['width'],
            'height' => (int) $format['height'],
        ];
    }

    private function assetUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (preg_match('/^[A-Za-z]:\\\\/', $path) || str_starts_with($path, '/')) {
            return $path;
        }

        $url = Storage::disk((string) config('ai_video_platform.storage.disk', 'public'))->url($path);

        return $this->absoluteUrl($url);
    }

    private function remoteUrl(?string $url): ?string
    {
        if (!$url || preg_match('/^[A-Za-z]:\\\\/', $url) || str_starts_with($url, '/')) {
            return null;
        }

        return str_starts_with($url, 'http://') || str_starts_with($url, 'https://')
            ? $url
            : $this->absoluteUrl($url);
    }

    private function absoluteUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return rtrim((string) config('app.url'), '/') . '/' . ltrim($url, '/');
    }
}
