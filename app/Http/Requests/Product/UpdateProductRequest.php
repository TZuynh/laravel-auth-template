<?php

namespace App\Http\Requests\Product;

use App\Models\Product;
use App\Rules\ProductSkuRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'required',
                'string',
                'max:64',
                new ProductSkuRule(),
                Rule::unique('products', 'sku')->ignore($product instanceof Product ? $product->id : null),
            ],
            'image' => ['nullable', 'image', 'max:2048'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'category' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:1000'],
            'featured' => ['nullable', 'boolean'],
            'synced_to_meta' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'product_form' => ['nullable', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
