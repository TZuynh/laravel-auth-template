<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\ImportProductsRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Jobs\GenerateProductExportJob;
use App\Models\Product;
use App\Models\ProductExport;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\ActivityNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ProductController extends Controller
{
    public function index(Request $request, ProductRepositoryInterface $productRepository)
    {
        $q = trim((string) $request->query('q', ''));
        $displayLocale = in_array(app()->getLocale(), ['vi', 'en'], true) ? app()->getLocale() : 'vi';
        $filters = [
            'status' => trim((string) $request->query('status', '')),
            'category' => trim((string) $request->query('category', '')),
            'brand' => trim((string) $request->query('brand', '')),
            'sort' => trim((string) $request->query('sort', 'id')),
            'dir' => trim((string) $request->query('dir', 'asc')),
        ];

        $products = $productRepository->paginateBySearch($q, $filters, 20);
        $products->getCollection()->transform(function (Product $product) use ($productRepository, $displayLocale) {
            $display = $productRepository->transformProductForDisplay($product, $displayLocale, [
                'show_currency_symbol' => true,
            ]);

            $product->setAttribute('display_name', $display['name']);
            $product->setAttribute('display_price', $display['price']);
            $product->setAttribute('display_category', $display['category']);
            $product->setAttribute('display_brand', $display['brand']);
            $product->setAttribute('display_status', $display['status']);

            return $product;
        });
        $categories = Product::query()->whereNotNull('category')->where('category', '!=', '')->distinct()->orderBy('category')->pluck('category');
        $brands = Product::query()->whereNotNull('brand')->where('brand', '!=', '')->distinct()->orderBy('brand')->pluck('brand');

        return view('products.index', compact('products', 'q', 'filters', 'categories', 'brands'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(StoreProductRequest $request, ProductRepositoryInterface $productRepository, ActivityNotificationService $activityNotificationService)
    {
        $product = $productRepository->create($request->validated());
        $activityNotificationService->log($request->user(), 'created', 'product', $product->id, $product->name);

        return redirect()->route('products.index')->with('success', __('messages.product_created'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(UpdateProductRequest $request, Product $product, ProductRepositoryInterface $productRepository, ActivityNotificationService $activityNotificationService)
    {
        $updatedProduct = $productRepository->update($product, $request->validated());
        $activityNotificationService->log($request->user(), 'updated', 'product', $updatedProduct->id, $updatedProduct->name);

        return redirect()->route('products.index')->with('success', __('messages.product_updated'));
    }

    public function destroy(Product $product, ProductRepositoryInterface $productRepository, ActivityNotificationService $activityNotificationService)
    {
        $deletedProductName = $product->name;
        $deletedProductId = $product->id;
        $productRepository->delete($product);
        $activityNotificationService->log(request()->user(), 'deleted', 'product', $deletedProductId, $deletedProductName);

        return back()->with('success', __('messages.product_deleted'));
    }

    public function import(ImportProductsRequest $request, ProductRepositoryInterface $productRepository, ActivityNotificationService $activityNotificationService)
    {
        try {
            $result = $productRepository->importCsv($request->file('csv_file'));
        } catch (RuntimeException $e) {
            return back()->withErrors(['csv_file' => __('messages.validation_failed') . ': ' . $e->getMessage()]);
        }

        $activityNotificationService->log(
            $request->user(),
            'imported',
            'product',
            null,
            __('messages.notifications.import_subject', ['rows' => $result['rows']])
        );

        return back()->with('success', __('messages.product_imported', [
            'rows' => $result['rows'],
            'batches' => $result['batches'],
        ]));
    }

    public function export(Request $request): JsonResponse|RedirectResponse
    {
        $payload = $this->buildExportPayload($request);
        $fileName = ($payload['export_format'] === 'woocommerce' ? 'woocommerce-products-' : 'products-') . now()->format('Y-m-d-His') . '.csv';

        $this->cancelStaleActiveExports($request->user()->id);

        $productExport = ProductExport::query()->create([
            'user_id' => $request->user()->id,
            'status' => 'pending',
            'search_query' => $payload['query'],
            'filters' => $payload['filters'],
            'export_format' => $payload['export_format'],
            'export_locale' => $payload['export_locale'],
            'options' => $payload['options'],
            'disk' => 'local',
            'download_name' => $fileName,
        ]);

        try {
            if ($this->shouldRunExportInline($payload)) {
                GenerateProductExportJob::dispatchSync($productExport->id);
                $productExport->refresh();
            } else {
                GenerateProductExportJob::dispatch($productExport->id);
            }
        } catch (Throwable $exception) {
            $productExport->refresh();

            if ($request->expectsJson()) {
                return response()->json(array_merge(
                    $this->serializeExport($productExport),
                    ['message' => $productExport->error_message ?: __('messages.products.export_failed')]
                ), 500);
            }

            return back()->withErrors(['export' => $productExport->error_message ?: $exception->getMessage()]);
        }

        if ($request->expectsJson()) {
            return response()->json(
                $this->serializeExport($productExport),
                $productExport->status === 'completed' ? 200 : 202
            );
        }

        return back()->with('success', __('messages.products.export_queued'));
    }

    public function exportPreview(Request $request, ProductRepositoryInterface $productRepository): JsonResponse
    {
        $payload = $this->buildExportPayload($request);

        $rows = $productRepository->exportQuery($payload['query'], $payload['filters'])
            ->limit(5)
            ->get()
            ->map(fn (Product $product) => $productRepository->exportRow($product, $payload['export_format'], $payload['export_locale'], $payload['options']))
            ->values();

        return response()->json([
            'headers' => $productRepository->exportHeaders($payload['export_format'], $payload['export_locale'], $payload['options']),
            'rows' => $rows,
        ]);
    }

    public function exportStatus(ProductExport $productExport, ProductRepositoryInterface $productRepository): JsonResponse
    {
        $this->authorizeExport($productExport);
        $this->processExportFallbackIfStale($productExport->fresh(), $productRepository);

        return response()->json($this->serializeExport($productExport->fresh()));
    }

    public function cancelExport(ProductExport $productExport): JsonResponse|RedirectResponse
    {
        $this->authorizeExport($productExport);

        if ($productExport->status === 'pending') {
            $productExport->fillExisting([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ])->save();
        } elseif ($productExport->status === 'processing') {
            $productExport->fillExisting([
                'status' => 'cancelling',
            ])->save();
        }

        if (request()->expectsJson()) {
            return response()->json($this->serializeExport($productExport->fresh()));
        }

        return back()->with('success', __('messages.products.export_cancelled'));
    }

    public function downloadExport(ProductExport $productExport): BinaryFileResponse
    {
        $this->authorizeExport($productExport);

        abort_unless($productExport->status === 'completed' && $productExport->path, 404);

        return response()->download(
            Storage::disk($productExport->disk ?: 'local')->path($productExport->path),
            $productExport->download_name,
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    public function destroyAll(ProductRepositoryInterface $productRepository, ActivityNotificationService $activityNotificationService)
    {
        $deleted = $productRepository->deleteAll();
        $activityNotificationService->log(
            request()->user(),
            'deleted_all',
            'product',
            null,
            __('messages.notifications.delete_all_subject', ['count' => $deleted])
        );

        return redirect()->route('products.index')->with('success', __('messages.product_deleted_all', ['count' => $deleted]));
    }

    private function buildExportPayload(Request $request): array
    {
        $exportLocale = trim((string) $request->input('export_locale', app()->getLocale()));
        $exportLocale = in_array($exportLocale, ['vi', 'en'], true) ? $exportLocale : app()->getLocale();

        return [
            'query' => trim((string) $request->input('q', '')),
            'filters' => [
                'status' => trim((string) $request->input('status', '')),
                'category' => trim((string) $request->input('category', '')),
                'brand' => trim((string) $request->input('brand', '')),
                'sort' => trim((string) $request->input('sort', 'id')),
                'dir' => trim((string) $request->input('dir', 'asc')),
            ],
            'export_format' => trim((string) $request->input('export_format', 'default')),
            'export_locale' => $exportLocale,
            'options' => [
                'show_currency_symbol' => $request->boolean('show_currency_symbol'),
            ],
        ];
    }

    private function shouldRunExportInline(array $payload): bool
    {
        $runInline = filter_var(config('services.product_export.run_inline', false), FILTER_VALIDATE_BOOLEAN);

        if (!$runInline) {
            return false;
        }

        $usesGeminiTranslation = ($payload['export_locale'] ?? null) === 'en'
            && trim((string) config('services.gemini.api_key', '')) !== '';

        return !$usesGeminiTranslation;
    }

    private function processExportFallbackIfStale(ProductExport $productExport, ProductRepositoryInterface $productRepository): void
    {
        if (!in_array($productExport->status, ['pending', 'processing'], true)) {
            return;
        }

        $staleAfterSeconds = 2;
        $lastTouchedAt = $productExport->status === 'pending'
            ? $productExport->created_at
            : $productExport->updated_at;

        if ($lastTouchedAt && $lastTouchedAt->gt(now()->subSeconds($staleAfterSeconds))) {
            return;
        }

        $lock = Cache::lock('product-export-fallback:' . $productExport->id, 15);

        if (!$lock->get()) {
            return;
        }

        try {
            (new GenerateProductExportJob($productExport->id))->processChunk(
                $productRepository,
                (int) config('services.product_export.fallback_chunk_rows', 3),
                (int) config('services.product_export.fallback_chunk_seconds', 8),
                false,
            );
        } catch (Throwable) {
            // The job marks the export as failed; status polling should still return JSON.
        } finally {
            $lock->release();
        }
    }

    private function cancelStaleActiveExports(int $userId): void
    {
        ProductExport::query()
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'processing', 'cancelling'])
            ->where('updated_at', '<', now()->subMinutes(5))
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private function authorizeExport(ProductExport $productExport): void
    {
        abort_unless((int) $productExport->user_id === (int) request()->user()->id, 403);
    }

    private function serializeExport(ProductExport $productExport): array
    {
        $totalRows = max(0, (int) ($productExport->total_rows ?? 0));
        $processedRows = max(0, (int) ($productExport->processed_rows ?? 0));
        $progress = $productExport->status === 'completed'
            ? 100
            : ($totalRows > 0 ? min(99, (int) floor(($processedRows / $totalRows) * 100)) : 0);

        if (in_array($productExport->status, ['cancelled', 'cancelling'], true)) {
            $progress = min($progress, 99);
        }

        return [
            'id' => $productExport->id,
            'status' => $productExport->status,
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'progress_percentage' => $progress,
            'error_message' => $productExport->error_message,
            'status_url' => route('products.export-status', $productExport),
            'cancel_url' => route('products.export-cancel', $productExport),
            'download_url' => $productExport->status === 'completed'
                ? route('products.export-download', $productExport)
                : null,
        ];
    }
}
