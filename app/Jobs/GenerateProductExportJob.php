<?php

namespace App\Jobs;

use App\Models\ProductExport;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
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
        $productExport = ProductExport::query()->find($this->productExportId);

        if (!$productExport || in_array($productExport->status, ['cancelled', 'cancelling'], true)) {
            return;
        }

        $productExport->fillExisting([
            'status' => 'processing',
            'started_at' => now(),
            'error_message' => null,
            'processed_rows' => 0,
        ])->save();

        $disk = $productExport->disk ?: 'local';
        $directory = 'exports';
        $path = $directory . '/' . $productExport->download_name;
        $temporaryPath = Storage::disk($disk)->path($directory . '/tmp-' . $productExport->id . '-' . $productExport->download_name);

        try {
            Storage::disk($disk)->makeDirectory($directory);

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

            $query = $productRepository->exportQuery(
                $productExport->search_query,
                $productExport->filters ?? [],
            );
            $totalRows = (clone $query)->count();

            $productExport->fillExisting([
                'total_rows' => $totalRows,
            ])->save();

            $processedRows = 0;

            foreach ($query->cursor() as $product) {
                if ($processedRows % 25 === 0) {
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
                }

                fputcsv($handle, $productRepository->exportRow(
                    $product,
                    $productExport->export_format,
                    $productExport->export_locale,
                    $productExport->options ?? [],
                ));

                $processedRows++;

                if ($processedRows === 1 || $processedRows % 25 === 0 || $processedRows === $totalRows) {
                    $productExport->fillExisting([
                        'processed_rows' => $processedRows,
                    ])->save();
                }
            }

            fclose($handle);

            Storage::disk($disk)->put($path, fopen($temporaryPath, 'rb'));
            @unlink($temporaryPath);

            $productExport->fillExisting([
                'status' => 'completed',
                'path' => $path,
                'processed_rows' => $processedRows,
                'completed_at' => now(),
            ])->save();
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
}
