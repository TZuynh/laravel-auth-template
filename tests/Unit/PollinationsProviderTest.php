<?php

namespace Tests\Unit;

use App\Services\AI\Providers\PollinationsProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PollinationsProviderTest extends TestCase
{
    public function test_it_generates_image_binary_from_pollinations_flux_endpoint(): void
    {
        config([
            'ai_providers.providers.pollinations.api_key' => 'test-key',
            'ai_providers.providers.pollinations.base_url' => 'https://gen.pollinations.ai',
            'ai_providers.providers.pollinations.image_model' => 'flux',
            'ai_providers.providers.pollinations.seed' => 0,
            'ai_providers.providers.pollinations.enhance' => false,
            'ai_providers.providers.pollinations.timeout' => 180,
        ]);

        Http::fake([
            'https://gen.pollinations.ai/image/*' => Http::response('fake-image-binary', 200, [
                'Content-Type' => 'image/png',
            ]),
        ]);

        $response = app(PollinationsProvider::class)->generate('image', [
            'prompt' => 'luxury gallery scene',
            'model' => 'flux_schnell',
            'aspect_ratio' => '16:9',
        ]);

        $this->assertSame('pollinations', $response->provider);
        $this->assertSame('image', $response->capability);
        $this->assertSame('fake-image-binary', $response->data['binary']);
        $this->assertSame('image/png', $response->data['mime']);
        $this->assertSame('flux', $response->data['model']);
        $this->assertSame(1024, $response->data['width']);
        $this->assertSame(576, $response->data['height']);

        Http::assertSent(fn ($request): bool => str_starts_with($request->url(), 'https://gen.pollinations.ai/image/luxury%20gallery%20scene?')
            && str_contains($request->url(), 'model=flux')
            && str_contains($request->url(), 'width=1024')
            && str_contains($request->url(), 'height=576')
            && str_contains($request->url(), 'seed=0')
            && str_contains($request->url(), 'enhance=false')
            && str_contains($request->url(), 'key=test-key'));
    }
}
