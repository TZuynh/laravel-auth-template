<x-layouts.app :title="__('messages.products.page_title')">
    @php($isAdmin = in_array(strtolower(trim((string) (auth()->user()->role ?? ''))), ['administrator', 'admin'], true))
    @php($currentSort = $filters['sort'] ?? request('sort', 'id'))
    @php($currentDir = strtolower($filters['dir'] ?? request('dir', 'asc')) === 'desc' ? 'desc' : 'asc')
    @php($toggleDir = fn ($column) => $currentSort === $column && $currentDir === 'asc' ? 'desc' : 'asc')
    @php($brandOptions = collect($brands ?? [])->prepend($filters['brand'] ?? request('brand'))->filter()->unique()->values())
    @php($sortIcon = function ($column) use ($currentSort, $currentDir) {
        if ($currentSort !== $column) {
            return '↕';
        }

        return $currentDir === 'asc' ? '↑' : '↓';
    })

    <div class="space-y-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-4">
                <div>
                    <h2 class="text-3xl font-black tracking-tight text-slate-900 dark:text-slate-100">{{ __('messages.products.title') }}</h2>
                    <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('messages.products.description') }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-[2.5rem] border border-slate-200 bg-white/90 p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900/80">
            <form method="GET" action="{{ route('products.index') }}" class="grid gap-4 xl:grid-cols-[1.4fr_repeat(4,minmax(0,1fr))_auto] xl:items-end" data-auto-search>
                <div class="relative">
                    <label class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">{{ __('messages.products.search') }}</label>
                    <span class="pointer-events-none absolute left-0 top-[31px] flex h-12 items-center pl-3">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" name="q" value="{{ $q ?? request('q') }}" placeholder="{{ __('messages.products.search_placeholder') }}" class="h-12 w-full rounded-2xl border border-slate-200 bg-white pl-10 pr-10 text-sm text-slate-900 shadow-sm outline-none transition-all placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100 dark:placeholder:text-slate-500">
                    @if(!empty($q ?? request('q')))
                        <a href="{{ route('products.index') }}" class="absolute right-0 top-[31px] flex h-12 items-center pr-3 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200" title="{{ __('messages.products.reset') }}">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                    @endif
                </div>

                <div>
                    <label class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">{{ __('messages.products.status') }}</label>
                    <select name="status" class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100">
                        <option value="">{{ __('messages.products.status') }}</option>
                        <option value="active" @selected(($filters['status'] ?? request('status')) === 'active')>{{ __('messages.products.status_active') }}</option>
                        <option value="inactive" @selected(($filters['status'] ?? request('status')) === 'inactive')>{{ __('messages.products.status_inactive') }}</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">{{ __('messages.products.category') }}</label>
                    <select name="category" class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100">
                        <option value="">{{ __('messages.products.category') }}</option>
                        @foreach(($categories ?? []) as $category)
                            <option value="{{ $category }}" @selected(($filters['category'] ?? request('category')) === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">{{ __('messages.products.brand') }}</label>
                    <select name="brand" class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100">
                        <option value="">{{ __('messages.products.brand') }}</option>
                        @foreach($brandOptions as $brand)
                            <option value="{{ $brand }}" @selected(($filters['brand'] ?? request('brand')) === $brand)>{{ $brand }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">{{ __('messages.products.sort') }}</label>
                    <select name="sort" class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100">
                        @php($sortOptions = ['id' => __('messages.products.id'), 'name' => __('messages.products.name'), 'sku' => __('messages.products.sku'), 'price' => __('messages.products.price'), 'stock' => __('messages.products.stock'), 'published_at' => __('messages.products.date')])
                        @foreach($sortOptions as $value => $label)
                            <option value="{{ $value }}" @selected($currentSort === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">{{ __('messages.products.order') }}</label>
                    <select name="dir" class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100">
                        <option value="asc" @selected($currentDir === 'asc')>{{ __('messages.products.order') }} ↑</option>
                        <option value="desc" @selected($currentDir === 'desc')>{{ __('messages.products.order') }} ↓</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 xl:justify-end">
                    <button type="submit" class="h-12 rounded-2xl bg-slate-900 px-5 text-sm font-bold text-white transition-colors hover:bg-indigo-600 dark:bg-indigo-600 dark:hover:bg-indigo-500">
                        {{ __('messages.products.apply') }}
                    </button>
                    <a href="{{ route('products.index') }}" class="inline-flex h-12 items-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-800">
                        {{ __('messages.products.reset') }}
                    </a>
                </div>
            </form>

            <div class="mt-6 flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between">
                @if ($isAdmin)
                    <form method="POST" action="{{ route('products.export') }}" id="products-export-form" class="flex flex-1 flex-wrap items-end gap-3 rounded-[1.75rem] border border-slate-200 bg-slate-50/80 p-3 shadow-sm dark:border-slate-800 dark:bg-slate-950/70">
                        @csrf
                        <input type="hidden" name="q" value="{{ $q ?? request('q') }}">
                        <input type="hidden" name="status" value="{{ $filters['status'] ?? request('status') }}">
                        <input type="hidden" name="category" value="{{ $filters['category'] ?? request('category') }}">
                        <input type="hidden" name="brand" value="{{ $filters['brand'] ?? request('brand') }}">
                        <input type="hidden" name="sort" value="{{ $filters['sort'] ?? request('sort', 'id') }}">
                        <input type="hidden" name="dir" value="{{ $filters['dir'] ?? request('dir', 'asc') }}">
                        <div class="min-w-[220px] flex-1 sm:flex-none sm:w-56">
                            <label class="mb-1 block text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">{{ __('messages.products.export_format') }}</label>
                            <select name="export_format" class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200">
                                <option value="default">App CSV</option>
                                <option value="woocommerce">WooCommerce CSV</option>
                            </select>
                        </div>
                        <div class="min-w-[190px] flex-1 sm:flex-none sm:w-48">
                            <label class="mb-1 block text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">{{ __('messages.products.export_language') }}</label>
                            <select name="export_locale" class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200">
                                <option value="vi" @selected(request('export_locale', app()->getLocale()) === 'vi')>Tiếng Việt</option>
                                <option value="en" @selected(request('export_locale', app()->getLocale()) === 'en')>English</option>
                            </select>
                        </div>
                        <label class="inline-flex h-10 min-w-[230px] items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200">
                            <input type="checkbox" name="show_currency_symbol" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/20">
                            <span>{{ __('messages.products.show_currency_symbol') }}</span>
                        </label>
                        <button type="button" id="open-export-preview" data-preview-url="{{ route('products.export-preview') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-sky-200 bg-sky-50 px-4 text-sm font-bold text-sky-700 transition-colors hover:bg-sky-100 dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-300">
                            {{ __('messages.products.ai_export_preview') }}
                        </button>
                        <button type="submit" id="products-export-submit" class="inline-flex h-10 items-center justify-center rounded-xl bg-slate-900 px-4 text-sm font-bold text-white transition-colors hover:bg-indigo-600 dark:bg-indigo-600 dark:hover:bg-indigo-500">
                            {{ __('messages.products.export_csv') }}
                        </button>
                    </form>
                   
                @endif
            </div>

            @if ($isAdmin)
                <div id="products-export-status" class="mt-4 hidden rounded-[2rem] border border-sky-200 bg-sky-50/80 p-4 text-sm text-sky-700 shadow-sm dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-300">
                    <div class="flex flex-col gap-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div class="space-y-1">
                                <p id="products-export-status-text" class="font-bold">{{ __('messages.products.export_queued') }}</p>
                                <p id="products-export-status-meta" class="text-xs text-current/80"></p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" id="products-export-cancel" class="hidden rounded-2xl border border-rose-200 bg-white px-4 py-2 text-sm font-bold text-rose-600 transition-colors hover:bg-rose-50 dark:border-rose-500/30 dark:bg-slate-950 dark:text-rose-300">
                                    {{ __('messages.products.export_cancel') }}
                                </button>
                                <a id="products-export-download" href="#" class="hidden rounded-2xl border border-emerald-200 bg-white px-4 py-2 text-sm font-bold text-emerald-700 transition-colors hover:bg-emerald-50 dark:border-emerald-500/30 dark:bg-slate-950 dark:text-emerald-300">
                                    {{ __('messages.products.export_download_ready') }}
                                </a>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-3 text-xs font-semibold uppercase tracking-[0.14em] text-current/80">
                                <span>{{ __('messages.products.export_processing') }}</span>
                                <span id="products-export-progress-label">0%</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-white/70 dark:bg-slate-950/70">
                                <div id="products-export-progress-bar" class="h-full w-0 rounded-full bg-current transition-all duration-300"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if ($isAdmin)
            <div class="rounded-[2.5rem] border border-slate-200 bg-white/90 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/80">
                <div class="mb-4">
                    <h3 class="text-xl font-black text-slate-900 dark:text-slate-100">{{ __('messages.products.import_title') }}</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('messages.products.import_description') }}</p>
                </div>
                <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data" class="grid gap-3 xl:grid-cols-[1fr_auto] xl:items-end">
                    @csrf
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('messages.products.csv_file') }}</label>
                        <input type="file" name="csv_file" accept=".csv,text/csv" class="w-full rounded-2xl border border-slate-200 bg-white p-3 text-sm text-slate-700 shadow-sm file:mr-4 file:rounded-xl file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:font-bold file:text-slate-700 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-200 dark:file:bg-slate-800 dark:file:text-slate-200" required>
                    </div>
                        <button class="h-12 rounded-2xl bg-slate-900 px-5 text-sm font-bold text-white transition-colors hover:bg-indigo-600 dark:bg-indigo-600 dark:hover:bg-indigo-500">
                            {{ __('messages.products.import') }}
                        </button>
                </form>
                <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">
                    {{ __('messages.products.required_columns', ['columns' => 'id, name, sku']) }}
                </p>
            </div>
        @endif

        @if ($isAdmin)
            <div class="flex flex-wrap items-center justify-end gap-3">
                <button type="button" id="open-products-clear" class="inline-flex h-10 shrink-0 items-center rounded-xl border border-rose-200 bg-rose-50 px-4 text-sm font-bold text-rose-700 transition-colors hover:bg-rose-100 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300">
                    {{ __('messages.products.delete_all') }}
                </button>
                <a href="{{ route('products.create') }}" class="inline-flex h-11 items-center justify-center rounded-xl bg-indigo-600 px-5 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition-colors hover:bg-indigo-700">
                    {{ __('messages.products.add') }}
                </a>
            </div>
        @endif

        <div class="overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white/90 shadow-sm dark:border-slate-800 dark:bg-slate-900/80">
            <div class="overflow-x-auto">
                <table class="min-w-[1180px] w-full border-collapse text-left">
                    <thead>
                        <tr class="bg-slate-50/70 dark:bg-slate-800/70">
                            <th class="px-6 py-5 text-[11px] font-black uppercase tracking-[0.15em] text-slate-400 dark:text-slate-500">{{ __('messages.users.profile_id') }}</th>
                            <th class="px-6 py-5 text-[11px] font-black uppercase tracking-[0.15em] text-slate-400 dark:text-slate-500">{{ __('messages.products.image') }}</th>
                            @foreach ([
                                'name' => __('messages.products.name'),
                                'sku' => __('messages.products.sku'),
                                'price' => __('messages.products.price'),
                                'stock' => __('messages.products.stock'),
                                'category' => __('messages.products.category'),
                                'brand' => __('messages.products.brand'),
                                'published_at' => __('messages.products.date'),
                                'status' => __('messages.products.status'),
                            ] as $column => $label)
                                <th class="px-6 py-5 text-[11px] font-black uppercase tracking-[0.15em] text-slate-400 dark:text-slate-500">
                                    <a href="{{ route('products.index', array_merge(request()->except('page'), ['sort' => $column, 'dir' => $toggleDir($column)])) }}" class="inline-flex items-center gap-1 transition-colors hover:text-slate-700 dark:hover:text-slate-200">
                                        <span>{{ $label }}</span>
                                        <span class="text-[10px]">{{ $sortIcon($column) }}</span>
                                    </a>
                                </th>
                            @endforeach
                            <th class="px-6 py-5 text-right text-[11px] font-black uppercase tracking-[0.15em] text-slate-400 dark:text-slate-500">{{ __('messages.products.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($products as $product)
                            <tr class="transition-colors hover:bg-indigo-50/30 dark:hover:bg-slate-800/60">
                                <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-slate-100">{{ $product->id }}</td>
                                <td class="px-6 py-4">
                                    @php($productImage = $product->image ? (str_starts_with($product->image, 'http') ? $product->image : asset('storage/' . $product->image)) : null)
                                    @if($productImage)
                                        <img src="{{ $productImage }}" alt="{{ $product->display_name ?? $product->name }}" class="h-12 w-12 rounded-xl border border-slate-200 object-cover dark:border-slate-800">
                                    @else
                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50 text-[10px] text-slate-400 dark:border-slate-700 dark:bg-slate-900">{{ __('messages.products.no_image') }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-slate-800 dark:text-slate-100">
                                    <div class="max-w-[280px] truncate" title="{{ $product->display_name ?? $product->name }}">{{ $product->display_name ?? $product->name }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $product->sku }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                    {{ $product->display_price ?? ($product->price !== null ? number_format((float) $product->price, 2) : '-') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $product->stock }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                                    <div class="max-w-[180px] truncate" title="{{ $product->display_category ?? $product->category ?? '-' }}">{{ $product->display_category ?? $product->category ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                                    <div class="max-w-[180px] truncate" title="{{ $product->display_brand ?? $product->brand ?? '-' }}">{{ $product->display_brand ?? $product->brand ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ optional($product->published_at)->format('Y-m-d') ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-[10px] font-black uppercase {{ $product->status === 'active' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300' }}">
                                        {{ $product->display_status ?? ($product->status === 'active' ? __('messages.products.status_active') : __('messages.products.status_inactive')) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    @if ($isAdmin)
                                        <a href="{{ route('products.edit', $product) }}" class="mr-4 text-sm font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">{{ __('messages.products.edit') }}</a>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('messages.products.delete_single') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-sm font-bold text-rose-600 hover:text-rose-700 dark:text-rose-400">{{ __('messages.products.delete') }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                                    {{ __('messages.products.no_products') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($products->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/50 px-8 py-6 dark:border-slate-800 dark:bg-slate-900/80">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>

    @if ($isAdmin)
        <div id="export-preview-modal" class="fixed inset-0 z-[125] hidden">
            <div id="export-preview-overlay" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="flex h-[min(78vh,720px)] w-full max-w-6xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                        <div>
                            <p class="text-lg font-black text-slate-900 dark:text-slate-100">{{ __('messages.products.ai_export_preview') }}</p>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('messages.products.ai_export_preview_help') }}</p>
                        </div>
                        <button id="export-preview-close" type="button" class="rounded-2xl p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="min-h-0 flex-1 overflow-auto px-6 py-5">
                        <div id="export-preview-loading" class="hidden rounded-2xl border border-dashed border-slate-200 p-6 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                            {{ __('messages.products.ai_export_preview_loading') }}
                        </div>
                        <div id="export-preview-error" class="hidden rounded-2xl border border-rose-200 bg-rose-50 p-6 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300"></div>
                        <div id="export-preview-table-wrap" class="hidden overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                            <table class="min-w-full border-collapse text-left">
                                <thead id="export-preview-head" class="bg-slate-50 dark:bg-slate-800/80"></thead>
                                <tbody id="export-preview-body" class="divide-y divide-slate-100 dark:divide-slate-800"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="products-clear-modal" class="fixed inset-0 z-[120] hidden">
            <div id="products-clear-overlay" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="w-full max-w-md overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                    <div class="px-6 py-5">
                        <p class="text-sm font-black text-slate-900 dark:text-slate-100">{{ __('messages.products.delete_title') }}</p>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            {{ __('messages.products.delete_desc') }}
                        </p>
                    </div>
                    <div class="flex items-center justify-end gap-3 px-6 pb-6">
                        <button id="products-clear-cancel" type="button" class="rounded-2xl px-4 py-2 text-sm font-bold text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                            {{ __('messages.products.cancel') }}
                        </button>
                        <form action="{{ route('products.clear') }}" method="POST" id="products-clear-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-2xl bg-rose-600 px-4 py-2 text-sm font-black text-white shadow-lg shadow-rose-200">
                                {{ __('messages.products.delete_confirm') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('products-clear-modal');
                const openBtn = document.getElementById('open-products-clear');
                const overlay = document.getElementById('products-clear-overlay');
                const cancelBtn = document.getElementById('products-clear-cancel');
                const exportForm = document.getElementById('products-export-form');
                const previewBtn = document.getElementById('open-export-preview');
                const previewModal = document.getElementById('export-preview-modal');
                const previewOverlay = document.getElementById('export-preview-overlay');
                const previewClose = document.getElementById('export-preview-close');
                const previewLoading = document.getElementById('export-preview-loading');
                const previewError = document.getElementById('export-preview-error');
                const previewTableWrap = document.getElementById('export-preview-table-wrap');
                const previewHead = document.getElementById('export-preview-head');
                const previewBody = document.getElementById('export-preview-body');
                const exportStatus = document.getElementById('products-export-status');
                const exportStatusText = document.getElementById('products-export-status-text');
                const exportStatusMeta = document.getElementById('products-export-status-meta');
                const exportDownload = document.getElementById('products-export-download');
                const exportCancel = document.getElementById('products-export-cancel');
                const exportProgressBar = document.getElementById('products-export-progress-bar');
                const exportProgressLabel = document.getElementById('products-export-progress-label');
                const exportSubmit = document.getElementById('products-export-submit');
                let exportPollTimer = null;
                let currentExport = null;

                if (!modal || !openBtn || !overlay || !cancelBtn) {
                    return;
                }

                const open = () => modal.classList.remove('hidden');
                const close = () => modal.classList.add('hidden');
                const closePreview = () => previewModal?.classList.add('hidden');
                const renderExportStatus = ({ message, meta = '', progress = 0, variant = 'info', downloadUrl = null, canCancel = false }) => {
                    if (!exportStatus || !exportStatusText || !exportStatusMeta || !exportDownload || !exportCancel || !exportProgressBar || !exportProgressLabel) {
                        return;
                    }

                    exportStatus.classList.remove('hidden', 'border-sky-200', 'bg-sky-50/80', 'text-sky-700', 'border-emerald-200', 'bg-emerald-50/80', 'text-emerald-700', 'border-rose-200', 'bg-rose-50/80', 'text-rose-700', 'border-amber-200', 'bg-amber-50/80', 'text-amber-700');
                    exportStatusText.textContent = message;
                    exportStatusMeta.textContent = meta;
                    exportProgressBar.style.width = `${Math.max(0, Math.min(100, progress))}%`;
                    exportProgressLabel.textContent = `${Math.max(0, Math.min(100, progress))}%`;
                    exportDownload.classList.add('hidden');
                    exportDownload.removeAttribute('href');
                    exportCancel.classList.toggle('hidden', !canCancel);

                    if (variant === 'success') {
                        exportStatus.classList.add('border-emerald-200', 'bg-emerald-50/80', 'text-emerald-700');
                    } else if (variant === 'error') {
                        exportStatus.classList.add('border-rose-200', 'bg-rose-50/80', 'text-rose-700');
                    } else if (variant === 'warning') {
                        exportStatus.classList.add('border-amber-200', 'bg-amber-50/80', 'text-amber-700');
                    } else {
                        exportStatus.classList.add('border-sky-200', 'bg-sky-50/80', 'text-sky-700');
                    }

                    if (downloadUrl) {
                        exportDownload.href = downloadUrl;
                        exportDownload.classList.remove('hidden');
                    }
                };
                const stopPolling = () => {
                    if (exportPollTimer) {
                        window.clearTimeout(exportPollTimer);
                        exportPollTimer = null;
                    }
                };
                const formatMeta = (data) => {
                    const processed = Number(data?.processed_rows || 0);
                    const total = Number(data?.total_rows || 0);

                    if (total > 0) {
                        return `${processed.toLocaleString()} / ${total.toLocaleString()} rows`;
                    }

                    return processed > 0 ? `${processed.toLocaleString()} rows` : '';
                };
                const pollExportStatus = async (statusUrl) => {
                    if (!statusUrl) {
                        return;
                    }

                    try {
                        const response = await fetch(statusUrl, {
                            headers: {
                                'Accept': 'application/json',
                            },
                        });
                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data?.message || 'Export status failed.');
                        }

                        currentExport = data;

                        if (data.status === 'completed' && data.download_url) {
                            renderExportStatus({
                                message: '{{ __('messages.products.export_ready') }}',
                                meta: formatMeta(data),
                                progress: 100,
                                variant: 'success',
                                downloadUrl: data.download_url,
                                canCancel: false,
                            });
                            window.location.href = data.download_url;
                            return;
                        }

                        if (data.status === 'failed') {
                            renderExportStatus({
                                message: data.error_message || '{{ __('messages.products.export_failed') }}',
                                meta: formatMeta(data),
                                progress: data.progress_percentage || 0,
                                variant: 'error',
                                canCancel: false,
                            });
                            return;
                        }

                        if (data.status === 'cancelled') {
                            renderExportStatus({
                                message: '{{ __('messages.products.export_cancelled') }}',
                                meta: formatMeta(data),
                                progress: data.progress_percentage || 0,
                                variant: 'warning',
                                canCancel: false,
                            });
                            return;
                        }

                        if (data.status === 'cancelling') {
                            renderExportStatus({
                                message: '{{ __('messages.products.export_cancelling') }}',
                                meta: formatMeta(data),
                                progress: data.progress_percentage || 0,
                                variant: 'warning',
                                canCancel: false,
                            });

                            exportPollTimer = window.setTimeout(() => pollExportStatus(statusUrl), 1500);
                            return;
                        }

                        renderExportStatus({
                            message: data.status === 'processing'
                                ? '{{ __('messages.products.export_processing') }}'
                                : '{{ __('messages.products.export_pending') }}',
                            meta: formatMeta(data),
                            progress: data.progress_percentage || 0,
                            variant: 'info',
                            canCancel: ['pending', 'processing'].includes(data.status),
                        });

                        exportPollTimer = window.setTimeout(() => pollExportStatus(statusUrl), 2000);
                    } catch (error) {
                        renderExportStatus({
                            message: error.message || '{{ __('messages.products.export_failed') }}',
                            variant: 'error',
                            canCancel: false,
                        });
                    }
                };

                openBtn.addEventListener('click', open);
                overlay.addEventListener('click', close);
                cancelBtn.addEventListener('click', close);
                exportForm?.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    stopPolling();

                    const formData = new FormData(exportForm);
                    exportSubmit?.setAttribute('disabled', 'disabled');
                    exportSubmit?.classList.add('opacity-70', 'cursor-not-allowed');
                    renderExportStatus({
                        message: '{{ __('messages.products.export_pending') }}',
                        progress: 0,
                        variant: 'info',
                        canCancel: false,
                    });

                    try {
                        const response = await fetch(exportForm.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });
                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data?.message || '{{ __('messages.products.export_failed') }}');
                        }

                        currentExport = data;

                        if (data.status === 'completed' && data.download_url) {
                            renderExportStatus({
                                message: '{{ __('messages.products.export_ready') }}',
                                meta: formatMeta(data),
                                progress: 100,
                                variant: 'success',
                                downloadUrl: data.download_url,
                                canCancel: false,
                            });
                            window.location.href = data.download_url;
                            return;
                        }

                        renderExportStatus({
                            message: '{{ __('messages.products.export_processing') }}',
                            progress: data.progress_percentage || 0,
                            variant: 'info',
                            canCancel: ['pending', 'processing'].includes(data.status),
                        });
                        pollExportStatus(data.status_url);
                    } catch (error) {
                        renderExportStatus({
                            message: error.message || '{{ __('messages.products.export_failed') }}',
                            variant: 'error',
                            canCancel: false,
                        });
                    } finally {
                        exportSubmit?.removeAttribute('disabled');
                        exportSubmit?.classList.remove('opacity-70', 'cursor-not-allowed');
                    }
                });
                exportCancel?.addEventListener('click', async () => {
                    if (!currentExport?.cancel_url) {
                        return;
                    }

                    stopPolling();
                    renderExportStatus({
                        message: '{{ __('messages.products.export_cancelling') }}',
                        meta: formatMeta(currentExport),
                        progress: currentExport.progress_percentage || 0,
                        variant: 'warning',
                        canCancel: false,
                    });

                    try {
                        const response = await fetch(currentExport.cancel_url, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            },
                        });
                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data?.message || '{{ __('messages.products.export_failed') }}');
                        }

                        currentExport = data;

                        if (data.status === 'cancelled') {
                            renderExportStatus({
                                message: '{{ __('messages.products.export_cancelled') }}',
                                meta: formatMeta(data),
                                progress: data.progress_percentage || 0,
                                variant: 'warning',
                                canCancel: false,
                            });
                            return;
                        }

                        pollExportStatus(data.status_url);
                    } catch (error) {
                        renderExportStatus({
                            message: error.message || '{{ __('messages.products.export_failed') }}',
                            variant: 'error',
                            canCancel: false,
                        });
                    }
                });
                previewClose?.addEventListener('click', closePreview);
                previewOverlay?.addEventListener('click', closePreview);
                previewBtn?.addEventListener('click', async () => {
                    if (!exportForm || !previewModal || !previewLoading || !previewError || !previewTableWrap || !previewHead || !previewBody) {
                        return;
                    }

                    previewModal.classList.remove('hidden');
                    previewLoading.classList.remove('hidden');
                    previewError.classList.add('hidden');
                    previewTableWrap.classList.add('hidden');
                    previewHead.innerHTML = '';
                    previewBody.innerHTML = '';

                    const params = new URLSearchParams(new FormData(exportForm));
                    params.delete('_token');

                    try {
                        const response = await fetch(`${previewBtn.dataset.previewUrl}?${params.toString()}`, {
                            headers: {
                                'Accept': 'application/json',
                            },
                        });
                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data?.message || 'Preview failed');
                        }

                        const headers = Array.isArray(data?.headers) ? data.headers : [];
                        const rows = Array.isArray(data?.rows) ? data.rows : [];

                        previewHead.innerHTML = `<tr>${headers.map((header) => `<th class="px-4 py-3 text-[11px] font-black uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">${header}</th>`).join('')}</tr>`;
                        previewBody.innerHTML = rows.map((row) => `<tr>${row.map((cell) => `<td class="max-w-[260px] whitespace-pre-wrap px-4 py-3 text-sm text-slate-700 dark:text-slate-200">${String(cell ?? '-').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;')}</td>`).join('')}</tr>`).join('');

                        previewLoading.classList.add('hidden');
                        previewTableWrap.classList.remove('hidden');
                    } catch (error) {
                        previewLoading.classList.add('hidden');
                        previewError.textContent = error.message || 'Preview failed.';
                        previewError.classList.remove('hidden');
                    }
                });
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        close();
                        closePreview();
                    }
                });
            });
        </script>
    @endif
</x-layouts.app>
