<?php

namespace App\Services\AI;

class AiProviderResponse
{
    public function __construct(
        public readonly string $provider,
        public readonly string $capability,
        public readonly array $data,
        public readonly int $tokensUsed = 0,
        public readonly float $cost = 0.0,
    ) {
    }

    public function text(): ?string
    {
        return $this->data['text'] ?? null;
    }

    public function assetUrl(): ?string
    {
        return $this->data['url'] ?? $this->data['asset_url'] ?? null;
    }
}
