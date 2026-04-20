<x-layouts.app :title="__('messages.dashboard.page_title')">
    <div class="mx-auto max-w-7xl space-y-8 pb-12">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <div class="mb-2 inline-flex items-center gap-2 text-xs font-black uppercase tracking-[0.24em] text-indigo-500">
                    <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                    {{ __('messages.dashboard.eyebrow') }}
                </div>
                <h1 class="text-4xl font-black tracking-tight text-slate-900 dark:text-slate-100">
                    {{ __('messages.dashboard.title') }}
                </h1>
            </div>
            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white/80 p-1.5 shadow-sm dark:border-slate-800 dark:bg-slate-900/80">
                <button class="rounded-xl px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800">
                    {{ __('messages.dashboard.export_data') }}
                </button>
                <button class="rounded-xl bg-slate-900 px-5 py-2 text-sm font-bold text-white shadow-lg shadow-slate-900/10 hover:bg-slate-800 dark:bg-indigo-600 dark:hover:bg-indigo-500">
                    {{ __('messages.dashboard.new_report') }}
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <section class="rounded-[2rem] border border-slate-200 bg-white/90 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/80">
                <div class="mb-4 flex items-end justify-between">
                    <p class="text-sm font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">{{ __('messages.dashboard.month_revenue') }}</p>
                    <span class="rounded-lg bg-emerald-50 px-2 py-1 text-xs font-black text-emerald-600 dark:bg-emerald-500/10">+18.4%</span>
                </div>
                <div class="flex items-baseline gap-2">
                    <h2 class="text-4xl font-black tracking-tight text-slate-900 dark:text-slate-100">$42,850</h2>
                    <span class="font-medium italic text-slate-400">USD</span>
                </div>
                <div class="mt-6 h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div class="h-full w-3/4 rounded-full bg-slate-900 dark:bg-indigo-500"></div>
                </div>
            </section>

            <section class="rounded-[2rem] border border-slate-200 bg-white/90 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/80">
                <div class="mb-4 flex items-end justify-between">
                    <p class="text-sm font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">{{ __('messages.dashboard.active_users') }}</p>
                    <span class="rounded-lg bg-indigo-50 px-2 py-1 text-xs font-black text-indigo-600 dark:bg-indigo-500/10">{{ __('messages.dashboard.target') }}: 2k</span>
                </div>
                <div class="flex items-baseline gap-2">
                    <h2 class="text-4xl font-black tracking-tight text-slate-900 dark:text-slate-100">1,842</h2>
                    <span class="font-medium italic text-slate-400">User</span>
                </div>
                <div class="mt-6 flex -space-x-3 overflow-hidden">
                    @foreach ([1, 2, 3, 4, 5] as $index)
                        <img class="h-8 w-8 rounded-full ring-4 ring-white dark:ring-slate-900" src="https://i.pravatar.cc/150?u={{ $index }}" alt="">
                    @endforeach
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 ring-4 ring-white dark:bg-indigo-600 dark:ring-slate-900">
                        <span class="text-[10px] font-bold text-white">+12</span>
                    </div>
                </div>
            </section>

            <section class="rounded-[2rem] border border-slate-200 bg-white/90 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/80">
                <div class="mb-4 flex items-end justify-between">
                    <p class="text-sm font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">{{ __('messages.dashboard.conversion_rate') }}</p>
                    <span class="rounded-lg bg-rose-50 px-2 py-1 text-xs font-black text-rose-600 dark:bg-rose-500/10">-2.1%</span>
                </div>
                <div class="flex items-baseline gap-2">
                    <h2 class="text-4xl font-black tracking-tight text-slate-900 dark:text-slate-100">14.2</h2>
                    <span class="font-medium italic text-slate-400">%</span>
                </div>
                <div class="mt-6 flex h-8 items-end gap-1">
                    @foreach ([40, 70, 50, 90, 60, 80, 100] as $height)
                        <div class="flex-1 rounded-sm bg-slate-200 dark:bg-slate-700" style="height: {{ $height }}%"></div>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="grid grid-cols-12 gap-6">
            <section class="col-span-12 rounded-[2.5rem] border border-slate-200 bg-white/90 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/80 lg:col-span-8 lg:p-8">
                <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ __('messages.dashboard.flow_analysis') }}</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('messages.dashboard.updated_5m') }}</p>
                    </div>
                    <div class="inline-flex rounded-2xl bg-slate-50 p-1.5 dark:bg-slate-800">
                        <button class="rounded-xl bg-white px-5 py-2 text-xs font-black text-slate-900 shadow-sm dark:bg-slate-100">{{ __('messages.dashboard.income') }}</button>
                        <button class="rounded-xl px-5 py-2 text-xs font-bold text-slate-400 dark:text-slate-400">{{ __('messages.dashboard.expenses') }}</button>
                    </div>
                </div>
                <div class="h-[380px]">
                    <canvas id="ultraChart"></canvas>
                </div>
            </section>

            <aside class="col-span-12 space-y-6 lg:col-span-4">
                <section class="relative overflow-hidden rounded-[2.5rem] bg-indigo-600 p-8 text-white shadow-2xl shadow-indigo-200/40 dark:shadow-none">
                    <div class="relative z-10">
                        <h4 class="mb-4 text-3xl font-black leading-tight">{{ __('messages.dashboard.ready') }}</h4>
                        <p class="mb-6 text-sm text-indigo-100/90">{{ __('messages.dashboard.explore_ai') }}</p>
                        <a href="#" class="inline-flex rounded-2xl bg-white px-8 py-3 text-sm font-black text-indigo-600 transition-colors hover:bg-indigo-50">
                            {{ __('messages.dashboard.try_now') }}
                        </a>
                    </div>
                    <svg class="absolute right-[-10%] bottom-[-10%] h-48 w-48 text-white/10" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L4.5 20.29l.71.71L12 18l6.79 3 .71-.71z"/>
                    </svg>
                </section>

                <section class="rounded-[2.5rem] border border-slate-200 bg-white/90 p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900/80">
                    <h4 class="mb-6 text-xl font-black text-slate-900 dark:text-slate-100">{{ __('messages.dashboard.recent_transactions') }}</h4>
                    <div class="space-y-6">
                        @foreach ([
                            ['name' => 'Figma Pro Subscription', 'type' => __('messages.dashboard.software'), 'amount' => '-$15.00'],
                            ['name' => 'Payment from Client', 'type' => __('messages.dashboard.invoice'), 'amount' => '+$2,400.00'],
                            ['name' => 'Amazon AWS Cloud', 'type' => __('messages.dashboard.infrastructure'), 'amount' => '-$120.50'],
                        ] as $item)
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 dark:bg-slate-800">
                                        <div class="h-2 w-2 rounded-full bg-slate-400 dark:bg-slate-500"></div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-800 dark:text-slate-100">{{ $item['name'] }}</p>
                                        <p class="text-xs font-medium text-slate-400">{{ $item['type'] }}</p>
                                    </div>
                                </div>
                                <p class="text-sm font-black {{ str_starts_with($item['amount'], '+') ? 'text-emerald-500' : 'text-slate-900 dark:text-slate-100' }}">
                                    {{ $item['amount'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </section>
            </aside>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('ultraChart').getContext('2d');
        const gradientStroke = ctx.createLinearGradient(0, 230, 0, 50);
        gradientStroke.addColorStop(1, 'rgba(99, 102, 241, 0.2)');
        gradientStroke.addColorStop(0.2, 'rgba(99, 102, 241, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
                datasets: [{
                    label: 'Premium Data',
                    borderColor: '#0f172a',
                    borderWidth: 4,
                    pointRadius: 0,
                    pointHoverRadius: 8,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#0f172a',
                    pointBorderWidth: 3,
                    fill: true,
                    backgroundColor: gradientStroke,
                    tension: 0.4,
                    data: [2000, 4500, 2800, 6000, 4800, 7500, 9000]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 12, weight: '600' }, color: '#94a3b8' }
                    },
                    y: {
                        grid: { color: 'rgba(226, 232, 240, 0.5)', borderDash: [5, 5] },
                        ticks: { font: { size: 12, weight: '600' }, color: '#94a3b8', stepSize: 2000 }
                    }
                },
                interaction: { intersect: false, mode: 'index' }
            }
        });
    </script>
</x-layouts.app>
