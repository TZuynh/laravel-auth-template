<x-layouts.app :title="__('messages.erp.sidebar.payroll')">
    @include('erp.partials.styles')

    @php
        $totalCommission = collect($payrollRows)->sum('commission');
        $totalNet = collect($payrollRows)->sum('net');
    @endphp

    <div class="space-y-8" data-payroll-page>
        <section class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex items-center gap-4">
                <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-950 text-amber-400 shadow-xl">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 2v20M17 6.5c-1.5-1-3.5-1.2-5-.5-2 .8-2.2 3.5 0 4.2l2.3.8c2.3.8 2.2 3.8-.2 4.5-1.7.5-3.8.1-5.1-1"/>
                    </svg>
                </span>
                <h2 class="text-3xl font-black tracking-tight text-slate-900 dark:text-slate-100">{{ __('messages.erp.sidebar.payroll') }}</h2>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="button" class="erp-btn erp-btn-outline" data-print-section="#payroll-print">{{ __('messages.erp.ui.payroll_print') }}</button>
                <button type="button" class="erp-btn erp-btn-outline" data-print-section="#payslip-print">{{ __('messages.erp.ui.payslip_print') }}</button>
                <button type="button" class="erp-btn erp-btn-green" data-approve-draft>{{ __('messages.erp.ui.approve_draft') }}</button>
                <button type="button" class="erp-btn erp-btn-dark" data-auto-close>{{ __('messages.erp.ui.auto_close_payroll') }}</button>
            </div>
        </section>

        <section class="erp-card grid gap-4 p-5 xl:grid-cols-[250px_250px_270px_1fr_auto] xl:items-center">
            <input type="month" class="erp-input" value="{{ now()->format('Y-m') }}">
            <select class="erp-input">
                @foreach ($departments as $department)
                    <option>{{ $department }}</option>
                @endforeach
            </select>
            <select class="erp-input"><option>{{ __('messages.erp.ui.all_statuses') }}</option><option>{{ __('messages.erp.ui.draft') }}</option><option>{{ __('messages.erp.ui.approved') }}</option><option>{{ __('messages.erp.ui.closed') }}</option></select>
            <div></div>
            <div class="text-right text-sm font-black text-slate-500">
                <p>{{ __('messages.erp.ui.total_commission') }} <span class="ml-3 text-slate-900">{{ number_format($totalCommission, 0, ',', '.') }} đ</span></p>
                <p class="mt-1">{{ __('messages.erp.ui.total_net') }} <span class="ml-3 text-amber-600">{{ number_format($totalNet, 0, ',', '.') }} đ</span></p>
            </div>
        </section>

        <section class="erp-card overflow-hidden" id="payroll-print">
            <div class="overflow-x-auto">
                <table class="erp-table min-w-[1100px]" id="payroll-table">
                    <thead>
                        <tr>
                            <th>{{ __('messages.erp.ui.employee') }}</th>
                            <th>{{ __('messages.erp.ui.actual_salary') }}</th>
                            <th>{{ __('messages.erp.ui.commission') }}</th>
                            <th>{{ __('messages.erp.ui.deduction') }}</th>
                            <th>{{ __('messages.erp.ui.net_salary') }}</th>
                            <th>{{ __('messages.erp.ui.status') }}</th>
                            <th class="erp-actions">{{ __('messages.erp.ui.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payrollRows as $row)
                            <tr data-payroll-row>
                                <td>
                                    <div class="font-black text-slate-900">{{ $row['name'] }}</div>
                                    <div class="mt-1 text-xs font-semibold text-slate-400">{{ $row['department'] }}</div>
                                </td>
                                <td>{{ number_format($row['salary'], 0, ',', '.') }} đ</td>
                                <td>{{ number_format($row['commission'], 0, ',', '.') }} đ</td>
                                <td>{{ number_format($row['deduction'], 0, ',', '.') }} đ</td>
                                <td class="font-black text-slate-950">{{ number_format($row['net'], 0, ',', '.') }} đ</td>
                                <td><span class="rounded-xl bg-amber-50 px-3 py-1 text-xs font-black text-amber-600" data-payroll-status>{{ $row['status'] }}</span></td>
                                <td class="erp-actions"><button type="button" class="rounded-xl bg-blue-50 px-3 py-2 text-xs font-black text-blue-600" data-open-payslip>{{ __('messages.erp.ui.personal_salary') }}</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section id="payslip-print" class="hidden">
            @foreach ($payrollRows as $row)
                <article class="mb-6 rounded-3xl border border-slate-200 bg-white p-6">
                    <h3 class="text-xl font-black text-slate-900">{{ __('messages.erp.ui.personal_salary') }} - {{ $row['name'] }}</h3>
                    <p class="mt-2 text-sm font-semibold text-slate-500">{{ now()->format('m/Y') }} · {{ $row['department'] }}</p>
                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <div>{{ __('messages.erp.ui.actual_salary') }}: <strong>{{ number_format($row['salary'], 0, ',', '.') }} đ</strong></div>
                        <div>{{ __('messages.erp.ui.commission') }}: <strong>{{ number_format($row['commission'], 0, ',', '.') }} đ</strong></div>
                        <div>{{ __('messages.erp.ui.deduction') }}: <strong>{{ number_format($row['deduction'], 0, ',', '.') }} đ</strong></div>
                        <div>{{ __('messages.erp.ui.net_salary') }}: <strong>{{ number_format($row['net'], 0, ',', '.') }} đ</strong></div>
                    </div>
                </article>
            @endforeach
        </section>

        <div class="erp-modal" data-payslip-modal aria-hidden="true">
            <div class="erp-modal-panel max-w-xl p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 dark:text-slate-100" data-payslip-name>{{ __('messages.erp.ui.personal_salary') }}</h3>
                        <p class="mt-1 text-sm font-semibold text-slate-500" data-payslip-department></p>
                    </div>
                    <button type="button" class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" data-payslip-close>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <div class="erp-soft p-4"><p class="text-xs font-black uppercase tracking-wider text-slate-500">{{ __('messages.erp.ui.actual_salary') }}</p><p class="mt-2 text-lg font-black text-slate-900 dark:text-slate-100" data-payslip-salary></p></div>
                    <div class="erp-soft p-4"><p class="text-xs font-black uppercase tracking-wider text-slate-500">{{ __('messages.erp.ui.commission') }}</p><p class="mt-2 text-lg font-black text-slate-900 dark:text-slate-100" data-payslip-commission></p></div>
                    <div class="erp-soft p-4"><p class="text-xs font-black uppercase tracking-wider text-slate-500">{{ __('messages.erp.ui.deduction') }}</p><p class="mt-2 text-lg font-black text-slate-900 dark:text-slate-100" data-payslip-deduction></p></div>
                    <div class="erp-soft p-4"><p class="text-xs font-black uppercase tracking-wider text-slate-500">{{ __('messages.erp.ui.net_salary') }}</p><p class="mt-2 text-lg font-black text-emerald-600" data-payslip-net></p></div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button type="button" class="erp-btn erp-btn-blue" data-payslip-close>{{ __('messages.erp.ui.close') }}</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-payroll-page]');
            if (!root) return;
            const labels = {{ \Illuminate\Support\Js::from([
                'approved' => __('messages.erp.ui.approved'),
                'closed' => __('messages.erp.ui.closed'),
                'payslip' => __('messages.erp.ui.personal_salary'),
                'employee' => __('messages.erp.ui.employee'),
            ]) }};
            const modal = root.querySelector('[data-payslip-modal]');
            const closeModal = () => {
                modal?.classList.remove('is-open');
                modal?.setAttribute('aria-hidden', 'true');
            };
            const setStatus = (text, classes) => {
                root.querySelectorAll('[data-payroll-status]').forEach((status) => {
                    status.textContent = text;
                    status.className = `rounded-xl px-3 py-1 text-xs font-black ${classes}`;
                });
            };
            root.querySelector('[data-approve-draft]')?.addEventListener('click', () => setStatus(labels.approved, 'bg-emerald-50 text-emerald-600'));
            root.querySelector('[data-auto-close]')?.addEventListener('click', () => setStatus(labels.closed, 'bg-blue-50 text-blue-600'));
            root.querySelectorAll('[data-open-payslip]').forEach((button) => {
                button.addEventListener('click', () => {
                    const row = button.closest('tr');
                    const cells = row?.querySelectorAll('td');
                    root.querySelector('[data-payslip-name]').textContent = `${labels.payslip} - ${cells?.[0]?.querySelector('.font-black')?.textContent.trim() || labels.employee}`;
                    root.querySelector('[data-payslip-department]').textContent = cells?.[0]?.querySelector('.text-xs')?.textContent.trim() || '';
                    root.querySelector('[data-payslip-salary]').textContent = cells?.[1]?.textContent.trim() || '';
                    root.querySelector('[data-payslip-commission]').textContent = cells?.[2]?.textContent.trim() || '';
                    root.querySelector('[data-payslip-deduction]').textContent = cells?.[3]?.textContent.trim() || '';
                    root.querySelector('[data-payslip-net]').textContent = cells?.[4]?.textContent.trim() || '';
                    modal?.classList.add('is-open');
                    modal?.setAttribute('aria-hidden', 'false');
                });
            });
            root.querySelectorAll('[data-payslip-close]').forEach((button) => button.addEventListener('click', closeModal));
        })();
    </script>
</x-layouts.app>
