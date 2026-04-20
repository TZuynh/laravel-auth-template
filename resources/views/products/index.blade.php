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
                <a href="{{ route('dashboard') }}" class="inline-flex h-12 items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 shadow-sm transition-colors hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    <span aria-hidden="true">←</span>
                    {{ __('messages.products.back') }}
                </a>

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

            <div class="mt-4 flex flex-wrap items-center justify-end gap-2">
                @if ($isAdmin)
                    <a href="{{ route('products.export', request()->only('q', 'status', 'category', 'brand', 'sort', 'dir')) }}" class="inline-flex h-11 items-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-800">
                        {{ __('messages.products.export_csv') }}
                    </a>
                    <button type="button" id="open-products-clear" class="inline-flex h-11 items-center rounded-2xl border border-rose-200 bg-rose-50 px-4 text-sm font-bold text-rose-700 transition-colors hover:bg-rose-100 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300">
                        {{ __('messages.products.delete_all') }}
                    </button>
                    <a href="{{ route('products.create') }}" class="inline-flex h-11 items-center rounded-2xl bg-indigo-600 px-5 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition-colors hover:bg-indigo-700">
                        {{ __('messages.products.add') }}
                    </a>
                @endif
            </div>
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
                                        <img src="{{ $productImage }}" alt="{{ $product->name }}" class="h-12 w-12 rounded-xl border border-slate-200 object-cover dark:border-slate-800">
                                    @else
                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50 text-[10px] text-slate-400 dark:border-slate-700 dark:bg-slate-900">{{ __('messages.products.no_image') }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-slate-800 dark:text-slate-100">
                                    <div class="max-w-[280px] truncate" title="{{ $product->name }}">{{ $product->name }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $product->sku }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ number_format((float) $product->price, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $product->stock }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                                    <div class="max-w-[180px] truncate" title="{{ $product->category ?? '-' }}">{{ $product->category ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                                    <div class="max-w-[180px] truncate" title="{{ $product->brand ?? '-' }}">{{ $product->brand ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ optional($product->published_at)->format('Y-m-d') ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-[10px] font-black uppercase {{ $product->status === 'active' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300' }}">
                                        {{ $product->status === 'active' ? __('messages.products.status_active') : __('messages.products.status_inactive') }}
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

                if (!modal || !openBtn || !overlay || !cancelBtn) {
                    return;
                }

                const open = () => modal.classList.remove('hidden');
                const close = () => modal.classList.add('hidden');

                openBtn.addEventListener('click', open);
                overlay.addEventListener('click', close);
                cancelBtn.addEventListener('click', close);
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        close();
                    }
                });
            });
        </script>
    @endif
</x-layouts.app>
