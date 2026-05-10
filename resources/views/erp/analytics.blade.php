<x-layouts.app :title="__('messages.erp.analytics.title')">
    @include('erp.partials.styles')

    @php($money = fn ($value) => number_format((int) $value, 0, ',', '.') . ' đ')

    <div class="space-y-6" data-live-analytics data-live-url="{{ route('erp.analytics.live') }}">
        <section class="erp-card p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="flex items-center gap-4">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-500/10">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12h4l2-7 4 14 2-7h6"/></svg>
                    </span>
                    <div>
                        <h2 class="text-3xl font-black tracking-tight text-slate-900 dark:text-slate-100">{{ __('messages.erp.analytics.title') }}</h2>
                        <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">{{ __('messages.erp.analytics.description') }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="button" class="erp-btn erp-btn-blue">{{ __('messages.erp.analytics.system_tab') }}</button>
                    <button type="button" class="erp-btn erp-btn-outline">{{ __('messages.erp.analytics.traffic_tab') }}</button>
                    <button type="button" class="erp-btn erp-btn-outline">{{ __('messages.erp.analytics.sales_tab') }}</button>
                </div>
            </div>
        </section>

        <section class="flex items-center justify-between">
            <h3 class="flex items-center gap-3 text-xl font-black text-slate-900 dark:text-slate-100">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12h4l2-7 4 14 2-7h6"/></svg>
                {{ __('messages.erp.analytics.command_center') }}
            </h3>
            <span class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 px-5 py-3 text-xs font-black uppercase tracking-wider text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
                {{ __('messages.erp.analytics.live_tracking') }}
            </span>
        </section>

        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            <article class="erp-card border-emerald-200 p-6 dark:border-emerald-500/30">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300">{{ __('messages.erp.analytics.online') }}</p>
                    <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3 3 15 0 18M12 3c-3 3-3 15 0 18"/></svg>
                </div>
                <p class="mt-5 text-4xl font-black text-slate-900 dark:text-slate-100" data-metric="online">{{ $metrics['online'] }}</p>
                <p class="mt-1 text-sm font-bold text-slate-400">{{ __('messages.erp.analytics.people') }}</p>
            </article>

            <article class="erp-card p-6">
                <p class="text-xs font-black uppercase tracking-wider text-slate-500">{{ __('messages.erp.analytics.views_24h') }}</p>
                <p class="mt-5 text-4xl font-black text-blue-600" data-metric="views_24h">{{ number_format($metrics['views_24h']) }}</p>
            </article>

            <article class="erp-card p-6">
                <p class="text-xs font-black uppercase tracking-wider text-slate-500">{{ __('messages.erp.analytics.ad_clicks') }}</p>
                <p class="mt-5 text-4xl font-black text-purple-600" data-metric="ad_clicks">{{ number_format($metrics['ad_clicks']) }}</p>
            </article>

            <article class="erp-card p-6">
                <p class="text-xs font-black uppercase tracking-wider text-slate-500">{{ __('messages.erp.analytics.ad_spend_today') }}</p>
                <p class="mt-5 text-4xl font-black text-orange-600" data-metric="ad_spend_today">{{ $money($metrics['ad_spend_today']) }}</p>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            <article class="overflow-hidden rounded-[1.1rem] border border-rose-200 bg-rose-50/70 dark:border-rose-500/30 dark:bg-rose-500/10">
                <header class="border-b border-rose-200 px-5 py-4 dark:border-rose-500/20">
                    <h3 class="flex items-center gap-3 text-base font-black text-rose-800 dark:text-rose-200">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9 2.5 17.5A2 2 0 0 0 4.2 20h15.6a2 2 0 0 0 1.7-2.5L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
                        {{ __('messages.erp.analytics.security_alerts') }}
                    </h3>
                </header>
                <div class="space-y-3 p-5" data-security-list>
                    @foreach ($securityAlerts as $alert)
                        <div class="rounded-2xl border border-rose-200 bg-white/70 p-4 dark:border-rose-500/20 dark:bg-slate-950/70">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-black text-slate-900 dark:text-slate-100">{{ $alert['message'] }}</p>
                                <span class="rounded-xl bg-rose-600 px-3 py-1 text-[10px] font-black uppercase text-white">{{ __('messages.erp.analytics.critical') }}</span>
                            </div>
                            <p class="mt-2 text-xs font-mono text-slate-500">IP: {{ $alert['ip'] }} <span class="float-right">{{ $alert['time'] }}</span></p>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="erp-card p-6">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-lg font-black text-slate-900 dark:text-slate-100">{{ __('messages.erp.analytics.event_stream') }}</h3>
                    <p class="text-xs font-black text-slate-400" data-live-updated>{{ __('messages.erp.analytics.updated_at', ['time' => now()->format('H:i:s')]) }}</p>
                </div>
                <div class="mt-5 min-h-[300px] space-y-3" data-event-list>
                    @foreach ($events as $event)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                            <p class="text-sm font-black text-slate-900 dark:text-slate-100">{{ $event['message'] }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">{{ $event['actor'] }} · {{ $event['time'] }}</p>
                        </div>
                    @endforeach
                </div>
            </article>
        </section>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-live-analytics]');
            if (!root) return;
            const money = (value) => `${Number(value || 0).toLocaleString('vi-VN')} đ`;
            const number = (value) => Number(value || 0).toLocaleString('vi-VN');
            const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
            const renderSecurity = (items) => {
                const list = root.querySelector('[data-security-list]');
                if (!list) return;
                list.innerHTML = items.map((item) => `
                    <div class="rounded-2xl border border-rose-200 bg-white/70 p-4 dark:border-rose-500/20 dark:bg-slate-950/70">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-black text-slate-900 dark:text-slate-100">${escapeHtml(item.message)}</p>
                            <span class="rounded-xl bg-rose-600 px-3 py-1 text-[10px] font-black uppercase text-white">{{ __('messages.erp.analytics.critical') }}</span>
                        </div>
                        <p class="mt-2 text-xs font-mono text-slate-500">IP: ${escapeHtml(item.ip)} <span class="float-right">${escapeHtml(item.time)}</span></p>
                    </div>
                `).join('');
            };
            const renderEvents = (items) => {
                const list = root.querySelector('[data-event-list]');
                if (!list) return;
                list.innerHTML = (items.length ? items : [{ message: '{{ __('messages.erp.analytics.no_events') }}', actor: 'System', time: '' }]).map((item) => `
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                        <p class="text-sm font-black text-slate-900 dark:text-slate-100">${escapeHtml(item.message)}</p>
                        <p class="mt-1 text-xs font-semibold text-slate-500">${escapeHtml(item.actor)}${item.time ? ` · ${escapeHtml(item.time)}` : ''}</p>
                    </div>
                `).join('');
            };
            const refresh = async () => {
                try {
                    const response = await fetch(root.dataset.liveUrl, { headers: { 'Accept': 'application/json' } });
                    if (!response.ok) return;
                    const data = await response.json();
                    root.querySelector('[data-metric="online"]').textContent = number(data.metrics.online);
                    root.querySelector('[data-metric="views_24h"]').textContent = number(data.metrics.views_24h);
                    root.querySelector('[data-metric="ad_clicks"]').textContent = number(data.metrics.ad_clicks);
                    root.querySelector('[data-metric="ad_spend_today"]').textContent = money(data.metrics.ad_spend_today);
                    root.querySelector('[data-live-updated]').textContent = '{{ __('messages.erp.analytics.updated_at', ['time' => '__TIME__']) }}'.replace('__TIME__', data.updated_at);
                    renderSecurity(data.securityAlerts || []);
                    renderEvents(data.events || []);
                } catch (error) {}
            };
            window.setInterval(refresh, 5000);
        })();
    </script>
</x-layouts.app>
