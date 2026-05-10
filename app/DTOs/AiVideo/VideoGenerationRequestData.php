<?php

namespace App\DTOs\AiVideo;

final readonly class VideoGenerationRequestData
{
    public function __construct(
        public ?int $productId,
        public string $prompt,
        public string $template,
        public string $language,
        public string $style,
        public string $aspectRatio,
        public int $durationSeconds,
        public string $voice,
        public string $music,
        public string $provider,
        public bool $renderImmediately,
    ) {
    }

    public static function fromValidated(array $data): self
    {
        return new self(
            productId: isset($data['product_id']) ? (int) $data['product_id'] : null,
            prompt: trim((string) ($data['prompt'] ?? '')),
            template: (string) ($data['template'] ?? 'product_showcase'),
            language: (string) ($data['language'] ?? app()->getLocale()),
            style: (string) ($data['style'] ?? 'cinematic'),
            aspectRatio: (string) ($data['aspect_ratio'] ?? '9:16'),
            durationSeconds: (int) ($data['duration_seconds'] ?? 15),
            voice: (string) ($data['voice'] ?? 'female_south'),
            music: (string) ($data['music'] ?? 'tiktok'),
            provider: (string) ($data['provider'] ?? 'auto'),
            renderImmediately: (bool) ($data['render_immediately'] ?? false),
        );
    }

    public function toProjectSettings(): array
    {
        return [
            'template' => $this->template,
            'voice' => $this->voice,
            'music' => $this->music,
            'provider' => $this->provider,
            'source' => 'api',
        ];
    }
}

