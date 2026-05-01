<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

interface ProductRepositoryInterface
{
    public function paginateBySearch(?string $query, array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function create(array $data): Product;

    public function update(Product $product, array $data): Product;

    public function delete(Product $product): bool|null;

    public function deleteAll(): int;

    public function exportQuery(?string $query, array $filters = []): Builder;

    public function transformProductForDisplay(Product $product, string $locale, array $options = []): array;

    public function transformProductForExport(Product $product, string $locale, array $options = []): array;

    public function exportHeaders(string $format, string $locale, array $options = []): array;

    public function exportRow(Product $product, string $format, string $locale, array $options = []): array;

    public function importCsv(UploadedFile $file): array;
}
