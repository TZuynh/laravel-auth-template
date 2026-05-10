<x-layouts.app :title="__('messages.erp.sidebar.contracts')">
    @include('erp.partials.styles')

    @php
        $formatMoney = fn ($value) => number_format((int) $value, 0, ',', '.') . ' đ';
    @endphp

    <div class="space-y-8" data-contracts-page>
        <section class="erp-card flex flex-col gap-5 p-7 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex items-center gap-4">
                <span class="inline-flex h-16 w-16 items-center justify-center rounded-3xl bg-orange-50 text-orange-600">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M6 2h12v20H6z"/><path d="M9 6h6M9 10h6M9 14h4"/>
                    </svg>
                </span>
                <h2 class="text-4xl font-black tracking-tight text-orange-600">{{ __('messages.erp.ui.contracts_title') }}</h2>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="button" class="erp-btn erp-btn-outline" data-print-section="#contract-print">{{ __('messages.erp.ui.print_list') }}</button>
                <button type="button" class="erp-btn erp-btn-outline" data-view-contracts="kanban">Kanban</button>
                <button type="button" class="erp-btn erp-btn-outline" data-view-contracts="table">{{ __('messages.erp.ui.table') }}</button>
                <button type="button" class="erp-btn erp-btn-orange" data-open-contract-modal>{{ __('messages.erp.ui.create_contract') }}</button>
            </div>
        </section>

        <section class="erp-card grid gap-4 border-orange-200 p-5 xl:grid-cols-[1fr_280px_220px]">
            <input class="erp-input" data-contract-search placeholder="{{ __('messages.erp.ui.contract_search_placeholder') }}">
            <select class="erp-input"><option>{{ __('messages.erp.ui.all_statuses') }}</option>@foreach ($contractStages as $stage)<option>{{ $stage }}</option>@endforeach</select>
            <select class="erp-input"><option>{{ __('messages.erp.ui.debt_filter') }}</option><option>{{ __('messages.erp.ui.unpaid_debt') }}</option><option>{{ __('messages.erp.ui.paid') }}</option></select>
        </section>

        <section class="erp-card overflow-hidden" id="contract-print" data-contract-table-panel>
            <div class="overflow-x-auto">
                <table class="erp-table min-w-[1120px]" id="contract-table">
                    <thead>
                        <tr>
                            <th>{{ __('messages.erp.ui.sequence') }}</th>
                            <th>{{ __('messages.erp.ui.contract_code') }}</th>
                            <th>{{ __('messages.erp.ui.customer') }}</th>
                            <th>{{ __('messages.erp.ui.grand_total') }}</th>
                            <th>{{ __('messages.erp.ui.deposit_progress') }}</th>
                            <th>{{ __('messages.erp.ui.debt') }}</th>
                            <th>{{ __('messages.erp.ui.status') }}</th>
                            <th class="erp-actions">{{ __('messages.erp.ui.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="contract-body">
                        @foreach ($contracts as $index => $contract)
                            <tr data-contract-row>
                                <td>{{ $index + 1 }}</td>
                                <td class="font-black text-slate-900">{{ $contract['code'] }}</td>
                                <td>
                                    <div class="font-black text-slate-900">{{ $contract['customer'] }}</div>
                                    <div class="mt-1 text-xs text-slate-400">{{ $contract['phone'] }}</div>
                                </td>
                                <td>{{ $formatMoney($contract['total']) }}</td>
                                <td>{{ $formatMoney($contract['deposit']) }}</td>
                                <td class="font-black {{ $contract['debt'] > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $formatMoney($contract['debt']) }}</td>
                                <td><span class="rounded-xl bg-orange-50 px-3 py-1 text-xs font-black text-orange-600">{{ $contract['status'] }}</span></td>
                                <td class="erp-actions">
                                    <div class="flex gap-2">
                                        <button type="button" class="rounded-xl bg-blue-50 px-3 py-2 text-xs font-black text-blue-600" data-edit-contract>{{ __('messages.erp.ui.edit') }}</button>
                                        <button type="button" class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-black text-rose-600" data-delete-row>{{ __('messages.erp.ui.delete') }}</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="hidden overflow-x-auto pb-4" data-contract-kanban-panel>
            <div class="grid min-w-[1380px] grid-cols-5 gap-5">
                @foreach ($contractStages as $stage)
                    <article class="min-h-[560px] rounded-3xl border border-slate-200 bg-slate-50 p-5" data-contract-stage="{{ $stage }}">
                        <header class="flex items-center justify-between rounded-2xl border border-orange-200 bg-orange-50 px-5 py-3 text-sm font-black uppercase tracking-[0.12em] text-orange-700">
                            <span>{{ $stage }}</span>
                            <span class="rounded-xl bg-white px-3 py-1">{{ collect($contracts)->where('status', $stage)->count() }}</span>
                        </header>
                        <div class="mt-5 space-y-4">
                            @foreach (collect($contracts)->where('status', $stage) as $contract)
                                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                    <h3 class="font-black text-slate-900">{{ $contract['code'] }}</h3>
                                    <p class="mt-1 text-sm font-semibold text-slate-500">{{ $contract['customer'] }}</p>
                                    <p class="mt-4 text-sm font-black text-rose-600">{{ __('messages.erp.ui.debt') }}: {{ $formatMoney($contract['debt']) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <div class="erp-modal" id="contract-modal">
            <div class="erp-modal-panel">
                <form class="p-6" data-contract-form>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-5">
                        <h3 class="text-2xl font-black text-slate-900">{{ __('messages.erp.ui.contract') }}</h3>
                        <button type="button" class="rounded-xl p-2 text-slate-400 hover:bg-slate-100" data-close-modal>{{ __('messages.erp.ui.close') }}</button>
                    </div>
                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        <input class="erp-input" name="code" placeholder="{{ __('messages.erp.ui.contract_code_placeholder') }}" required>
                        <input class="erp-input" name="customer" placeholder="{{ __('messages.erp.ui.customer_placeholder') }}" required>
                        <input class="erp-input" name="phone" placeholder="{{ __('messages.erp.ui.phone_placeholder') }}" required>
                        <input class="erp-input" name="total" type="number" placeholder="{{ __('messages.erp.ui.total_placeholder') }}" required>
                        <input class="erp-input" name="deposit" type="number" placeholder="{{ __('messages.erp.ui.deposit_placeholder') }}" required>
                        <select class="erp-input" name="status">@foreach ($contractStages as $stage)<option>{{ $stage }}</option>@endforeach</select>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" class="erp-btn erp-btn-outline" data-close-modal>{{ __('messages.erp.ui.cancel') }}</button>
                        <button class="erp-btn erp-btn-orange" type="submit">{{ __('messages.erp.ui.save_contract') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-contracts-page]');
            if (!root) return;
            const tablePanel = root.querySelector('[data-contract-table-panel]');
            const kanbanPanel = root.querySelector('[data-contract-kanban-panel]');
            const modal = document.getElementById('contract-modal');
            const form = root.querySelector('[data-contract-form]');
            const body = document.getElementById('contract-body');
            let editingRow = null;
            const labels = {{ \Illuminate\Support\Js::from([
                'edit' => __('messages.erp.ui.edit'),
                'delete' => __('messages.erp.ui.delete'),
            ]) }};
            const money = (value) => `${Number(value || 0).toLocaleString('vi-VN')} đ`;
            const setView = (view) => {
                tablePanel?.classList.toggle('hidden', view !== 'table');
                kanbanPanel?.classList.toggle('hidden', view !== 'kanban');
            };
            const close = () => { modal?.classList.remove('is-open'); form?.reset(); editingRow = null; };
            const open = () => modal?.classList.add('is-open');
            root.querySelectorAll('[data-view-contracts]').forEach((button) => button.addEventListener('click', () => setView(button.dataset.viewContracts)));
            root.querySelector('[data-open-contract-modal]')?.addEventListener('click', open);
            root.querySelectorAll('[data-close-modal]').forEach((button) => button.addEventListener('click', close));
            root.querySelector('[data-contract-search]')?.addEventListener('input', (event) => {
                const q = event.target.value.toLowerCase();
                root.querySelectorAll('[data-contract-row]').forEach((row) => row.classList.toggle('hidden', !row.textContent.toLowerCase().includes(q)));
            });
            root.addEventListener('click', (event) => {
                const del = event.target.closest('[data-delete-row]');
                if (del) {
                    del.closest('tr')?.remove();
                    return;
                }
                const edit = event.target.closest('[data-edit-contract]');
                if (edit && form) {
                    editingRow = edit.closest('tr');
                    const cells = editingRow.querySelectorAll('td');
                    form.code.value = cells[1].textContent.trim();
                    form.customer.value = cells[2].querySelector('div')?.textContent.trim() || '';
                    form.phone.value = cells[2].querySelector('.text-xs')?.textContent.trim() || '';
                    form.total.value = cells[3].textContent.replace(/\D/g, '');
                    form.deposit.value = cells[4].textContent.replace(/\D/g, '');
                    form.status.value = cells[6].textContent.trim();
                    open();
                }
            });
            form?.addEventListener('submit', (event) => {
                event.preventDefault();
                const data = Object.fromEntries(new FormData(form).entries());
                const debt = Math.max(0, Number(data.total) - Number(data.deposit));
                const index = editingRow ? editingRow.children[0].textContent : (body?.children.length || 0) + 1;
                const html = `<td>${index}</td><td class="font-black text-slate-900">${data.code}</td><td><div class="font-black text-slate-900">${data.customer}</div><div class="mt-1 text-xs text-slate-400">${data.phone}</div></td><td>${money(data.total)}</td><td>${money(data.deposit)}</td><td class="font-black ${debt > 0 ? 'text-rose-600' : 'text-emerald-600'}">${money(debt)}</td><td><span class="rounded-xl bg-orange-50 px-3 py-1 text-xs font-black text-orange-600">${data.status}</span></td><td class="erp-actions"><div class="flex gap-2"><button type="button" class="rounded-xl bg-blue-50 px-3 py-2 text-xs font-black text-blue-600" data-edit-contract>${labels.edit}</button><button type="button" class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-black text-rose-600" data-delete-row>${labels.delete}</button></div></td>`;
                if (editingRow) editingRow.innerHTML = html;
                else body?.insertAdjacentHTML('beforeend', `<tr data-contract-row>${html}</tr>`);
                close();
            });
            setView('{{ $viewMode === 'kanban' ? 'kanban' : 'table' }}');
        })();
    </script>
</x-layouts.app>
