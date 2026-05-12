<?php

namespace App\DTOs\AiVideo;

use App\Models\VideoVersion;

final readonly class BulkVideoOutputData
{
    public function __construct(
        public string $title,
        public string $style,
        public string $duration,
        public array $scenes,
        public ?string $voice,
        public ?string $music,
        public ?string $subtitleStyle,
        public array $timelineJson,
    ) {
    }

    public static function fromVersion(VideoVersion $version): self
    {
        $version->loadMissing('videoProject.scenes');

        return new self(
            title: $version->title,
            style: $version->style_name,
            duration: number_format((float) $version->duration_seconds, 1) . 's',
            scenes: $version->videoProject?->scenes?->map(fn ($scene): array => [
                'title' => $scene->title,
                'duration' => (float) $scene->duration_seconds,
                'voice_over' => $scene->voice_over_text,
                'subtitle' => $scene->subtitle_text,
                'camera' => $scene->camera_movement,
                'transition' => $scene->transition?->slug ?? data_get($scene->metadata, 'transition_type'),
            ])->values()->all() ?? [],
            voice: $version->voice,
            music: $version->music,
            subtitleStyle: $version->subtitle_style,
            timelineJson: $version->timeline_json ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'style' => $this->style,
            'duration' => $this->duration,
            'scenes' => $this->scenes,
            'voice' => $this->voice,
            'music' => $this->music,
            'subtitle_style' => $this->subtitleStyle,
            'timeline_json' => $this->timelineJson,
        ];
    }
}
