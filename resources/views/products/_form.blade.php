@php
    $currentProduct = $product ?? null;
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
        <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('messages.products.image_label') }}</label>
        <div class="flex items-start gap-4">
            <div class="shrink-0">
                @php($imageUrl = $currentProduct?->image ? (str_starts_with($currentProduct->image, 'http') ? $currentProduct->image : asset('storage/' . $currentProduct->image)) : null)
                @if($imageUrl)
                    <img src="{{ $imageUrl }}" alt="{{ $currentProduct->name ?? __('messages.products.image') }}" class="h-24 w-24 rounded-2xl border border-slate-200 object-cover shadow-sm dark:border-slate-800">
                @else
                    <div class="flex h-24 w-24 items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 text-xs text-slate-400 dark:border-slate-700 dark:bg-slate-950/60">{{ __('messages.products.no_image') }}</div>
                @endif
            </div>
            <div class="flex-1">
                <input name="image" type="file" accept="image/*" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-700 shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:font-bold file:text-slate-700 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-200 dark:file:bg-slate-800 dark:file:text-slate-200">
                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ __('messages.products.image_note') }}</p>
            </div>
        </div>
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('messages.products.name_label') }}</label>
        <input name="name" value="{{ old('name', $currentProduct->name ?? '') }}" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-900 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100" required>
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('messages.products.sku_label') }}</label>
        <input name="sku" value="{{ old('sku', $currentProduct->sku ?? '') }}" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-900 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100" required>
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('messages.products.price_label') }}</label>
        <input name="price" type="number" step="0.01" min="0" value="{{ old('price', $currentProduct->price ?? '') }}" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-900 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100">
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('messages.products.stock_label') }}</label>
        <input name="stock" type="number" min="0" value="{{ old('stock', $currentProduct->stock ?? 0) }}" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-900 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100">
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('messages.products.category_label') }}</label>
        <input name="category" value="{{ old('category', $currentProduct->category ?? '') }}" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-900 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100">
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('messages.products.brand_label') }}</label>
        <input name="brand" value="{{ old('brand', $currentProduct->brand ?? '') }}" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-900 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100">
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('messages.products.product_form_label') }}</label>
        <input name="product_form" value="{{ old('product_form', $currentProduct->product_form ?? '') }}" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-900 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100">
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('messages.products.date_label') }}</label>
        <input name="published_at" type="date" value="{{ old('published_at', optional($currentProduct->published_at ?? null)->format('Y-m-d')) }}" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-900 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100">
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('messages.products.status_label') }}</label>
        <select name="status" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-900 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100" required>
            <option value="active" @selected(old('status', $currentProduct->status ?? 'active') === 'active')>{{ __('messages.products.status_active') }}</option>
            <option value="inactive" @selected(old('status', $currentProduct->status ?? 'active') === 'inactive')>{{ __('messages.products.status_inactive') }}</option>
        </select>
    </div>

    <div class="md:col-span-2">
        <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('messages.products.tags_label') }}</label>
        <input name="tags" value="{{ old('tags', is_array($currentProduct?->tags ?? null) ? implode(', ', $currentProduct->tags) : ($currentProduct->tags ?? '')) }}" placeholder="tag1, tag2" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-900 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100">
    </div>

    <div class="md:col-span-2 grid grid-cols-1 gap-4 md:grid-cols-2">
        <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-3 dark:border-slate-800">
            <input type="checkbox" name="featured" value="1" @checked(old('featured', $currentProduct->featured ?? false))>
            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('messages.products.featured_label') }}</span>
        </label>
        <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-3 dark:border-slate-800">
            <input type="checkbox" name="synced_to_meta" value="1" @checked(old('synced_to_meta', $currentProduct->synced_to_meta ?? false))>
            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('messages.products.synced_to_meta_label') }}</span>
        </label>
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('messages.products.seo_title_label') }}</label>
        <input name="seo_title" value="{{ old('seo_title', $currentProduct->seo_title ?? '') }}" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-900 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100">
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('messages.products.seo_description_label') }}</label>
        <input name="seo_description" value="{{ old('seo_description', $currentProduct->seo_description ?? '') }}" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-900 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100">
    </div>
</div>
