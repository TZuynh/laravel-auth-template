<?php

namespace App\Jobs;

use App\Models\ProductExport;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateProductExportJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 3600;

    public int $tries = 1;

    public function __construct(
        private readonly int $productExportId,
    ) {
    }

    public function handle(ProductRepositoryInterface $productRepository): void
    {
        $this->processChunk(
            $productRepository,
            (int) config('services.product_export.chunk_rows', 20),
            (int) config('services.product_export.chunk_seconds', 45),
            true,
        );
    }

    public function processChunk(ProductRepositoryInterface $productRepository, int $maxRows, int $maxSeconds, bool $dispatchNext): void
    {
        $lock = Cache::lock('product-export-process:' . $this->productExportId, max(15, $maxSeconds + 10));

        if (!$lock->get()) {
            return;
        }

        try {
            $this->processChunkLocked($productRepository, $maxRows, $maxSeconds, $dispatchNext);
        } finally {
            $lock->release();
        }
    }

    private function processChunkLocked(ProductRepositoryInterface $productRepository, int $maxRows, int $maxSeconds, bool $dispatchNext): void
    {
        $productExport = ProductExport::query()->find($this->productExportId);

        if (!$productExport || in_array($productExport->status, ['cancelled', 'completed'], true)) {
            return;
        }

        $disk = $productExport->disk ?: 'local';
        $directory = 'exports';
        $path = $directory . '/' . $productExport->download_name;
        $temporaryPath = Storage::disk($disk)->path($directory . '/tmp-' . $productExport->id . '-' . $productExport->download_name);

        if ($productExport->status === 'cancelling') {
            if (is_file($temporaryPath)) {
                @unlink($temporaryPath);
            }

            $productExport->fillExisting([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ])->save();

            return;
        }

        $productExport->fillExisting([
            'status' => 'processing',
            'started_at' => $productExport->started_at ?: now(),
            'error_message' => null,
        ])->save();

        $maxRows = max(1, $maxRows);
        $deadline = microtime(true) + max(1, $maxSeconds);

        try {
            Storage::disk($disk)->makeDirectory($directory);

            $query = $productRepository->exportQuery(
                $productExport->search_query,
                $productExport->filters ?? [],
            );
            $totalRows = (int) ($productExport->total_rows ?: (clone $query)->count());
            $processedRows = max(0, (int) ($productExport->processed_rows ?? 0));
            $shouldStartFile = $processedRows === 0 || !is_file($temporaryPath);

            if ($shouldStartFile) {
                $processedRows = 0;
                $handle = fopen($temporaryPath, 'wb');

                if ($handle === false) {
                    throw new \RuntimeException('Unable to create export file.');
                }

                fwrite($handle, "\xEF\xBB\xBF");
                fputcsv($handle, $productRepository->exportHeaders(
                    $productExport->export_format,
                    $productExport->export_locale,
                    $productExport->options ?? [],
                ));
            } else {
                $handle = fopen($temporaryPath, 'ab');

                if ($handle === false) {
                    throw new \RuntimeException('Unable to continue export file.');
                }
            }

            $productExport->fillExisting([
                'total_rows' => $totalRows,
                'processed_rows' => $processedRows,
            ])->save();

            if ($totalRows === 0 || $processedRows >= $totalRows) {
                fclose($handle);
                $this->completeExport($productExport, $disk, $path, $temporaryPath, $processedRows);

                return;
            }

            $rowsThisRun = 0;
            $remainingQuery = clone $query;

            if ($processedRows > 0) {
                $remainingQuery->skip($processedRows);
            }

            $remainingProducts = $remainingQuery->limit($maxRows)->get();

            foreach ($remainingProducts as $product) {
                $productExport->refresh();

                if (in_array($productExport->status, ['cancelled', 'cancelling'], true)) {
                    fclose($handle);
                    @unlink($temporaryPath);

                    $productExport->fillExisting([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                    ])->save();

                    return;
                }

                fputcsv($handle, $productRepository->exportRow(
                    $product,
                    $productExport->export_format,
                    $productExport->export_locale,
                    $productExport->options ?? [],
                ));

                $processedRows++;
                $rowsThisRun++;

                if ($processedRows === 1 || $processedRows % 5 === 0 || $processedRows === $totalRows) {
                    $productExport->fillExisting([
                        'processed_rows' => $processedRows,
                    ])->save();
                }

                if ($processedRows < $totalRows && ($rowsThisRun >= $maxRows || microtime(true) >= $deadline)) {
                    fclose($handle);

                    $productExport->fillExisting([
                        'processed_rows' => $processedRows,
                    ])->save();

                    if ($dispatchNext) {
                        self::dispatch($productExport->id);
                    }

                    return;
                }
            }

            fclose($handle);
            $this->completeExport($productExport, $disk, $path, $temporaryPath, $processedRows);
        } catch (Throwable $exception) {
            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }

            if (is_file($temporaryPath)) {
                @unlink($temporaryPath);
            }

            $productExport->fillExisting([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ])->save();

            throw $exception;
        }
    }

    private function completeExport(ProductExport $productExport, string $disk, string $path, string $temporaryPath, int $processedRows): void
    {
        $readHandle = fopen($temporaryPath, 'rb');

        if ($readHandle === false) {
            throw new \RuntimeException('Unable to finalize export file.');
        }

        Storage::disk($disk)->put($path, $readHandle);
        fclose($readHandle);
        @unlink($temporaryPath);

        $productExport->fillExisting([
            'status' => 'completed',
            'path' => $path,
            'processed_rows' => $processedRows,
            'completed_at' => now(),
        ])->save();
    }
}
