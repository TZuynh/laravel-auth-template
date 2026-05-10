<?php

namespace App\Services\AiVideo;

use App\DTOs\AiVideo\TimelineManifestData;
use App\Models\SceneAsset;
use App\Models\VideoProject;
use Illuminate\Support\Facades\Storage;

class TimelineManifestBuilder
{
    public function build(VideoProject $project): TimelineManifestData
    {
        $project->loadMissing(['scenes.sceneAssets']);
        $format = $this->format($project->aspect_ratio?->value ?? (string) $project->aspect_ratio ?: '9:16');
        $cursor = 0.0;

        $scenes = $project->scenes->map(function ($scene) use (&$cursor): array {
            $duration = (float) $scene->duration_seconds;
            $asset = $scene->sceneAssets
                ->first(fn (SceneAsset $asset): bool => in_array((string) $asset->type?->value, ['generated_video', 'generated_image', 'video', 'image'], true));

            $payload = [
                'id' => $scene->id,
                'sort_order' => $scene->sort_order,
                'start' => round($cursor, 3),
                'duration' => $duration,
                'title' => $scene->title,
                'subtitle' => $scene->subtitle_text ?: $scene->voice_over_text,
                'voice_over' => $scene->voice_over_text,
                'camera' => $scene->camera_movement,
                'transition' => $scene->transition?->slug ?? data_get($scene->metadata, 'transition_type', 'bloom_cut'),
                'visual_url' => $asset?->path ? $this->publicUrl($asset->path) : null,
            ];

            $cursor += $duration;

            return $payload;
        })->values()->all();

        return new TimelineManifestData(
            projectId: $project->id,
            aspectRatio: $format['aspect_ratio'],
            width: $format['width'],
            height: $format['height'],
            fps: (int) config('ai_video.ffmpeg.fps', 30),
            scenes: $scenes,
        );
    }

    private function format(string $aspectRatio): array
    {
        $formats = config('ai_video.formats', []);
        $format = $formats[$aspectRatio] ?? $formats['9:16'] ?? ['width' => 1080, 'height' => 1920];

        return [
            'aspect_ratio' => $aspectRatio,
            'width' => (int) $format['width'],
            'height' => (int) $format['height'],
        ];
    }

    private function publicUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk((string) config('ai_video_platform.storage.disk', 'public'))->url($path);
    }
}

