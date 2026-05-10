<x-layouts.app :title="__('messages.erp.sidebar.stock_report')">
    @include('erp.partials.styles')

    <div class="space-y-6">
        <section class="erp-card flex flex-col gap-4 p-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-4">
                <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M6 2h9l5 5v15H6z"/><path d="M14 2v6h6M9 17h6M9 13h8"/>
                    </svg>
                </span>
                <div>
                    <h2 class="text-3xl font-black text-slate-900">{{ __('messages.erp.sidebar.stock_report') }}</h2>
                    <p class="mt-1 text-sm font-semibold text-slate-500">{{ __('messages.erp.ui.stock_report_desc') }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <input type="month" value="{{ now()->format('Y-m') }}" class="erp-input w-52">
                <button type="button" class="erp-btn erp-btn-outline" data-print-section="#stock-report-print">{{ __('messages.erp.ui.print_report') }}</button>
                <button type="button" class="erp-btn erp-btn-green" data-export-table="#stock-report-table" data-filename="bao-cao-xnt.csv">{{ __('messages.erp.ui.export_excel') }}</button>
            </div>
        </section>

        <section class="erp-card overflow-hidden" id="stock-report-print">
            <div class="overflow-x-auto">
                <table class="erp-table min-w-[980px]" id="stock-report-table">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>{{ __('messages.erp.ui.item') }}</th>
                            <th>{{ __('messages.erp.ui.opening') }}</th>
                            <th>{{ __('messages.erp.ui.incoming') }}</th>
                            <th>{{ __('messages.erp.ui.outgoing') }}</th>
                            <th>{{ __('messages.erp.ui.closing') }}</th>
                            <th>{{ __('messages.erp.ui.unit') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reportRows as $row)
                            <tr>
                                <td class="font-black text-slate-900">{{ $row['sku'] }}</td>
                                <td>{{ $row['name'] }}</td>
                                <td>{{ $row['opening'] }}</td>
                                <td class="text-emerald-600">{{ $row['incoming'] }}</td>
                                <td class="text-rose-600">{{ $row['outgoing'] }}</td>
                                <td class="font-black text-slate-900">{{ $row['closing'] }}</td>
                                <td>{{ $row['unit'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-layouts.app>
