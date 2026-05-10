<x-layouts.app :title="__('messages.erp.sidebar.purchase_orders')">
    @include('erp.partials.styles')

    @php($formatMoney = fn ($value) => number_format((int) $value, 0, ',', '.') . ' đ')

    <div class="space-y-6" data-po-page>
        <section class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <a href="{{ route('erp.inventory') }}" class="inline-flex items-center gap-2 text-sm font-black text-slate-500 hover:text-blue-600">
                    <span>←</span> {{ __('messages.erp.ui.back_to_inventory') }}
                </a>
                <div class="mt-4 flex items-center gap-4">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M3 5h11v11H3zM14 9h4l3 3v4h-7z"/><circle cx="7" cy="19" r="2"/><circle cx="18" cy="19" r="2"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-3xl font-black text-slate-900 dark:text-slate-100">{{ __('messages.erp.sidebar.purchase_orders') }}</h2>
                        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">{{ __('messages.erp.ui.purchase_desc') }}</p>
                    </div>
                </div>
            </div>
            <button type="button" class="erp-btn erp-btn-blue" data-add-po>{{ __('messages.erp.ui.create_po') }}</button>
        </section>

        <section class="erp-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="erp-table min-w-[980px]">
                    <thead>
                        <tr>
                            <th>{{ __('messages.erp.ui.order_code') }}</th>
                            <th>{{ __('messages.erp.ui.supplier') }}</th>
                            <th>{{ __('messages.erp.ui.total') }}</th>
                            <th>{{ __('messages.erp.ui.status') }}</th>
                            <th>{{ __('messages.erp.ui.created_at') }}</th>
                            <th>{{ __('messages.erp.ui.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="po-body">
                        @foreach ($purchaseOrders as $order)
                            <tr>
                                <td class="font-black text-slate-900">{{ $order['code'] }}</td>
                                <td>{{ $order['supplier'] }}</td>
                                <td>{{ $formatMoney($order['total']) }}</td>
                                <td><span class="rounded-xl bg-indigo-50 px-3 py-1 text-xs font-black text-indigo-600">{{ $order['status'] }}</span></td>
                                <td>{{ $order['created_at'] }}</td>
                                <td><button type="button" class="rounded-xl bg-emerald-50 px-3 py-2 text-xs font-black text-emerald-600" data-receive-po>{{ __('messages.erp.ui.receive_stock') }}</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <div class="erp-modal" data-po-modal aria-hidden="true">
            <div class="erp-modal-panel max-w-lg p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 dark:text-slate-100">{{ __('messages.erp.ui.create_po') }}</h3>
                        <p class="mt-1 text-sm font-semibold text-slate-500">{{ __('messages.erp.ui.create_po_desc') }}</p>
                    </div>
                    <button type="button" class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" data-po-close>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="mt-5 space-y-4">
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">{{ __('messages.erp.ui.supplier') }}</label>
                        <input class="erp-input" data-po-supplier placeholder="{{ __('messages.erp.ui.supplier') }}">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">{{ __('messages.erp.ui.expected_total') }}</label>
                        <input class="erp-input" data-po-total type="number" min="0" step="1000" placeholder="0">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="erp-btn erp-btn-outline" data-po-close>{{ __('messages.erp.ui.cancel') }}</button>
                    <button type="button" class="erp-btn erp-btn-blue" data-po-save>{{ __('messages.erp.ui.create') }}</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-po-page]');
            if (!root) return;
            const body = document.getElementById('po-body');
            const modal = root.querySelector('[data-po-modal]');
            const supplierInput = root.querySelector('[data-po-supplier]');
            const totalInput = root.querySelector('[data-po-total]');
            const labels = {{ \Illuminate\Support\Js::from([
                'draft' => __('messages.erp.ui.draft'),
                'receive' => __('messages.erp.ui.receive_stock'),
                'received' => __('messages.erp.ui.received'),
                'completed' => __('messages.erp.ui.completed'),
            ]) }};
            const formatMoney = (value) => `${Number(value || 0).toLocaleString('vi-VN')} đ`;
            const openModal = () => {
                modal?.classList.add('is-open');
                modal?.setAttribute('aria-hidden', 'false');
                supplierInput?.focus();
            };
            const closeModal = () => {
                modal?.classList.remove('is-open');
                modal?.setAttribute('aria-hidden', 'true');
                if (supplierInput) supplierInput.value = '';
                if (totalInput) totalInput.value = '';
            };
            root.querySelector('[data-add-po]')?.addEventListener('click', () => {
                openModal();
            });
            root.querySelectorAll('[data-po-close]').forEach((button) => button.addEventListener('click', closeModal));
            root.querySelector('[data-po-save]')?.addEventListener('click', () => {
                const supplier = supplierInput?.value.trim();
                if (!supplier) return;
                const code = `PO-${new Date().getFullYear()}-${String((body?.children.length || 0) + 28).padStart(3, '0')}`;
                const total = Number(totalInput?.value || 0);
                body?.insertAdjacentHTML('afterbegin', `
                    <tr>
                        <td class="font-black text-slate-900">${code}</td>
                        <td>${supplier}</td>
                        <td>${formatMoney(total)}</td>
                        <td><span class="rounded-xl bg-amber-50 px-3 py-1 text-xs font-black text-amber-600">${labels.draft}</span></td>
                        <td>${new Date().toISOString().slice(0, 10)}</td>
                        <td><button type="button" class="rounded-xl bg-emerald-50 px-3 py-2 text-xs font-black text-emerald-600" data-receive-po>${labels.receive}</button></td>
                    </tr>
                `);
                closeModal();
            });
            root.addEventListener('click', (event) => {
                const button = event.target.closest('[data-receive-po]');
                if (!button) return;
                const status = button.closest('tr')?.querySelector('td:nth-child(4)');
                if (status) {
                    status.innerHTML = `<span class="rounded-xl bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-600">${labels.received}</span>`;
                }
                button.textContent = labels.completed;
            });
        })();
    </script>
</x-layouts.app>
