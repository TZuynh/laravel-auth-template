<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\ImportProductsRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    public function index(Request $request, ProductRepositoryInterface $productRepository)
    {
        $q = trim((string) $request->query('q', ''));
        $filters = [
            'status' => trim((string) $request->query('status', '')),
            'category' => trim((string) $request->query('category', '')),
            'brand' => trim((string) $request->query('brand', '')),
            'sort' => trim((string) $request->query('sort', 'id')),
            'dir' => trim((string) $request->query('dir', 'asc')),
        ];

        $products = $productRepository->paginateBySearch($q, $filters, 20);
        $categories = Product::query()->whereNotNull('category')->where('category', '!=', '')->distinct()->orderBy('category')->pluck('category');
        $brands = Product::query()->whereNotNull('brand')->where('brand', '!=', '')->distinct()->orderBy('brand')->pluck('brand');

        return view('products.index', compact('products', 'q', 'filters', 'categories', 'brands'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(StoreProductRequest $request, ProductRepositoryInterface $productRepository)
    {
        $productRepository->create($request->validated());

        return redirect()->route('products.index')->with('success', __('messages.product_created'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(UpdateProductRequest $request, Product $product, ProductRepositoryInterface $productRepository)
    {
        $productRepository->update($product, $request->validated());

        return redirect()->route('products.index')->with('success', __('messages.product_updated'));
    }

    public function destroy(Product $product, ProductRepositoryInterface $productRepository)
    {
        $productRepository->delete($product);

        return back()->with('success', __('messages.product_deleted'));
    }

    public function import(ImportProductsRequest $request, ProductRepositoryInterface $productRepository)
    {
        try {
            $result = $productRepository->importCsv($request->file('csv_file'));
        } catch (RuntimeException $e) {
            return back()->withErrors(['csv_file' => __('messages.validation_failed') . ': ' . $e->getMessage()]);
        }

        return back()->with('success', __('messages.product_imported', [
            'rows' => $result['rows'],
            'batches' => $result['batches'],
        ]));
    }

    public function export(Request $request, ProductRepositoryInterface $productRepository): StreamedResponse
    {
        $q = trim((string) $request->query('q', ''));
        $filters = [
            'status' => trim((string) $request->query('status', '')),
            'category' => trim((string) $request->query('category', '')),
            'brand' => trim((string) $request->query('brand', '')),
            'sort' => trim((string) $request->query('sort', 'id')),
            'dir' => trim((string) $request->query('dir', 'asc')),
        ];
        $query = $productRepository->exportQuery($q, $filters);
        $fileName = 'products-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
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

            foreach ($query->cursor() as $product) {
                fputcsv($handle, [
                    $product->id,
                    $product->name,
                    $product->sku,
                    $product->image,
                    $product->price,
                    $product->stock,
                    $product->category,
                    $product->brand,
                    is_array($product->tags) ? implode('|', $product->tags) : $product->tags,
                    $product->featured ? 1 : 0,
                    $product->synced_to_meta ? 1 : 0,
                    $product->status,
                    $product->product_form,
                    optional($product->published_at)->format('Y-m-d'),
                    $product->seo_title,
                    $product->seo_description,
                    optional($product->created_at)->toDateTimeString(),
                    optional($product->updated_at)->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function destroyAll(ProductRepositoryInterface $productRepository)
    {
        $deleted = $productRepository->deleteAll();

        return redirect()->route('products.index')->with('success', __('messages.product_deleted_all', ['count' => $deleted]));
    }
}
