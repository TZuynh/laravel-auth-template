<?php

namespace App\DTOs\AiVideo;

final readonly class TimelineManifestData
{
    public function __construct(
        public int $projectId,
        public string $aspectRatio,
        public int $width,
        public int $height,
        public int $fps,
        public array $scenes,
        public ?string $musicUrl = null,
        public ?string $voiceUrl = null,
        public ?string $title = null,
        public ?string $style = null,
        public ?string $music = null,
        public ?string $subtitleStyle = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'project_id' => $this->projectId,
            'aspect_ratio' => $this->aspectRatio,
            'width' => $this->width,
            'height' => $this->height,
            'fps' => $this->fps,
            'scenes' => $this->scenes,
            'music_url' => $this->musicUrl,
            'voice_url' => $this->voiceUrl,
            'title' => $this->title,
            'style' => $this->style,
            'music' => $this->music,
            'subtitle_style' => $this->subtitleStyle,
        ];
    }
}
