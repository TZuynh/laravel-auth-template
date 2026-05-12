@php
    $sections = app(\App\Repositories\Contracts\SidebarMenuRepositoryInterface::class)
        ->groupedForUser(auth()->user());

    $icon = function (string $name): string {
        return match ($name) {
            'grid' => '<path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"/>',
            'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'file' => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 15l2 2 4-5"/>',
            'dollar' => '<path d="M12 2v20M17 6.5c-1.5-1-3.5-1.2-5-.5-2 .8-2.2 3.5 0 4.2l2.3.8c2.3.8 2.2 3.8-.2 4.5-1.7.5-3.8.1-5.1-1"/>',
            'cart' => '<path d="M6 6h15l-2 8H8L6 6z"/><path d="M6 6 5 2H2"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/>',
            'factory' => '<path d="M3 21V9l6 4V9l6 4V5h6v16z"/><path d="M7 17h2M12 17h2M17 17h2"/>',
            'pulse' => '<path d="M3 12h4l2-7 4 14 2-7h6"/>',
            'box' => '<path d="m21 8-9-5-9 5 9 5z"/><path d="M3 8v8l9 5 9-5V8M12 13v8"/>',
            'warehouse' => '<path d="M3 21V9l9-6 9 6v12"/><path d="M7 21v-8h10v8M9 17h6"/>',
            'truck' => '<path d="M3 5h11v11H3zM14 9h4l3 3v4h-7z"/><circle cx="7" cy="19" r="2"/><circle cx="18" cy="19" r="2"/>',
            'report' => '<path d="M6 2h9l5 5v15H6z"/><path d="M14 2v6h6M9 17h6M9 13h8"/>',
            'megaphone' => '<path d="m3 11 18-5v12L3 13v-2z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
            'pen' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
            'database' => '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/>',
            'video' => '<path d="M4 6h11a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z"/><path d="m17 10 5-3v10l-5-3z"/>',
            'image' => '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10" r="1.5"/><path d="m21 15-5-5L5 19"/>',
            'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.8 1.8 0 0 0 .4 2l.1.1-2 3.4-.2-.1a1.8 1.8 0 0 0-2.1.3l-.2.2a1.8 1.8 0 0 0-.5 1.1H9.1a1.8 1.8 0 0 0-.5-1.1l-.2-.2a1.8 1.8 0 0 0-2.1-.3l-.2.1-2-3.4.1-.1a1.8 1.8 0 0 0 .4-2 1.8 1.8 0 0 0-1.5-1.1H3v-4h.1a1.8 1.8 0 0 0 1.5-1.1 1.8 1.8 0 0 0-.4-2l-.1-.1 2-3.4.2.1a1.8 1.8 0 0 0 2.1-.3l.2-.2A1.8 1.8 0 0 0 9.1 2h5.8a1.8 1.8 0 0 0 .5 1.1l.2.2a1.8 1.8 0 0 0 2.1.3l.2-.1 2 3.4-.1.1a1.8 1.8 0 0 0-.4 2 1.8 1.8 0 0 0 1.5 1.1h.1v4h-.1a1.8 1.8 0 0 0-1.5 1.1z"/>',
            'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
            'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/>',
            default => '<circle cx="12" cy="12" r="9"/>',
        };
    };

    $renderItems = function (array $items) use ($icon): string {
        $html = '';

        foreach ($items as $item) {
            $isActive = count($item['active']) > 0
                && collect($item['active'])->contains(fn ($pattern) => request()->routeIs($pattern));
            $class = $isActive
                ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/20'
                : 'text-slate-400 hover:bg-slate-900 hover:text-white';

            $html .= '<li><a title="' . e($item['label']) . '" href="' . route($item['route']) . '" class="oa-nav-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-bold transition ' . $class . '">';
            $html .= '<svg class="h-[18px] w-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">' . $icon($item['icon']) . '</svg>';
            $html .= '<span data-sidebar-text class="truncate">' . e($item['label']) . '</span></a></li>';
        }

        return $html;
    };
@endphp

