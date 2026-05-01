<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Repositories\Eloquent\ProductRepository;
use App\Services\ProductExportTranslationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use ReflectionMethod;
use Tests\TestCase;

class ProductRepositoryTest extends TestCase
{
    public function test_default_english_export_keeps_base_vnd_price_for_round_trip(): void
    {
        config([
            'services.gemini.api_key' => '',
            'services.product_export.ai_translation' => false,
            'services.product_export.usd_rate' => 2632.84,
        ]);

        $export = $this->repository()->transformProductForExport($this->product(), 'en');

        $this->assertSame('41.78', $export['price']);
        $this->assertSame('USD', $export['price_currency']);
        $this->assertSame('110000.00', $export['price_vnd']);
    }

    public function test_woocommerce_english_export_includes_currency_metadata(): void
    {
        config([
            'services.gemini.api_key' => '',
            'services.product_export.ai_translation' => false,
            'services.product_export.usd_rate' => 2632.84,
        ]);

        $repository = $this->repository();
        $headers = $repository->exportHeaders('woocommerce', 'en');
        $row = $repository->exportRow($this->product(), 'woocommerce', 'en');

        $this->assertCount(count($headers), $row);
        $this->assertContains('41.78', $row);
        $this->assertSame('USD', $row[array_search('Meta: price_currency', $headers, true)]);
        $this->assertSame('110000.00', $row[array_search('Meta: price_vnd', $headers, true)]);
    }

    public function test_woocommerce_price_keeps_symbol_when_requested(): void
    {
        config([
            'services.gemini.api_key' => '',
            'services.product_export.ai_translation' => false,
            'services.product_export.usd_rate' => 2632.84,
        ]);

        $row = $this->repository()->exportRow($this->product(), 'woocommerce', 'en', [
            'show_currency_symbol' => true,
        ]);

        $this->assertContains('$41.78', $row);
    }

    public function test_import_price_prefers_base_vnd_and_can_convert_usd_metadata(): void
    {
        config([
            'services.gemini.api_key' => '',
            'services.product_export.usd_rate' => 2632.84,
        ]);

        $repository = $this->repository();

        $this->assertSame('price_currency', $this->invokePrivate($repository, 'normalizeHeader', ['Meta: price_currency']));
        $this->assertSame('price_vnd', $this->invokePrivate($repository, 'normalizeHeader', ['Meta: price_vnd']));

        $this->assertSame('110000.00', $this->invokePrivate($repository, 'resolveImportPrice', [[
            'price_regular' => '41.78',
            'price_currency' => 'USD',
            'price_vnd' => '110000.00',
        ]]));

        $this->assertSame('110000.00', $this->invokePrivate($repository, 'resolveImportPrice', [[
            'price_regular' => '41.78',
            'price_currency' => 'USD',
        ]]));
    }

    public function test_english_export_uses_gemini_for_all_export_text_fields(): void
    {
        Cache::flush();
        Http::fake([
            '*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => json_encode([
                                'name' => 'Men cotton shirt',
                                'category' => 'Men fashion',
                                'brand' => 'Viet Brand',
                                'tags' => 'shirt|summer',
                                'status' => 'active',
                                'product_form' => 'simple',
                                'seo_title' => 'Men cotton shirt',
                                'seo_description' => 'Soft cotton shirt for summer.',
                            ]),
                        ]],
                    ],
                ]],
            ]),
        ]);

        config([
            'services.gemini.api_key' => 'test-api-key',
            'services.gemini.model' => 'gemini-test',
            'services.product_export.ai_translation' => true,
            'services.product_export.usd_rate' => 25000,
        ]);

        $product = new Product([
            'name' => 'Áo sơ mi cotton nam',
            'sku' => 'VN-SHIRT-1',
            'image' => 'products/shirt.jpg',
            'price' => '125000.00',
            'stock' => 7,
            'category' => 'Thời trang nam',
            'brand' => 'Thương hiệu Việt',
            'tags' => ['áo sơ mi', 'mùa hè'],
            'featured' => false,
            'synced_to_meta' => false,
            'status' => 'active',
            'product_form' => 'simple',
            'seo_title' => 'Áo sơ mi cotton nam',
            'seo_description' => 'Áo cotton mềm cho mùa hè.',
        ]);
        $product->id = 700;

        $export = $this->repository()->transformProductForExport($product, 'en');

        $this->assertSame('Men cotton shirt', $export['name']);
        $this->assertSame('Men fashion', $export['category']);
        $this->assertSame('Viet Brand', $export['brand']);
        $this->assertSame('shirt|summer', $export['tags']);
        $this->assertSame('Men cotton shirt', $export['seo_title']);
        $this->assertSame('Soft cotton shirt for summer.', $export['seo_description']);
        $this->assertSame('5.00', $export['price']);
        $this->assertSame('125000.00', $export['price_vnd']);
        Http::assertSentCount(1);
    }

    private function repository(): ProductRepository
    {
        return new ProductRepository(new ProductExportTranslationService());
    }

    private function product(): Product
    {
        $product = new Product([
            'name' => 'Tranh treo tuong',
            'sku' => 'RentArtPrint-1',
            'image' => 'products/example.jpg',
            'price' => '110000.00',
            'stock' => 1,
            'category' => 'Mua Dong - Giang Sinh',
            'brand' => null,
            'tags' => [],
            'featured' => false,
            'synced_to_meta' => false,
            'status' => 'active',
            'product_form' => 'simple',
        ]);
        $product->id = 617;

        return $product;
    }

    private function invokePrivate(object $object, string $method, array $arguments): mixed
    {
        $reflection = new ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($object, $arguments);
    }
}
