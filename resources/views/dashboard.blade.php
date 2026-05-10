<x-layouts.app :title="__('messages.dashboard.page_title')">
    @include('erp.partials.styles')

    @php
        $stats = collect($dashboardStats ?? []);
        $displayLocale = $displayLocale ?? app()->getLocale();
        $totalProducts = (int) $stats->get('total_products', 0);
        $totalUsers = (int) $stats->get('total_users', 0);
        $inventoryValue = (float) $stats->get('inventory_value', 0);
        $totalStock = (int) $stats->get('total_stock', 0);
        $today = now()->format('d/m/Y');
        $formatMoney = function (float|int $value) use ($displayLocale): string {
            if ($displayLocale === 'en') {
                $usdRate = (float) config('services.product_export.usd_rate', 25000);
                $usdRate = $usdRate > 0 ? $usdRate : 25000;

                return '$' . number_format($value / $usdRate, 2);
            }

            return number_format($value, 0, ',', '.') . ' đ';
        };
        $cards = [
            ['label' => __('messages.dashboard.month_revenue'), 'value' => $formatMoney($inventoryValue), 'delta' => '+12.5%', 'tone' => 'blue', 'icon' => 'M8 7h8M8 11h8M8 15h5'],
            ['label' => __('messages.dashboard.gross_profit'), 'value' => $formatMoney($inventoryValue * 0.34), 'delta' => '+8.2%', 'tone' => 'green', 'icon' => 'M5 15l4-4 3 3 7-7'],
            ['label' => __('messages.dashboard.accounts_receivable'), 'value' => $formatMoney(130000000), 'delta' => '-4.1%', 'tone' => 'amber', 'icon' => 'M12 3v18M17 7.5c-1.5-.8-3.6-1-5-.4-2 .8-2.1 3.2.1 3.9l1.8.6c2.5.8 2.3 3.9-.2 4.5-1.8.4-3.7 0-5.1-1'],
            ['label' => __('messages.dashboard.new_orders'), 'value' => __('messages.dashboard.order_count', ['count' => 3]), 'delta' => '+15%', 'tone' => 'violet', 'icon' => 'M6 6h15l-2 8H8L6 6zM6 6 5 2H2M9 20h.01M18 20h.01'],
            ['label' => __('messages.dashboard.deal_close_rate'), 'value' => '42%', 'delta' => '+5.4%', 'tone' => 'purple', 'icon' => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM12 16a4 4 0 1 0 0-8 4 4 0 0 0 0 8z'],
            ['label' => __('messages.dashboard.production_orders'), 'value' => __('messages.dashboard.production_order_count', ['count' => 2]), 'delta' => '+2.1%', 'tone' => 'cyan', 'icon' => 'm21 8-9-5-9 5 9 5 9-5zM3 8v8l9 5 9-5V8'],
            ['label' => __('messages.dashboard.overdue_orders'), 'value' => __('messages.dashboard.order_count', ['count' => 1]), 'delta' => '-1.5%', 'tone' => 'rose', 'icon' => 'M12 8v5M12 17h.01M10.3 3.9 2.4-1.4 2.4 1.4 6.4 11.1c.9 1.6-.2 3.5-2.1 3.5H4.6c-1.9 0-3-2-2.1-3.5z'],
            ['label' => __('messages.dashboard.absent_staff'), 'value' => __('messages.dashboard.people_count', ['count' => max(0, $totalUsers - 1)]), 'delta' => '+0%', 'tone' => 'slate', 'icon' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM22 21v-2a4 4 0 0 0-3-3.87'],
        ];
        $toneClasses = [
            'blue' => ['bg' => 'bg-blue-600', 'soft' => 'bg-blue-100', 'text' => 'text-blue-600'],
            'green' => ['bg' => 'bg-emerald-500', 'soft' => 'bg-emerald-100', 'text' => 'text-emerald-600'],
            'amber' => ['bg' => 'bg-amber-500', 'soft' => 'bg-amber-100', 'text' => 'text-amber-600'],
            'violet' => ['bg' => 'bg-indigo-500', 'soft' => 'bg-indigo-100', 'text' => 'text-indigo-600'],
            'purple' => ['bg' => 'bg-purple-500', 'soft' => 'bg-purple-100', 'text' => 'text-purple-600'],
            'cyan' => ['bg' => 'bg-cyan-500', 'soft' => 'bg-cyan-100', 'text' => 'text-cyan-600'],
            'rose' => ['bg' => 'bg-rose-500', 'soft' => 'bg-rose-100', 'text' => 'text-rose-600'],
            'slate' => ['bg' => 'bg-slate-700', 'soft' => 'bg-slate-100', 'text' => 'text-slate-600'],
        ];
    @endphp

    <div class="space-y-8">
        <section class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="text-4xl font-black tracking-tight text-slate-900">{{ __('messages.dashboard.welcome_admin', ['name' => auth()->user()->name ?? 'Admin']) }}</h2>
                <p class="mt-3 text-lg font-semibold text-slate-500">{{ __('messages.dashboard.database_description') }}</p>
            </div>
            <div class="erp-card inline-flex items-center gap-3 px-5 py-3 text-base font-black text-slate-600">
                <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>
                </svg>
                {{ $today }}
            </div>
        </section>

        <section class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($cards as $card)
                @php($tone = $toneClasses[$card['tone']])
                <article class="erp-card relative min-h-[168px] overflow-hidden p-7">
                    <div class="absolute -right-7 -top-7 h-28 w-28 rounded-full {{ $tone['soft'] }}"></div>
                    <div class="relative z-10 flex items-start justify-between gap-4">
                        <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl {{ $tone['bg'] }} text-white">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                <path d="{{ $card['icon'] }}"/>
                            </svg>
                        </span>
                        <span class="rounded-xl px-3 py-1 text-sm font-black {{ str_starts_with($card['delta'], '-') ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }}">{{ $card['delta'] }}</span>
                    </div>
                    <p class="mt-6 text-base font-black text-slate-400">{{ $card['label'] }}</p>
                    <p class="mt-3 text-3xl font-black text-slate-900">{{ $card['value'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
            <article class="erp-card p-7">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-2xl font-black text-slate-900">{{ __('messages.dashboard.cash_profit_analysis') }}</h3>
                    <span class="rounded-xl bg-blue-50 px-3 py-1 text-sm font-black text-blue-600">{{ __('messages.dashboard.current_month') }}</span>
                </div>
                <div class="mt-8 grid h-80 grid-cols-6 items-end gap-5 border-b border-dashed border-slate-200 px-4">
                    @foreach ([46, 72, 58, 90, 66, 82] as $bar)
                        <div class="flex h-full flex-col justify-end gap-3">
                            <div class="rounded-t-2xl bg-blue-600/85" style="height: {{ $bar }}%"></div>
                            <div class="rounded-t-2xl bg-emerald-500/80" style="height: {{ max(18, $bar - 24) }}%"></div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-5 flex items-center gap-6 text-sm font-black text-slate-500">
                    <span class="inline-flex items-center gap-2"><i class="h-3 w-3 rounded-full bg-blue-600"></i> {{ __('messages.dashboard.revenue') }}</span>
                    <span class="inline-flex items-center gap-2"><i class="h-3 w-3 rounded-full bg-emerald-500"></i> {{ __('messages.dashboard.profit') }}</span>
                </div>
            </article>

            <article class="erp-card p-7">
                <h3 class="text-2xl font-black text-slate-900">{{ __('messages.dashboard.production_progress') }}</h3>
                <div class="mt-8 space-y-5">
                    @foreach ([
                        [__('messages.dashboard.cutting_cnc'), 78],
                        [__('messages.dashboard.edge_banding'), 64],
                        [__('messages.dashboard.assembly'), 52],
                        [__('messages.dashboard.qc_packaging'), 38],
                    ] as [$label, $value])
                        <div>
                            <div class="flex items-center justify-between text-sm font-black text-slate-600">
                                <span>{{ $label }}</span>
                                <span>{{ $value }}%</span>
                            </div>
                            <div class="mt-2 h-3 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-gradient-to-r from-blue-600 to-emerald-500" style="width: {{ $value }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-8 rounded-2xl bg-slate-950 p-5 text-white">
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-slate-400">{{ __('messages.dashboard.inventory_total') }}</p>
                    <p class="mt-2 text-3xl font-black">{{ __('messages.dashboard.unit_count', ['count' => number_format($totalStock)]) }}</p>
                    <p class="mt-2 text-sm font-semibold text-slate-400">{{ __('messages.dashboard.available_products', ['count' => number_format($totalProducts)]) }}</p>
                </div>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            <article class="erp-card overflow-hidden">
                <header class="flex items-center justify-between border-b border-slate-100 bg-slate-50/60 px-6 py-5">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 12 2 2 4-5"/><circle cx="12" cy="12" r="10"/></svg>
                        <h3 class="text-lg font-black text-slate-900">{{ __('messages.dashboard.pending_approvals') }}</h3>
                    </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-600">0</span>
                </header>
                <div class="grid min-h-40 place-items-center p-8 text-center">
                    <div>
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full border-4 border-slate-200 text-slate-300">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 12 2 2 4-5"/><circle cx="12" cy="12" r="10"/></svg>
                        </div>
                        <p class="mt-4 text-sm font-semibold text-slate-400">{{ __('messages.dashboard.no_pending_tasks') }}</p>
                    </div>
                </div>
            </article>

            <article class="erp-card overflow-hidden border-rose-100">
                <header class="flex items-center gap-2 border-b border-rose-100 bg-rose-50 px-6 py-5">
                    <svg class="h-5 w-5 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v5M12 17h.01M10.3 3.9 2.5 17.5A2 2 0 0 0 4.2 20h15.6a2 2 0 0 0 1.7-2.5L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
                    <h3 class="text-lg font-black text-rose-700">{{ __('messages.dashboard.operation_hotspots') }}</h3>
                </header>
                <div class="grid min-h-40 place-items-center p-8 text-center">
                    <p class="text-sm font-semibold italic text-slate-400">{{ __('messages.dashboard.system_stable') }}</p>
                </div>
            </article>

            <article class="erp-card overflow-hidden border-amber-100">
                <header class="flex items-center gap-2 border-b border-amber-100 bg-amber-50 px-6 py-5">
                    <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m3 17 6-6 4 4 8-8M14 7h7v7"/></svg>
                    <h3 class="text-lg font-black text-amber-700">{{ __('messages.dashboard.sales_leaderboard') }}</h3>
                </header>
                <div class="grid min-h-40 place-items-center p-8 text-center">
                    <p class="text-sm font-semibold italic text-slate-400">{{ __('messages.dashboard.no_sales_data') }}</p>
                </div>
            </article>
        </section>
    </div>
</x-layouts.app>
