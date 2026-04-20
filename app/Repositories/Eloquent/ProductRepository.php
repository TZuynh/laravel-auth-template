<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use SplFileObject;

class ProductRepository implements ProductRepositoryInterface
{
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

    public function importCsv(UploadedFile $file): array
    {
        $csv = new SplFileObject($file->getRealPath());
        $csv->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        $delimiter = $this->detectDelimiter($file->getRealPath());
        $csv->setCsvControl($delimiter, '"', '\\');

        $headers = [];
        $buffer = [];
        $rowsProcessed = 0;
        $batchCount = 0;

        foreach ($csv as $index => $row) {
            if (!is_array($row) || $row === [null] || $row === false) {
                continue;
            }

            if ($index === 0) {
                $headers = $this->normalizeHeaders($row);
                $this->assertRequiredHeaders($headers);
                continue;
            }

            $payload = $this->mapCsvRow($headers, $row, $index + 1);
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
            'ten', 'ho_ten' => 'name',
            'ma_sku' => 'sku',
            'image', 'image_url', 'hinh', 'anh' => 'image',
            'ton_kho' => 'stock',
            'gia' => 'price',
            'danh_muc' => 'category',
            'thuong_hieu' => 'brand',
            'the' => 'tags',
            'noi_bat' => 'featured',
            'synced_to_meta_catalog', 'meta_catalog', 'filter_by_synced_to_meta' => 'synced_to_meta',
            'trang_thai' => 'status',
            'ngay', 'date' => 'published_at',
            'chi_tiet_seo', 'seo_chi_tiet' => 'seo_title',
            'seo_mo_ta' => 'seo_description',
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

        $payload = [
            'id' => $this->normalizeId($combined['id'] ?? null, $lineNumber),
            'name' => $this->normalizeRequiredString($combined['name'] ?? null, 'name', $lineNumber),
            'sku' => $this->normalizeRequiredString($combined['sku'] ?? null, 'sku', $lineNumber),
            'image' => $this->normalizeNullableString($combined['image'] ?? null),
            'price' => $this->normalizeDecimal($combined['price'] ?? null),
            'stock' => (int) ($combined['stock'] ?? 0),
            'category' => $this->normalizeNullableString($combined['category'] ?? null),
            'brand' => $this->normalizeNullableString($combined['brand'] ?? null),
            'tags' => $this->normalizeTagsForImport($combined['tags'] ?? null),
            'featured' => $this->normalizeBoolean($combined['featured'] ?? null),
            'synced_to_meta' => $this->normalizeBoolean($combined['synced_to_meta'] ?? null),
            'status' => $this->normalizeStatus($combined['status'] ?? null),
            'product_form' => $this->normalizeNullableString($combined['product_form'] ?? null),
            'published_at' => $this->normalizeNullableDate($combined['published_at'] ?? null),
            'seo_title' => $this->normalizeNullableString($combined['seo_title'] ?? null),
            'seo_description' => $this->normalizeNullableString($combined['seo_description'] ?? null),
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

        $text = str_replace([',', ' '], ['', ''], $text);

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

        return in_array($text, ['active', 'inactive'], true) ? $text : 'active';
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

        $builder->where(function ($subQuery) use ($search) {
            if (ctype_digit($search)) {
                $subQuery->orWhere('id', (int) $search);
            }

            $subQuery->orWhere('name', 'like', '%' . $search . '%')
                ->orWhere('sku', 'like', '%' . $search . '%')
                ->orWhere('category', 'like', '%' . $search . '%')
                ->orWhere('brand', 'like', '%' . $search . '%')
                ->orWhere('product_form', 'like', '%' . $search . '%')
                ->orWhere('status', 'like', '%' . $search . '%');
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



