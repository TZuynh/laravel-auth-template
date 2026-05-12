<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Services\Marketing\CommercialImagePromptService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CommercialImagePromptServiceTest extends TestCase
{
    public function test_it_uses_gemini_as_image_prompt_brain_when_configured(): void
    {
        config([
            'services.gemini.api_key' => 'test-key',
            'services.gemini.model' => 'gemini-test',
        ]);
        Cache::flush();

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => json_encode([
                                'title' => 'Gallery Product Scene',
                                'concept' => 'A premium photographed scene.',
                                'prompt' => 'Ultra realistic luxury gallery interior, framed product mounted on textured concrete, warm cinematic spotlight, reflective floor, architectural shadows, deep spatial depth, rich material textures, photoreal camera perspective, no text.',
                                'negative_prompt' => 'blurry, low quality, random text, watermark, logo',
                                'style_tags' => ['photoreal', 'cinematic'],
                                'category' => 'artwork',
                                'scene' => 'Luxury gallery interior with framed product.',
                                'environment' => 'Textured concrete wall and polished floor.',
                                'camera' => '35mm lens, slightly low perspective.',
                                'lighting' => 'Warm cinematic spotlight.',
                                'mood' => 'expensive, quiet, refined',
                                'colors' => ['charcoal', 'warm amber'],
                            ], JSON_UNESCAPED_SLASHES),
                        ]],
                    ],
                ]],
            ]),
        ]);

        $package = app(CommercialImagePromptService::class)->build(new Product([
            'name' => 'Wall Art',
            'category' => 'artwork',
        ]), [
            'prompt' => 'premium gallery product',
            'style' => 'luxury_editorial',
            'aspect_ratio' => '9:16',
        ]);

        $this->assertSame('gemini', $package['engine']);
        $this->assertSame('gemini-test', $package['engine_model']);
        $this->assertStringContainsString('luxury gallery interior', $package['prompt']);
        $this->assertStringContainsString('typography', $package['negative_prompt']);
    }

    public function test_it_falls_back_to_local_rules_without_gemini_key(): void
    {
        config(['services.gemini.api_key' => '']);
        Cache::flush();
        Http::fake();

        $package = app(CommercialImagePromptService::class)->build(null, [
            'prompt' => 'skincare serum on glass',
            'style' => 'premium_packshot',
            'aspect_ratio' => '1:1',
        ]);

        $this->assertSame('local_rules', $package['engine']);
        $this->assertNotEmpty($package['prompt']);
        Http::assertNothingSent();
    }
}
