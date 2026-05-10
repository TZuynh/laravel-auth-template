<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\ProductExportTranslationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use SplFileObject;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private readonly ProductExportTranslationService $productExportTranslationService,
    ) {
    }

    private const DEFAULT_EXPORT_HEADERS = [
        'id',
        'name',
        'sku',
        'image',
        'price',
        'price_currency',
        'price_vnd',
        'stock',
        'category',
        'brand',
        'tags',
        'featured',
        'synced_to_meta',
        'status',
        'product_form',
        'published_at',
        'seo_title',
        'seo_description',
        'created_at',
        'updated_at',
    ];

    private const WOOCOMMERCE_EXPORT_HEADERS = [
        'ID', 'Loại', 'SKU', 'GTIN, UPC, EAN hoặc ISBN', 'Tên', 'Đã xuất bản', 'Là sản phẩm nổi bật?', 'Hiển thị trong danh mục',
        'Mô tả ngắn', 'Mô tả', 'Ngày bắt đầu giá khuyến mãi', 'Ngày kết thúc giá khuyến mãi', 'Trạng thái thuế', 'Lớp thuế', 'Còn hàng?',
        'Tồn kho', 'Số lượng sắp hết hàng', 'Cho phép đặt hàng trước?', 'Bán riêng lẻ?', 'Cân nặng (kg)', 'Chiều dài (cm)', 'Chiều rộng (cm)',
        'Chiều cao (cm)', 'Cho phép khách hàng đánh giá?', 'Ghi chú mua hàng', 'Giá khuyến mãi', 'Giá thông thường', 'Danh mục', 'Thẻ',
        'Lớp vận chuyển', 'Hình ảnh', 'Giới hạn tải xuống', 'Ngày hết hạn tải xuống', 'Cha', 'Sản phẩm theo nhóm', 'Bán thêm', 'Bán chéo',
        'URL bên ngoài', 'Văn bản nút', 'Vị trí', 'Thương hiệu', 'WCPA Forms', 'Meta: hwp_product_gtin', 'Meta: rank_math_internal_links_processed',
        'Meta: _wc_gla_synced_at', 'Meta: _wc_gla_sync_status', 'Meta: _wc_gla_visibility', 'Meta: _wc_gla_notification_status',
        'Meta: _wc_gla_mc_status', 'Meta: rank_math_seo_score', 'Meta: wcpa_exclude_global_forms', 'Meta: rank_math_primary_product_brand',
        'Meta: rank_math_primary_product_cat', 'Meta: rank_math_focus_keyword', 'Meta: rank_math_pillar_content', 'Meta: _thumbnail_id_watermarked',
        'Meta: _last_change_time', 'Meta: _wc_facebook_sync_enabled_v2', 'Meta: fb_visibility', 'Meta: fb_product_description',
        'Meta: fb_rich_text_description', 'Meta: _wc_facebook_product_image_source', 'Meta: fb_brand', 'Meta: fb_mpn', 'Meta: fb_size',
        'Meta: fb_color', 'Meta: fb_material', 'Meta: fb_pattern', 'Meta: fb_age_group', 'Meta: fb_gender', 'Meta: fb_product_condition',
        'Meta: price_currency', 'Meta: price_vnd',
    ];

    public function paginateBySearch(?string $query, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $search = trim((string) $query);

        $builder = Product::query()->select([
            'id',
            'name',
            'sku',
            'image',
            'price',
            'stock',
            'category',
            'brand',
            'featured',
            'synced_to_meta',
            'status',
            'product_form',
            'published_at',
            'seo_title',
            'seo_description',
            'created_at',
        ]);

        $this->applySearch($builder, $search);
        $this->applyFilters($builder, $filters);
        $this->applySorting($builder, $filters);

        return $builder->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Product
    {
        return Product::create($this->normalizePayload($data));
    }

    public function update(Product $product, array $data): Product
    {
        $oldImage = $product->image;
        $product->update($this->normalizePayload($data, $product));

        if ($oldImage && $product->image !== $oldImage && $this->isLocalImagePath($oldImage)) {
            Storage::disk('public')->delete($oldImage);
        }

        return $product->refresh();
    }

    public function delete(Product $product): bool|null
    {
        if ($product->image && $this->isLocalImagePath($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        return $product->delete();
    }

    public function deleteAll(): int
    {
        Product::query()
            ->select(['id', 'image'])
            ->chunkById(1000, function ($products) {
                foreach ($products as $product) {
                    if ($product->image && $this->isLocalImagePath($product->image)) {
                        Storage::disk('public')->delete($product->image);
                    }
                }
            });

        return Product::query()->delete();
    }

    public function exportQuery(?string $query, array $filters = []): Builder
    {
        $search = trim((string) $query);

        $builder = Product::query()->select([
            'id',
            'name',
            'sku',
            'image',
            'price',
            'stock',
            'category',
            'brand',
            'tags',
            'featured',
            'synced_to_meta',
            'status',
            'product_form',
            'published_at',
            'seo_title',
            'seo_description',
            'created_at',
            'updated_at',
        ]);

        $this->applySearch($builder, $search);
        $this->applyFilters($builder, $filters);
        $this->applySorting($builder, $filters);

        return $builder;
    }

    public function transformProductForExport(Product $product, string $locale, array $options = []): array
    {
        $locale = in_array($locale, ['vi', 'en'], true) ? $locale : app()->getLocale();
        $translatedFields = $this->resolveTranslatedExportFields($product, $locale);

        return [
            'id' => $product->id,
            'name' => $translatedFields['name'] ?? $this->translateForExport($product->name, $locale),
            'sku' => $product->sku,
            'image' => $product->image,
            'price' => $this->convertPriceForExport($product->price, $locale, $options),
            // Helps round-trip: when exporting EN we output USD in `price`,
            // and can convert it back to VND on import.
            'price_currency' => $locale === 'en' ? 'USD' : 'VND',
            // Preserve the base storage value so exported files can be imported
            // without losing VND precision after display-currency rounding.
            'price_vnd' => $this->normalizeDecimal($product->price),
            'stock' => $product->stock,
            'category' => $translatedFields['category'] ?? $this->translateForExport($product->category, $locale),
            'brand' => $translatedFields['brand'] ?? $this->translateForExport($product->brand, $locale),
            'tags' => $translatedFields['tags'] ?? $this->transformTagsForExport($product->tags, $locale),
            'featured' => $product->featured ? 1 : 0,
            'synced_to_meta' => $product->synced_to_meta ? 1 : 0,
            'status' => $translatedFields['status'] ?? $this->translateStatusForExport($product->status, $locale),
            'product_form' => $translatedFields['product_form'] ?? $this->translateForExport($product->product_form, $locale),
            'published_at' => optional($product->published_at)->format('Y-m-d'),
            'seo_title' => $translatedFields['seo_title'] ?? $this->translateForExport($product->seo_title, $locale),
            'seo_description' => $translatedFields['seo_description'] ?? $this->translateForExport($product->seo_description, $locale),
            'created_at' => optional($product->created_at)->toDateTimeString(),
            'updated_at' => optional($product->updated_at)->toDateTimeString(),
        ];
    }

    public function transformProductForDisplay(Product $product, string $locale, array $options = []): array
    {
        $locale = in_array($locale, ['vi', 'en'], true) ? $locale : app()->getLocale();

        return [
            'name' => $this->translateForExport($product->name, $locale),
            'price' => $this->convertPriceForExport($product->price, $locale, $options),
            'category' => $this->translateForExport($product->category, $locale),
            'brand' => $this->translateForExport($product->brand, $locale),
            'status' => $this->translateStatusForExport($product->status, $locale),
        ];
    }

    public function exportHeaders(string $format, string $locale, array $options = []): array
    {
        return $format === 'woocommerce'
            ? self::WOOCOMMERCE_EXPORT_HEADERS
            : self::DEFAULT_EXPORT_HEADERS;
    }

    public function exportRow(Product $product, string $format, string $locale, array $options = []): array
    {
        return $format === 'woocommerce'
            ? $this->transformProductForWooCommerceExport($product, $locale, $options)
            : array_values($this->transformProductForExport($product, $locale, $options));
    }

    public function importCsv(UploadedFile $file): array
    {
        return DB::transaction(function () use ($file) {
            $csv = new SplFileObject($file->getRealPath());
            $csv->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

            $delimiter = $this->detectDelimiter($file->getRealPath());
            $csv->setCsvControl($delimiter, '"', '\\');

            $headers = [];
            $buffer = [];
            $rowsProcessed = 0;
            $batchCount = 0;

            foreach ($csv as $index => $row) {
                if (!is_array($row) || $row === [null] || $row === false || $this->isEffectivelyEmptyRow($row)) {
                    continue;
                }

                if ($index === 0) {
                    $headers = $this->normalizeHeaders($row);
                    $this->assertRequiredHeaders($headers);
                    continue;
                }

                $normalizedRow = $this->normalizeRowColumnCount($row, count($headers), $index + 1);
                $payload = $this->mapCsvRow($headers, $normalizedRow, $index + 1);
                $buffer[] = $payload;

                if (count($buffer) >= 500) {
                    $this->upsertBatch($buffer);
                    $rowsProcessed += count($buffer);
                    $batchCount++;
                    $buffer = [];
                }
            }

            if ($buffer !== []) {
                $this->upsertBatch($buffer);
                $rowsProcessed += count($buffer);
                $batchCount++;
            }

            return [
                'rows' => $rowsProcessed,
                'batches' => $batchCount,
            ];
        });
    }

    private function upsertBatch(array $rows): void
    {
        $now = now();

        foreach ($rows as &$row) {
            $row['updated_at'] = $now;
            $row['created_at'] = $row['created_at'] ?? $now;
        }

        Product::upsert(
            $rows,
            ['id'],
            [
                'name',
                'sku',
                'image',
                'price',
                'stock',
                'category',
                'brand',
                'tags',
                'featured',
                'synced_to_meta',
                'status',
                'product_form',
                'published_at',
                'seo_title',
                'seo_description',
                'updated_at',
            ]
        );
    }

    private function normalizePayload(array $data, ?Product $currentProduct = null): array
    {
        $tags = $data['tags'] ?? null;
        $image = $data['image'] ?? null;

        if (is_string($tags)) {
            $tags = $this->parseTags($tags);
        }

        if ($image instanceof UploadedFile) {
            $image = $image->store('products', 'public');
        } elseif (is_string($image)) {
            $image = trim($image);
            $image = $image === '' ? null : $image;
        } else {
            $image = null;
        }

        if ($image === null && $currentProduct instanceof Product) {
            $image = $currentProduct->image;
        }

        return [
            'name' => trim((string) $data['name']),
            'sku' => trim((string) $data['sku']),
            'image' => $image,
            'price' => $this->normalizeDecimal($data['price'] ?? null),
            'stock' => (int) ($data['stock'] ?? 0),
            'category' => $this->normalizeNullableString($data['category'] ?? null),
            'brand' => $this->normalizeNullableString($data['brand'] ?? null),
            'tags' => $tags === null ? null : array_values($tags),
            'featured' => (bool) ($data['featured'] ?? false),
            'synced_to_meta' => (bool) ($data['synced_to_meta'] ?? false),
            'status' => $data['status'] ?? 'active',
            'product_form' => $this->normalizeNullableString($data['product_form'] ?? null),
            'published_at' => $this->normalizeNullableDate($data['published_at'] ?? null),
            'seo_title' => $this->normalizeNullableString($data['seo_title'] ?? null),
            'seo_description' => $this->normalizeNullableString($data['seo_description'] ?? null),
        ];
    }

    private function normalizeHeaders(array $row): array
    {
        return array_map(function ($value) {
            return $this->normalizeHeader((string) $value);
        }, $row);
    }

    private function normalizeHeader(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
        $value = Str::lower($value);
        $value = Str::ascii($value);
        $value = preg_replace('/[^a-z0-9]+/', '_', $value);
        $value = trim((string) $value, '_');

        return match ($value) {
            'product_id', 'item_id' => 'id',
            'product_name', 'item_name', 'title', 'product_title' => 'name',
            'ten', 'ho_ten' => 'name',
            'loai' => 'type',
            'da_xuat_ban' => 'published',
            'la_san_pham_noi_bat' => 'featured',
            'mo_ta_ngan' => 'short_description',
            'mo_ta' => 'description',
            'con_hang' => 'in_stock',
            'sku_code', 'product_sku', 'variant_sku', 'seller_sku', 'item_sku' => 'sku',
            'ma_sku' => 'sku',
            'image', 'image_url', 'hinh', 'anh', 'hinh_anh', 'image_src', 'image_link', 'main_image', 'featured_image', 'thumbnail', 'thumbnail_image', 'image_1', 'image1', 'images' => 'image',
            'qty', 'quantity', 'inventory', 'inventory_quantity', 'available_quantity', 'so_luong' => 'stock',
            'ton_kho' => 'stock',
            'price_sale', 'sale_price', 'product_price', 'variant_price', 'list_price', 'gia_ban' => 'price',
            'gia', 'gia_thong_thuong' => 'price_regular',
            'gia_khuyen_mai' => 'price_sale',
            'price_vnd', 'base_price', 'base_price_vnd', 'original_price', 'original_price_vnd', 'meta_price_vnd', 'meta_base_price_vnd', 'meta_original_price_vnd' => 'price_vnd',
            'price_currency', 'currency', 'currency_code', 'meta_price_currency', 'meta_currency', 'meta_currency_code' => 'price_currency',
            'product_type', 'product_category', 'collection', 'collections', 'categories' => 'category',
            'danh_muc' => 'category',
            'vendor', 'manufacturer', 'brands', 'nhan_hieu', 'hang' => 'brand',
            'thuong_hieu' => 'brand',
            'the' => 'tags',
            'noi_bat' => 'featured',
            'synced_to_meta_catalog', 'meta_catalog', 'filter_by_synced_to_meta' => 'synced_to_meta',
            'state', 'publish_status', 'stock_status' => 'status',
            'trang_thai' => 'status',
            'ngay', 'date' => 'published_at',
            'meta_title' => 'seo_title',
            'chi_tiet_seo', 'seo_chi_tiet' => 'seo_title',
            'meta_description' => 'seo_description',
            'seo_mo_ta' => 'seo_description',
            'type', 'form' => 'product_form',
            'product_forms' => 'product_form',
            default => $value,
        };
    }

    private function assertRequiredHeaders(array $headers): void
    {
        foreach (['id', 'name', 'sku'] as $required) {
            if (!in_array($required, $headers, true)) {
                throw new RuntimeException("Missing required CSV header: {$required}");
            }
        }
    }

    private function mapCsvRow(array $headers, array $row, int $lineNumber): array
    {
        $combined = [];

        foreach ($headers as $index => $header) {
            $combined[$header] = $row[$index] ?? null;
        }

        $productId = $this->normalizeId($combined['id'] ?? null, $lineNumber);
        $productName = $this->normalizeRequiredString($combined['name'] ?? null, 'name', $lineNumber);

        $payload = [
            'id' => $productId,
            'name' => $productName,
            'sku' => $this->normalizeSkuForImport($combined['sku'] ?? null, $productId, $productName),
            'image' => $this->normalizeImageValue($combined['image'] ?? null),
            'price' => $this->resolveImportPrice($combined),
            'stock' => $this->resolveImportStock($combined),
            'category' => $this->normalizeNullableString($combined['category'] ?? null),
            'brand' => $this->normalizeNullableString($combined['brand'] ?? null),
            'tags' => $this->normalizeTagsForImport($combined['tags'] ?? null),
            'featured' => $this->normalizeBoolean($combined['featured'] ?? null),
            'synced_to_meta' => $this->normalizeBoolean($combined['synced_to_meta'] ?? null),
            'status' => $this->resolveImportStatus($combined),
            'product_form' => $this->normalizeNullableString($combined['product_form'] ?? $combined['type'] ?? null),
            'published_at' => $this->normalizeNullableDate($combined['published_at'] ?? null),
            'seo_title' => $this->normalizeNullableString($combined['seo_title'] ?? $combined['name'] ?? null),
            'seo_description' => $this->normalizeNullableString($combined['seo_description'] ?? $combined['short_description'] ?? null),
        ];

        return $payload;
    }

    private function normalizeId(mixed $value, int $lineNumber): int
    {
        if (!is_numeric($value) || (int) $value <= 0) {
            throw new RuntimeException("Invalid id at CSV line {$lineNumber}");
        }

        return (int) $value;
    }

    private function normalizeRequiredString(mixed $value, string $field, int $lineNumber): string
    {
        $text = trim((string) $value);

        if ($text === '') {
            throw new RuntimeException("Missing required {$field} at CSV line {$lineNumber}");
        }

        return $text;
    }

    private function normalizeSkuForImport(mixed $value, int $productId, string $productName): string
    {
        $text = trim((string) $value);

        if ($text !== '') {
            return $text;
        }

        $slug = Str::of($productName)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', '-')->trim('-')->limit(32, '')->toString();

        if ($slug === '') {
            return 'wc-' . $productId;
        }

        return 'wc-' . $productId . '-' . $slug;
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function normalizeDecimal(mixed $value): ?string
    {
        $text = trim((string) $value);

        if ($text === '') {
            return null;
        }

        $text = preg_replace('/[^\d,.\-]/u', '', $text) ?? '';

        if ($text === '') {
            return null;
        }

        $lastComma = strrpos($text, ',');
        $lastDot = strrpos($text, '.');

        if ($lastComma !== false && $lastDot !== false) {
            if ($lastComma > $lastDot) {
                $text = str_replace('.', '', $text);
                $text = str_replace(',', '.', $text);
            } else {
                $text = str_replace(',', '', $text);
            }
        } elseif ($lastComma !== false) {
            $fractionLength = strlen($text) - $lastComma - 1;
            if ($fractionLength > 0 && $fractionLength <= 2) {
                $text = str_replace('.', '', $text);
                $text = str_replace(',', '.', $text);
            } else {
                $text = str_replace(',', '', $text);
            }
        } elseif ($lastDot !== false) {
            $dotCount = substr_count($text, '.');

            if ($dotCount > 1) {
                // Likely thousand separators (e.g. 1.234.567) in VI-style files.
                $text = str_replace('.', '', $text);
            } else {
                $fractionLength = strlen($text) - $lastDot - 1;

                if ($fractionLength > 0 && $fractionLength <= 2) {
                    // Decimal dot (e.g. 41.78)
                    $text = str_replace(',', '', $text);
                } else {
                    // Thousand dot (e.g. 144.000 -> 144000)
                    $text = str_replace('.', '', $text);
                    $text = str_replace(',', '', $text);
                }
            }
        } else {
            $text = str_replace(',', '', $text);
        }

        return is_numeric($text) ? number_format((float) $text, 2, '.', '') : null;
    }

    private function normalizeBoolean(mixed $value): bool
    {
        $text = strtolower(trim((string) $value));

        return in_array($text, ['1', 'true', 'yes', 'y', 'on', 'active', 'co', 'có'], true);
    }

    private function normalizeStatus(mixed $value): string
    {
        $text = strtolower(trim((string) $value));

        return match ($text) {
            'inactive', 'outofstock', 'false', '0', 'ngung hoat dong', 'ngung', 'draft', 'disabled', 'private', 'pending' => 'inactive',
            default => 'active',
        };
    }

    private function resolveImportPrice(array $combined): ?string
    {
        $basePrice = $this->normalizeDecimal($combined['price_vnd'] ?? null);
        if ($basePrice !== null) {
            return $basePrice;
        }

        $saleRaw = $combined['price_sale'] ?? null;
        $regularRaw = $combined['price_regular'] ?? $combined['price'] ?? null;
        $isUsd = $this->isUsdRowForImport($combined, [$saleRaw, $regularRaw]);

        $salePrice = $this->normalizeDecimal($saleRaw);
        if ($salePrice !== null) {
            return $isUsd ? $this->convertUsdToVndForImport($salePrice) : $salePrice;
        }

        $regularPrice = $this->normalizeDecimal($regularRaw);
        if ($regularPrice !== null) {
            return $isUsd ? $this->convertUsdToVndForImport($regularPrice) : $regularPrice;
        }

        return null;
    }

    private function isUsdRowForImport(array $combined, array $rawCandidates = []): bool
    {
        $currencyCandidates = [
            $combined['price_currency'] ?? null,
            $combined['currency'] ?? null,
            $combined['currency_code'] ?? null,
            $combined['export_locale'] ?? null,
            $combined['locale'] ?? null,
        ];

        foreach ($currencyCandidates as $candidate) {
            $text = strtolower(trim((string) $candidate));
            if ($text === '') {
                continue;
            }

            if ($text === 'usd' || str_contains($text, 'usd')) {
                return true;
            }

            // Convenience: allow `en` to mean the price is USD if a file explicitly includes locale.
            if ($text === 'en' || str_contains($text, 'en')) {
                return true;
            }
        }

        foreach ($rawCandidates as $candidate) {
            $text = strtolower((string) $candidate);
            if ($text === '') {
                continue;
            }

            if (str_contains($text, '$') || str_contains($text, 'usd')) {
                return true;
            }
        }

        return false;
    }

    private function convertUsdToVndForImport(string $usdNormalized): string
    {
        $usdRate = (float) config('services.product_export.usd_rate', 25000);
        if ($usdRate <= 0) {
            $usdRate = 25000;
        }

        $vnd = ((float) $usdNormalized) * $usdRate;

        return number_format(round($vnd), 2, '.', '');
    }

    private function resolveImportStock(array $combined): int
    {
        $rawStock = trim((string) ($combined['stock'] ?? ''));
        if ($rawStock !== '' && is_numeric($rawStock)) {
            return max(0, (int) $rawStock);
        }

        $inStock = $this->normalizeBoolean($combined['in_stock'] ?? null);

        return $inStock ? 1 : 0;
    }

    private function resolveImportStatus(array $combined): string
    {
        $status = $this->normalizeStatus($combined['status'] ?? null);

        $published = trim((string) ($combined['published'] ?? ''));
        if ($published !== '') {
            return $this->normalizeBoolean($published) ? 'active' : 'inactive';
        }

        return $status;
    }

    private function normalizeNullableDate(mixed $value): ?string
    {
        $text = trim((string) $value);

        if ($text === '') {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($text)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeTagsForImport(mixed $value): ?string
    {
        $tags = $this->parseTags((string) $value);

        return $tags === [] ? null : json_encode(array_values($tags), JSON_UNESCAPED_UNICODE);
    }

    private function parseTags(string $value): array
    {
        $value = trim($value);

        if ($value === '') {
            return [];
        }

        $parts = preg_split('/[|,;]+/', $value) ?: [];
        $tags = [];

        foreach ($parts as $part) {
            $tag = trim($part);
            if ($tag !== '') {
                $tags[] = $tag;
            }
        }

        return array_values(array_unique($tags));
    }

    private function stringifyTags(mixed $tags): ?string
    {
        if (is_string($tags)) {
            $decoded = json_decode($tags, true);
            $tags = is_array($decoded) ? $decoded : $this->parseTags($tags);
        }

        if (!is_array($tags) || $tags === []) {
            return null;
        }

        $normalized = [];
        foreach ($tags as $tag) {
            $text = trim((string) $tag);
            if ($text !== '') {
                $normalized[] = $text;
            }
        }

        return $normalized === [] ? null : implode('|', array_values(array_unique($normalized)));
    }

    private function normalizeImageValue(mixed $value): ?string
    {
        $text = trim((string) $value);

        if ($text === '') {
            return null;
        }

        if (preg_match('/https?:\/\/[^\s,"\']+/i', $text, $matches)) {
            return $matches[0];
        }

        $parts = preg_split('/[|,;]+/', $text) ?: [];
        foreach ($parts as $part) {
            $candidate = trim($part);
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }

    private function isEffectivelyEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function normalizeRowColumnCount(array $row, int $headerCount, int $lineNumber): array
    {
        $currentCount = count($row);

        if ($currentCount < $headerCount) {
            return array_pad($row, $headerCount, null);
        }

        if ($currentCount > $headerCount) {
            $extraValues = array_slice($row, $headerCount);
            foreach ($extraValues as $value) {
                if (trim((string) $value) !== '') {
                    throw new RuntimeException("Unexpected extra columns at CSV line {$lineNumber}");
                }
            }

            return array_slice($row, 0, $headerCount);
        }

        return $row;
    }

    private function transformTagsForExport(mixed $tags, string $locale): ?string
    {
        $tagText = $this->stringifyTags($tags);

        if ($tagText === null) {
            return null;
        }

        $tagParts = $this->parseTags($tagText);

        return implode('|', array_map(fn ($tag) => $this->translateForExport($tag, $locale), $tagParts));
    }

    private function transformProductForWooCommerceExport(Product $product, string $locale, array $options = []): array
    {
        $normalized = $this->transformProductForExport($product, $locale, $options);
        $description = $this->buildWooCommerceDescription($product, $locale, $normalized);
        $shortDescription = $this->buildWooCommerceShortDescription($product, $locale, $normalized);
        $price = $normalized['price'] !== null ? $this->formatWooCommercePrice($normalized['price'], $options) : '';
        $stock = max(0, (int) ($product->stock ?? 0));
        $isPublished = ($product->status ?? 'active') === 'active' ? '1' : '0';
        $inStock = $stock > 0 || ($product->status ?? 'active') === 'active' ? '1' : '0';
        $lastChange = optional($product->updated_at)->timestamp ?? now()->timestamp;

        return [
            $product->id,
            $normalized['product_form'] ?: 'simple',
            $normalized['sku'] ?? '',
            '',
            $normalized['name'] ?? '',
            $isPublished,
            $product->featured ? '1' : '0',
            'visible',
            $shortDescription,
            $description,
            '',
            '',
            'taxable',
            '',
            $inStock,
            $stock > 0 ? $stock : '',
            '',
            '0',
            '0',
            '',
            '',
            '',
            '',
            '1',
            '',
            '',
            $price,
            $normalized['category'] ?? '',
            $this->formatTagsForWooCommerceExport($normalized['tags'] ?? null),
            '',
            $this->transformImagesForWooCommerceExport($product->image),
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            (string) $product->id,
            $normalized['brand'] ?? '',
            $normalized['product_form'] ?? '',
            '',
            '',
            $product->synced_to_meta ? (string) $lastChange : '',
            $product->synced_to_meta ? 'synced' : '',
            $product->synced_to_meta ? 'sync-and-show' : '',
            $product->synced_to_meta ? 'updated' : '',
            $product->synced_to_meta ? 'approved' : '',
            '',
            '0',
            '',
            '',
            '',
            '',
            '',
            (string) $lastChange,
            $product->synced_to_meta ? 'yes' : 'no',
            'visible',
            $description,
            $description,
            'product',
            $normalized['brand'] ?? '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            $normalized['price_currency'] ?? ($locale === 'en' ? 'USD' : 'VND'),
            $normalized['price_vnd'] ?? '',
        ];
    }

    private function buildWooCommerceDescription(Product $product, string $locale, array $normalized = []): string
    {
        $parts = array_filter([
            $normalized['seo_description'] ?? $this->translateForExport($product->seo_description, $locale),
            $normalized['name'] ?? $this->translateForExport($product->name, $locale),
        ]);

        return implode("\n\n", array_unique($parts));
    }

    private function buildWooCommerceShortDescription(Product $product, string $locale, array $normalized = []): string
    {
        return $normalized['seo_title']
            ?? $this->translateForExport($product->seo_title ?: $product->name, $locale)
            ?? '';
    }

    private function formatTagsForWooCommerceExport(?string $tags): string
    {
        $tagParts = $this->parseTags((string) $tags);

        if ($tagParts === []) {
            return '';
        }

        return implode(', ', $tagParts);
    }

    private function transformImagesForWooCommerceExport(?string $image): string
    {
        $image = trim((string) $image);
        if ($image === '') {
            return '';
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        return asset('storage/' . ltrim($image, '/'));
    }

    private function formatWooCommercePrice(mixed $value, array $options = []): string
    {
        if ($options['show_currency_symbol'] ?? false) {
            $text = trim((string) $value);

            if ($text !== '') {
                return $text;
            }
        }

        $number = $this->normalizeDecimal($value);

        if ($number === null) {
            return '';
        }

        return rtrim(rtrim($number, '0'), '.');
    }

    private function convertPriceForExport(mixed $value, string $locale, array $options = []): ?string
    {
        $normalized = $this->normalizeDecimal($value);

        if ($normalized === null) {
            return null;
        }

        if ($locale !== 'en') {
            return $this->formatPriceWithOptionalSymbol($normalized, $locale, $options);
        }

        $usdRate = (float) config('services.product_export.usd_rate', 25000);
        if ($usdRate <= 0) {
            $usdRate = 25000;
        }

        $usdPrice = number_format(((float) $normalized) / $usdRate, 2, '.', '');

        return $this->formatPriceWithOptionalSymbol($usdPrice, $locale, $options);
    }

    private function formatPriceWithOptionalSymbol(string $value, string $locale, array $options = []): string
    {
        if (!($options['show_currency_symbol'] ?? false)) {
            return $value;
        }

        return $locale === 'en' ? '$' . $value : $value . ' VND';
    }

    private function resolveTranslatedExportFields(Product $product, string $locale): array
    {
        if (!$this->shouldUseAiExportTranslation($locale)) {
            return [];
        }

        $sourceFields = array_filter([
            'name' => $product->name,
            'category' => $product->category,
            'brand' => $product->brand,
            'tags' => $this->stringifyTags($product->tags),
            'status' => $product->status,
            'product_form' => $product->product_form,
            'seo_title' => $product->seo_title,
            'seo_description' => $product->seo_description,
        ], fn ($value) => trim((string) $value) !== '');

        $aiFields = $this->productExportTranslationService->translateFields($sourceFields, $locale);
        $translated = [];

        foreach ($sourceFields as $key => $value) {
            $candidate = trim((string) ($aiFields[$key] ?? $value));
            $fallback = $this->translateForExport($value, $locale);
            $translated[$key] = $candidate !== '' && $candidate !== trim((string) $value)
                ? $candidate
                : $fallback;
        }

        return $translated;
    }

    private function shouldUseAiExportTranslation(string $locale): bool
    {
        if ($locale !== 'en') {
            return false;
        }

        return trim((string) config('services.gemini.api_key', '')) !== '';
    }

    private function translateStatusForExport(?string $status, string $locale): ?string
    {
        if ($status === null) {
            return null;
        }

        return match ($locale) {
            'en' => match (Str::lower(trim($status))) {
                'dang hoat dong', 'active' => 'active',
                'ngung hoat dong', 'inactive' => 'inactive',
                default => $this->translateForExport($status, $locale),
            },
            'vi' => match (Str::lower(trim($status))) {
                'active', 'dang hoat dong' => 'Đang hoạt động',
                'inactive', 'ngung hoat dong' => 'Ngừng hoạt động',
                default => $this->translateForExport($status, $locale),
            },
            default => $status,
        };
    }

    private function translateForExport(?string $value, string $locale): ?string
    {
        $text = trim((string) $value);

        if ($text === '') {
            return null;
        }

        if (!in_array($locale, ['vi', 'en'], true)) {
            return $text;
        }

        $viToEnExact = [
            'Đang hoạt động' => 'Active',
            'Ngừng hoạt động' => 'Inactive',
            'Mùa Đông - Giáng Sinh' => 'Winter - Christmas',
            'Tranh treo tường' => 'Wall art',
            'Áp phích' => 'Poster',
            'Khung tranh' => 'Frame',
            'Vải canvas' => 'Canvas',
        ];
        $enToViExact = array_flip($viToEnExact);

        if ($locale === 'en' && isset($viToEnExact[$text])) {
            return $viToEnExact[$text];
        }

        if ($locale === 'vi' && isset($enToViExact[$text])) {
            return $enToViExact[$text];
        }

        $phraseMap = $locale === 'en'
            ? [
                'tranh treo tường' => 'wall art',
                'tranh canvas' => 'canvas wall art',
                'tranh decor' => 'decor wall art',
                'tranh in' => 'printed art',
                'bộ tranh' => 'art set',
                'tranh bộ' => 'art set',
                'tranh tối giản' => 'minimalist wall art',
                'tranh hiện đại' => 'modern wall art',
                'tranh cổ điển' => 'classic wall art',
                'tranh phong cảnh' => 'landscape wall art',
                'tranh trừu tượng' => 'abstract wall art',
                'tranh hoa lá' => 'botanical wall art',
                'tranh động vật' => 'animal wall art',
                'tranh treo phòng khách' => 'living room wall art',
                'tranh treo phòng ngủ' => 'bedroom wall art',
                'tranh' => 'art',
                'phòng khách' => 'living room',
                'phòng ngủ' => 'bedroom',
                'phòng em bé' => 'nursery',
                'trẻ em' => 'kids',
                'giáng sinh' => 'christmas',
                'noel' => 'christmas',
                'mùa đông' => 'winter',
                'mùa thu' => 'autumn',
                'mùa hè' => 'summer',
                'mùa xuân' => 'spring',
                'lễ hội' => 'holiday',
                'phong cách' => 'style',
                'trang trí' => 'decor',
                'treo tường' => 'wall decor',
                'cây thông' => 'pine tree',
                'lá thông' => 'pine branch',
                'vòng hoa' => 'wreath',
                'người tuyết' => 'snowman',
                'tuần lộc' => 'reindeer',
                'xe trượt tuyết' => 'sleigh',
                'ông già noel' => 'santa claus',
                'hoa tuyết' => 'snowflake',
                'rừng thông' => 'pine forest',
                'cảnh tuyết' => 'snowy landscape',
                'chủ đề' => 'theme',
                'tổng hợp' => 'general',
                'cây cối' => 'plants',
                'tông màu' => 'color palette',
                'dành cho' => 'for',
                'người yêu cây' => 'plant lovers',
                'người yêu mèo' => 'cat lovers',
                'mèo' => 'cat',
                'quà cho' => 'gift for',
                'quà' => 'gift',
                'chân dung gia đình' => 'family portrait',
                'biển hiệu tên' => 'name sign',
                'gia đình' => 'family',
                'tùy chỉnh' => 'custom',
                'đồng quê (farmhouse)' => 'farmhouse',
                'đồng quê' => 'farmhouse',
                'mộc mạc' => 'rustic',
                'khung gỗ' => 'wooden frame',
                'trung tính' => 'neutral',
                'in được' => 'printable',
                'ảnh' => 'image',
                'khung' => 'frame',
                'viền' => 'border',
                'vải canvas' => 'canvas',
                'gỗ' => 'wood',
                'be' => 'beige',
                'nâu' => 'brown',
                'trắng' => 'white',
                'đen' => 'black',
                'xanh lá' => 'green',
                'xanh dương' => 'blue',
                'đỏ' => 'red',
                'vàng' => 'yellow',
                'nghệ thuật' => 'art',
                'danh mục' => 'category',
                'thương hiệu' => 'brand',
                'mô tả' => 'description',
                'tiêu đề' => 'title',
                'nổi bật' => 'featured',
                'đã đồng bộ' => 'synced',
                'đang hoạt động' => 'active',
                'ngừng hoạt động' => 'inactive',
            ]
            : [
                'wall art' => 'tranh treo tường',
                'canvas wall art' => 'tranh canvas',
                'decor wall art' => 'tranh decor',
                'printed art' => 'tranh in',
                'art set' => 'bộ tranh',
                'minimalist wall art' => 'tranh tối giản',
                'modern wall art' => 'tranh hiện đại',
                'classic wall art' => 'tranh cổ điển',
                'landscape wall art' => 'tranh phong cảnh',
                'abstract wall art' => 'tranh trừu tượng',
                'botanical wall art' => 'tranh hoa lá',
                'animal wall art' => 'tranh động vật',
                'living room wall art' => 'tranh treo phòng khách',
                'bedroom wall art' => 'tranh treo phòng ngủ',
                'living room' => 'phòng khách',
                'bedroom' => 'phòng ngủ',
                'nursery' => 'phòng em bé',
                'kids' => 'trẻ em',
                'christmas' => 'giáng sinh',
                'winter' => 'mùa đông',
                'autumn' => 'mùa thu',
                'summer' => 'mùa hè',
                'spring' => 'mùa xuân',
                'holiday' => 'lễ hội',
                'style' => 'phong cách',
                'decor' => 'trang trí',
                'wall decor' => 'treo tường',
                'pine tree' => 'cây thông',
                'pine branch' => 'lá thông',
                'wreath' => 'vòng hoa',
                'snowman' => 'người tuyết',
                'reindeer' => 'tuần lộc',
                'sleigh' => 'xe trượt tuyết',
                'santa claus' => 'ông già noel',
                'snowflake' => 'hoa tuyết',
                'pine forest' => 'rừng thông',
                'snowy landscape' => 'cảnh tuyết',
                'theme' => 'chủ đề',
                'general' => 'tổng hợp',
                'plants' => 'cây cối',
                'color palette' => 'tông màu',
                'plant lovers' => 'người yêu cây',
                'cat lovers' => 'người yêu mèo',
                'cat' => 'mèo',
                'gift for' => 'quà cho',
                'gift' => 'quà',
                'family portrait' => 'chân dung gia đình',
                'name sign' => 'biển hiệu tên',
                'family' => 'gia đình',
                'custom' => 'tùy chỉnh',
                'farmhouse' => 'đồng quê',
                'rustic' => 'mộc mạc',
                'wooden frame' => 'khung gỗ',
                'neutral' => 'trung tính',
                'printable' => 'in được',
                'image' => 'ảnh',
                'frame' => 'khung',
                'border' => 'viền',
                'canvas' => 'vải canvas',
                'wood' => 'gỗ',
                'beige' => 'be',
                'brown' => 'nâu',
                'white' => 'trắng',
                'black' => 'đen',
                'green' => 'xanh lá',
                'blue' => 'xanh dương',
                'red' => 'đỏ',
                'yellow' => 'vàng',
                'art' => 'nghệ thuật',
                'category' => 'danh mục',
                'brand' => 'thương hiệu',
                'description' => 'mô tả',
                'title' => 'tiêu đề',
                'featured' => 'nổi bật',
                'synced' => 'đã đồng bộ',
                'active' => 'đang hoạt động',
                'inactive' => 'ngừng hoạt động',
            ];

        $translated = $text;
        foreach ($phraseMap as $source => $target) {
            $translated = preg_replace('/' . preg_quote($source, '/') . '/iu', $target, $translated) ?? $translated;
        }

        $translated = Str::of($translated)
            ->replace(['  ', ' ,', ' .'], [' ', ',', '.'])
            ->trim()
            ->toString();

        return $translated === '' ? $text : $translated;
    }

    private function detectDelimiter(string $path): string
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return ',';
        }

        $line = (string) fgets($handle);
        fclose($handle);

        $commaCount = substr_count($line, ',');
        $semicolonCount = substr_count($line, ';');

        return $semicolonCount > $commaCount ? ';' : ',';
    }

    private function isLocalImagePath(string $value): bool
    {
        return !str_starts_with($value, 'http://') && !str_starts_with($value, 'https://');
    }

    private function applySearch(Builder $builder, string $search): void
    {
        if ($search === '') {
            return;
        }

        $terms = collect([
            $search,
            Str::ascii($search),
            $this->translateForExport($search, 'en'),
            $this->translateForExport($search, 'vi'),
        ])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        $normalizedSearch = Str::lower(Str::ascii($search));
        $statusFilter = match (true) {
            str_contains($normalizedSearch, 'inactive') || str_contains($normalizedSearch, 'ngung hoat dong') => 'inactive',
            str_contains($normalizedSearch, 'active') || str_contains($normalizedSearch, 'dang hoat dong') => 'active',
            default => null,
        };

        $builder->where(function ($subQuery) use ($search, $terms, $statusFilter) {
            if (ctype_digit($search)) {
                $subQuery->orWhere('id', (int) $search);
            }

            foreach ($terms as $term) {
                $like = '%' . $term . '%';

                $subQuery->orWhere('name', 'like', $like)
                    ->orWhere('sku', 'like', $like)
                    ->orWhere('category', 'like', $like)
                    ->orWhere('brand', 'like', $like)
                    ->orWhere('product_form', 'like', $like)
                    ->orWhere('status', 'like', $like)
                    ->orWhere('tags', 'like', $like)
                    ->orWhere('seo_title', 'like', $like)
                    ->orWhere('seo_description', 'like', $like);
            }

            if ($statusFilter !== null) {
                $subQuery->orWhere('status', $statusFilter);
            }
        });
    }

    private function applyFilters(Builder $builder, array $filters): void
    {
        if (!empty($filters['status']) && in_array($filters['status'], ['active', 'inactive'], true)) {
            $builder->where('status', $filters['status']);
        }

        if (!empty($filters['category'])) {
            $builder->where('category', $filters['category']);
        }

        if (!empty($filters['brand'])) {
            $builder->where('brand', $filters['brand']);
        }
    }

    private function applySorting(Builder $builder, array $filters): void
    {
        $sort = (string) ($filters['sort'] ?? 'id');
        $direction = strtolower((string) ($filters['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';

        $allowed = ['id', 'name', 'sku', 'price', 'stock', 'category', 'brand', 'published_at', 'status'];

        if (!in_array($sort, $allowed, true)) {
            $sort = 'id';
        }

        $builder->orderBy($sort, $direction)->orderBy('id', 'asc');
    }
}



