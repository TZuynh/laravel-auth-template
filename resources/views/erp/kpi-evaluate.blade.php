<x-layouts.app :title="__('messages.erp.sidebar.kpi_evaluate')">
    @include('erp.partials.styles')

    <div class="space-y-8" data-kpi-evaluate-page>
        <section class="flex items-center gap-4">
            <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-500">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.5"/>
                </svg>
            </span>
            <h2 class="text-4xl font-black tracking-tight text-slate-900">Quản lý chấm điểm KPI</h2>
        </section>

        <section class="erp-card grid gap-4 p-5 xl:grid-cols-[auto_130px_130px_220px_1fr_auto_auto] xl:items-center">
            <div class="flex items-center gap-3 text-lg font-black text-slate-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 4h18l-7 8v6l-4 2v-8z"/></svg>
                Lọc dữ liệu:
            </div>
            <select class="erp-input"><option>Tháng {{ now()->format('m') }}</option></select>
            <select class="erp-input"><option>Năm {{ now()->format('Y') }}</option></select>
            <select class="erp-input">
                @foreach ($departments as $department)
                    <option>{{ $department }}</option>
                @endforeach
            </select>
            <input class="erp-input" data-kpi-search placeholder="Tìm nhân viên...">
            <button type="button" class="erp-btn erp-btn-dark" data-print-section="#kpi-evaluate-print">In danh sách</button>
            <button type="button" class="erp-btn erp-btn-green" data-export-table="#kpi-evaluate-table" data-filename="kpi-thang.csv">Xuất Excel</button>
        </section>

        <section class="erp-card overflow-hidden" id="kpi-evaluate-print">
            <div class="overflow-x-auto">
                <table class="erp-table min-w-[1050px]" id="kpi-evaluate-table">
                    <thead>
                        <tr>
                            <th>Nhân viên</th>
                            <th>Tự chấm (30%)</th>
                            <th>Cấp 1 (40%)</th>
                            <th>Chốt (30/70%)</th>
                            <th>KPI cuối cùng</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kpiEmployees as $employee)
                            <tr data-kpi-employee>
                                <td>
                                    <div class="font-black text-slate-900">{{ $employee['name'] }}</div>
                                    <div class="mt-1 text-xs font-semibold text-slate-400">{{ $employee['department'] }}</div>
                                </td>
                                <td><input class="erp-input h-11 min-h-0" type="number" value="{{ $employee['self'] }}"></td>
                                <td><input class="erp-input h-11 min-h-0" type="number" value="{{ $employee['level_one'] }}"></td>
                                <td><input class="erp-input h-11 min-h-0" type="number" value="{{ max(0, $employee['final'] - $employee['self'] - $employee['level_one']) }}"></td>
                                <td class="font-black text-emerald-600">{{ $employee['final'] }}</td>
                                <td><span class="rounded-xl bg-blue-50 px-3 py-1 text-xs font-black text-blue-600">{{ $employee['status'] }}</span></td>
                                <td><button type="button" class="rounded-xl bg-slate-950 px-3 py-2 text-xs font-black text-white" data-kpi-lock>Chốt</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-kpi-evaluate-page]');
            if (!root) return;
            const search = root.querySelector('[data-kpi-search]');
            search?.addEventListener('input', () => {
                const value = search.value.toLowerCase();
                root.querySelectorAll('[data-kpi-employee]').forEach((row) => {
                    row.classList.toggle('hidden', !row.textContent.toLowerCase().includes(value));
                });
            });
            root.querySelectorAll('[data-kpi-lock]').forEach((button) => {
                button.addEventListener('click', () => {
                    const row = button.closest('tr');
                    row?.querySelectorAll('input').forEach((input) => input.setAttribute('disabled', 'disabled'));
                    button.textContent = 'Đã chốt';
                    button.className = 'rounded-xl bg-emerald-50 px-3 py-2 text-xs font-black text-emerald-600';
                });
            });
        })();
    </script>
</x-layouts.app>