<div id="owl-sidebar" class="oa-sidebar flex h-full w-72 flex-col bg-[#0f172a] text-slate-200 shadow-2xl transition-[width] duration-300">
    <style>
        .oa-sidebar.is-collapsed { width: 5rem; }
        .oa-sidebar.is-collapsed [data-sidebar-text],
        .oa-sidebar.is-collapsed [data-sidebar-logo],
        .oa-sidebar.is-collapsed [data-brand-wordmark],
        .oa-sidebar.is-collapsed [data-sidebar-profile],
        .oa-sidebar.is-collapsed [data-sidebar-footer],
        .oa-sidebar.is-collapsed [data-sidebar-group] { display: none !important; }
        .oa-sidebar.is-collapsed [data-sidebar-brand] { justify-content: center; padding-left: .5rem; padding-right: .5rem; }
        .oa-sidebar.is-collapsed .oa-section-button,
        .oa-sidebar.is-collapsed .oa-nav-link { justify-content: center; padding-left: 0; padding-right: 0; }
        .oa-sidebar.is-collapsed .oa-section-list { border-left: 0; padding-left: 0; }
    </style>

    <div class="flex h-[76px] shrink-0 items-center gap-3 border-b border-white/5 px-5" data-sidebar-brand>
        <x-brand-logo class="text-white" />
        <div data-sidebar-logo class="hidden"></div>
    </div>

    <nav class="min-h-0 flex-1 overflow-y-auto px-3 py-4">
        @foreach ($sections as $sectionName => $section)
            @php
                $sectionKey = $section['section'] ?? $sectionName;
                $isSectionActive = collect($section['items'])->contains(fn ($item) => collect($item['active'])->contains(fn ($pattern) => request()->routeIs($pattern)));
            @endphp
            <section class="oa-section mt-3 first:mt-0" data-sidebar-section="{{ $sectionKey }}" data-sidebar-section-active="{{ $isSectionActive ? '1' : '0' }}">
                <button type="button" class="oa-section-button mb-1.5 flex w-full items-center justify-between rounded-xl px-2 py-2 text-white transition hover:bg-slate-900" title="{{ $section['title'] }}" data-sidebar-section-toggle aria-expanded="true">
                    <span class="flex min-w-0 items-center gap-3">
                        <svg class="h-[18px] w-[18px] shrink-0 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">{!! $icon($section['icon']) !!}</svg>
                        <span data-sidebar-text class="truncate text-[13px] font-black">{{ $section['title'] }}</span>
                    </span>
                    <svg data-sidebar-section-chevron class="h-4 w-4 shrink-0 text-slate-400 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m6 9 6 6 6-6"/>
                    </svg>
                </button>
                <ul class="oa-section-list space-y-1 border-l border-slate-800 pl-4" data-sidebar-group="{{ $sectionKey }}">
                    {!! $renderItems($section['items']) !!}
                </ul>
            </section>
        @endforeach
    </nav>

    <div class="shrink-0 border-t border-white/5 p-3">
        <button type="button" id="sidebar-collapse-toggle" class="mb-3 flex h-10 w-full items-center justify-center gap-2 rounded-xl border border-dashed border-slate-700 text-[10px] font-black uppercase tracking-[0.16em] text-slate-400 transition hover:border-blue-500 hover:text-white">
            <span id="sidebar-collapse-icon">&lt;</span>
            <span data-sidebar-text>{{ __('messages.erp.sidebar.collapse') }}</span>
        </button>

        <div class="flex items-center gap-2 rounded-2xl bg-slate-900 px-3 py-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-blue-600 text-sm font-black text-white">
                <img src="{{ auth()->user()?->avatar_url }}" alt="{{ auth()->user()?->name ?? 'Admin' }}" class="h-full w-full object-cover">
            </div>
            <div data-sidebar-profile class="min-w-0 flex-1">
                <div class="truncate text-sm font-black text-white">{{ auth()->user()->name ?? 'Admin Account' }}</div>
                <div class="mt-1 text-[9px] font-black uppercase tracking-wider text-slate-400"><span class="text-emerald-400">&bull;</span> {{ __('messages.erp.sidebar.admin') }} &middot; <span class="text-blue-400">Enterprise</span></div>
            </div>
        </div>

        <div class="mt-2 grid grid-cols-2 gap-2" data-sidebar-footer>
            <a href="{{ url('/') }}" class="flex h-10 items-center justify-center gap-2 rounded-xl bg-emerald-500/10 text-xs font-black text-emerald-400 hover:bg-emerald-500/15">{{ __('messages.erp.sidebar.website') }}</a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-rose-500/10 text-xs font-black text-rose-400 hover:bg-rose-500/15">{{ __('messages.erp.sidebar.logout') }}</button>
            </form>
        </div>
    </div>
</div>

<script>
    (() => {
        const sidebar = document.getElementById('owl-sidebar');
        if (!sidebar || sidebar.dataset.ready === '1') return;
        sidebar.dataset.ready = '1';

        const storageKey = 'owl_agency_sidebar_state';
        const collapseButton = document.getElementById('sidebar-collapse-toggle');
        const collapseIcon = document.getElementById('sidebar-collapse-icon');
        const setSectionOpen = (section, open) => {
            const list = section.querySelector('[data-sidebar-group]');
            const button = section.querySelector('[data-sidebar-section-toggle]');
            const chevron = section.querySelector('[data-sidebar-section-chevron]');
            list?.classList.toggle('hidden', !open);
            button?.setAttribute('aria-expanded', open ? 'true' : 'false');
            chevron?.classList.toggle('-rotate-90', !open);
        };

        const setCollapsed = (collapsed) => {
            sidebar.classList.toggle('is-collapsed', collapsed);
            if (collapseIcon) collapseIcon.textContent = collapsed ? '>' : '<';
            try { localStorage.setItem(storageKey, collapsed ? 'collapsed' : 'expanded'); } catch (error) {}
        };

        try {
            setCollapsed(localStorage.getItem(storageKey) === 'collapsed');
        } catch (error) {
            setCollapsed(false);
        }

        sidebar.querySelectorAll('[data-sidebar-section]').forEach((section) => {
            setSectionOpen(section, true);

            section.querySelector('[data-sidebar-section-toggle]')?.addEventListener('click', () => {
                if (sidebar.classList.contains('is-collapsed')) {
                    setCollapsed(false);
                    setSectionOpen(section, true);
                    return;
                }

                const nextOpen = section.querySelector('[data-sidebar-group]')?.classList.contains('hidden') ?? false;
                setSectionOpen(section, nextOpen);
            });
        });

        collapseButton?.addEventListener('click', () => setCollapsed(!sidebar.classList.contains('is-collapsed')));
    })();
</script>
