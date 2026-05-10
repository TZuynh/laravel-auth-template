<?php

namespace App\Services\AI\Contracts;

use App\Services\AI\AiProviderResponse;

interface AiProviderInterface
{
    public function key(): string;

    public function supports(string $capability): bool;

    public function generate(string $capability, array $payload): AiProviderResponse;
}
