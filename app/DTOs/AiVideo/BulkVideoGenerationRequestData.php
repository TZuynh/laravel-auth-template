<?php

namespace App\DTOs\AiVideo;

final readonly class BulkVideoGenerationRequestData
{
    public function __construct(
        public string $prompt,
        public string $styleSlug,
        public string $language,
        public string $aspectRatio,
        public int $durationSeconds,
        public string $provider,
        public string $renderProvider,
        public bool $renderImmediately,
        public array $sceneOverrides,
        public array $editorSettings,
    ) {
    }

    public static function fromValidated(array $data): self
    {
        return new self(
            prompt: trim((string) $data['prompt']),
            styleSlug: (string) ($data['style_slug'] ?? 'ai_studio'),
            language: (string) ($data['language'] ?? app()->getLocale() ?: 'en'),
            aspectRatio: (string) ($data['aspect_ratio'] ?? '9:16'),
            durationSeconds: (int) ($data['duration_seconds'] ?? 30),
            provider: (string) ($data['provider'] ?? config('bulk_ai_video.generation.asset_provider', 'kling')),
            renderProvider: (string) ($data['render_provider'] ?? config('bulk_ai_video.render.default_provider', 'ffmpeg')),
            renderImmediately: (bool) ($data['render_immediately'] ?? true),
            sceneOverrides: self::decodeJsonArray($data['scene_overrides'] ?? null),
            editorSettings: self::decodeJsonArray($data['editor_settings'] ?? null),
        );
    }

    public function toSettings(): array
    {
        return [
            'source' => 'bulk_ai_video_generator',
            'style_slug' => $this->styleSlug,
            'provider' => $this->provider,
            'render_provider' => $this->renderProvider,
            'render_immediately' => $this->renderImmediately,
            'scene_overrides' => $this->sceneOverrides,
            'editor_settings' => $this->editorSettings,
        ];
    }

    private static function decodeJsonArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }
}
