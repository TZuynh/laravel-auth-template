<?php

namespace App\Services\AI;

use App\Enums\AiPromptStatus;
use App\Enums\AiPromptType;
use App\Models\AiPrompt;
use App\Services\AI\Contracts\AiProviderInterface;
use App\Services\AI\Providers\ElevenLabsProvider;
use App\Services\AI\Providers\KlingProvider;
use App\Services\AI\Providers\OpenAiProvider;
use App\Services\AI\Providers\RunwayProvider;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;
use Throwable;

class AiProviderManager
{
    /** @var array<string, AiProviderInterface> */
    private array $providers;

    public function __construct()
    {
        $this->providers = [
            'openai' => app(OpenAiProvider::class),
            'kling' => app(KlingProvider::class),
            'runway' => app(RunwayProvider::class),
            'elevenlabs' => app(ElevenLabsProvider::class),
        ];
    }

    public function generate(string $capability, array $payload, ?string $preferredProvider = null): AiProviderResponse
    {
        $payload['prompt'] = $this->optimizePrompt((string) ($payload['prompt'] ?? ''), $capability, $payload);
        $providers = $this->providerOrder($capability, $preferredProvider);
        $lastException = null;

        foreach ($providers as $providerKey) {
            $provider = $this->providers[$providerKey] ?? null;

            if (!$provider || !$provider->supports($capability)) {
                continue;
            }

            try {
                $this->guardRateLimit($provider);
                $response = $provider->generate($capability, $payload);
                $this->recordUsage($payload, $response);

                return $response;
            } catch (Throwable $exception) {
                $lastException = $exception;
                $this->recordFailure($payload, $providerKey, $capability, $exception);
            }
        }

        throw new RuntimeException(
            'All AI providers failed for ' . $capability . ': ' . ($lastException?->getMessage() ?? 'no provider available')
        );
    }

    public function optimizePrompt(string $prompt, string $capability, array $context = []): string
    {
        $style = $context['style'] ?? 'cinematic';
        $aspect = $context['aspect_ratio'] ?? '9:16';

        if ($capability === 'voice') {
            return trim($prompt);
        }

        $cinematicDirectives = [
            'premium ecommerce commercial',
            'realistic lighting',
            'parallax depth',
            'motion blur',
            'depth of field',
            'cinematic color grading',
            'not a flat slideshow',
            "style={$style}",
            "aspect={$aspect}",
        ];

        return trim($prompt . "\n\nCinematic directives: " . implode(', ', $cinematicDirectives) . '.');
    }

    private function providerOrder(string $capability, ?string $preferredProvider): array
    {
        $fallbacks = config("ai_providers.fallbacks.{$capability}", []);

        if ($preferredProvider) {
            return array_values(array_unique([$preferredProvider, ...$fallbacks]));
        }

        $default = (string) config('ai_providers.default', 'openai');

        return array_values(array_unique([$default, ...$fallbacks]));
    }

    private function guardRateLimit(AiProviderInterface $provider): void
    {
        $limits = config("ai_providers.rate_limits.{$provider->key()}", ['max_attempts' => 30, 'decay_seconds' => 60]);
        $key = 'ai-provider:' . $provider->key() . ':' . now()->format('YmdHi');

        if (RateLimiter::tooManyAttempts($key, (int) $limits['max_attempts'])) {
            throw new RuntimeException("Rate limit reached for {$provider->key()} provider. Retry in " . RateLimiter::availableIn($key) . ' seconds.');
        }

        RateLimiter::hit($key, (int) $limits['decay_seconds']);
    }

    private function recordUsage(array $payload, AiProviderResponse $response): void
    {
        if (empty($payload['video_project_id'])) {
            return;
        }

        AiPrompt::create([
            'video_project_id' => $payload['video_project_id'],
            'video_scene_id' => $payload['video_scene_id'] ?? null,
            'type' => $this->promptType($response->capability),
            'provider' => $response->provider,
            'model' => $payload['model'] ?? null,
            'prompt' => $payload['prompt'] ?? '',
            'response' => $this->safeResponseData($response->data),
            'tokens_used' => $response->tokensUsed,
            'cost' => $response->cost,
            'status' => AiPromptStatus::Completed,
        ]);
    }

    private function recordFailure(array $payload, string $provider, string $capability, Throwable $exception): void
    {
        if (empty($payload['video_project_id'])) {
            return;
        }

        AiPrompt::create([
            'video_project_id' => $payload['video_project_id'],
            'video_scene_id' => $payload['video_scene_id'] ?? null,
            'type' => $this->promptType($capability),
            'provider' => $provider,
            'model' => $payload['model'] ?? null,
            'prompt' => $payload['prompt'] ?? '',
            'response' => ['error' => $exception->getMessage()],
            'status' => AiPromptStatus::Failed,
        ]);
    }

    private function promptType(string $capability): AiPromptType
    {
        return match ($capability) {
            'text' => AiPromptType::Script,
            'image' => AiPromptType::Image,
            'video' => AiPromptType::Video,
            'voice' => AiPromptType::VoiceOver,
            default => AiPromptType::Optimization,
        };
    }

    private function safeResponseData(array $data): array
    {
        if (array_key_exists('binary', $data)) {
            $data['binary'] = '[binary omitted]';
        }

        return $data;
    }
}
