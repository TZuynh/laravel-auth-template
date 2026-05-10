<x-layouts.app :title="__('messages.erp.sidebar.inventory')">
    @include('erp.partials.styles')

    <div class="space-y-6">
        <section class="erp-card flex flex-col gap-4 p-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-4">
                <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 21V9l9-6 9 6v12"/><path d="M7 21v-8h10v8M9 17h6"/>
                    </svg>
                </span>
                <div>
                    <h2 class="text-3xl font-black text-slate-900">{{ __('messages.erp.sidebar.inventory') }}</h2>
                    <p class="mt-1 text-sm font-semibold text-slate-500">{{ __('messages.erp.ui.inventory_desc') }}</p>
                </div>
            </div>
            <button type="button" class="erp-btn erp-btn-outline" data-print-section="#inventory-print">{{ __('messages.erp.ui.print') }}</button>
        </section>

        <section class="erp-card grid gap-3 p-4 lg:grid-cols-[auto_1fr]">
            <div class="flex rounded-2xl bg-slate-100 p-1">
                <button type="button" class="rounded-xl bg-white px-4 py-3 text-sm font-black text-emerald-600 shadow-sm">{{ __('messages.erp.ui.current_stock') }}</button>
                <button type="button" class="rounded-xl px-4 py-3 text-sm font-black text-slate-500">{{ __('messages.erp.ui.stock_history') }}</button>
            </div>
            <input class="erp-input" data-inventory-search placeholder="{{ __('messages.erp.ui.search') }}">
        </section>

        <section class="erp-card overflow-hidden" id="inventory-print">
            <div class="overflow-x-auto">
                <table class="erp-table min-w-[980px]" id="inventory-table">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>{{ __('messages.erp.ui.item') }}</th>
                            <th>{{ __('messages.erp.ui.category') }}</th>
                            <th>{{ __('messages.erp.ui.stock') }}</th>
                            <th>{{ __('messages.erp.ui.unit') }}</th>
                            <th>{{ __('messages.erp.ui.last_movement') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inventoryRows as $row)
                            <tr data-inventory-row>
                                <td class="font-black text-slate-900">{{ $row['sku'] }}</td>
                                <td>{{ $row['name'] }}</td>
                                <td>{{ $row['category'] }}</td>
                                <td><span class="rounded-xl bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-600">{{ $row['stock'] }}</span></td>
                                <td>{{ $row['unit'] }}</td>
                                <td>{{ $row['last'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        (() => {
            const search = document.querySelector('[data-inventory-search]');
            search?.addEventListener('input', () => {
                const q = search.value.toLowerCase();
                document.querySelectorAll('[data-inventory-row]').forEach((row) => {
                    row.classList.toggle('hidden', !row.textContent.toLowerCase().includes(q));
                });
            });
        })();
    </script>
</x-layouts.app>
