@php
    $locale = app()->getLocale();
    $notifications = \Illuminate\Support\Facades\Auth::check()
        ? \App\Models\ActivityNotification::query()->with('actor:id,name,avatar')->latest()->limit(20)->get()
        : collect();
    $serializedNotifications = $notifications->map(fn ($notification) => $notification->toDisplayArray())->values()->all();
    $appName = (string) env('SITE_NAME', 'Owl Agency');
    $user = auth()->user();
@endphp

<div class="flex h-[80px] items-center justify-between border-b border-slate-200/70 bg-white px-5 dark:border-slate-800 dark:bg-slate-950 md:px-8">
    <div class="min-w-0">
        <p class="hidden text-xs font-black uppercase tracking-[0.22em] text-slate-400 dark:text-slate-500 md:block">
            {{ __('messages.navbar.admin_os', ['name' => $appName]) }}
        </p>
        <h1 class="sr-only">{{ $title }}</h1>
    </div>

    <div class="flex items-center gap-4 text-slate-400">
        <div class="hidden items-center gap-1 rounded-2xl bg-slate-100 p-1 dark:bg-slate-900 md:flex">
            <a href="{{ route('locale.switch', 'vi') }}" class="rounded-xl px-3 py-1.5 text-xs font-black {{ $locale === 'vi' ? 'bg-white text-blue-600 shadow-sm dark:bg-slate-800' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white' }}">VI</a>
            <a href="{{ route('locale.switch', 'en') }}" class="rounded-xl px-3 py-1.5 text-xs font-black {{ $locale === 'en' ? 'bg-white text-blue-600 shadow-sm dark:bg-slate-800' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white' }}">EN</a>
        </div>

        <button type="button" data-theme-toggle class="inline-flex h-10 w-10 items-center justify-center rounded-2xl text-slate-400 transition hover:bg-slate-100 hover:text-blue-600 dark:hover:bg-slate-900 dark:hover:text-blue-400" title="{{ __('messages.toggle_theme') }}">
            <svg data-theme-icon-moon class="h-6 w-6 block dark:hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 12.6A8 8 0 1 1 11.4 4 6.5 6.5 0 0 0 20 12.6z"/>
            </svg>
            <svg data-theme-icon-sun class="h-6 w-6 hidden dark:block" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2M12 20v2M4 12H2M22 12h-2M5 5l1.5 1.5M17.5 17.5 19 19M19 5l-1.5 1.5M6.5 17.5 5 19"/>
            </svg>
        </button>

        <div class="relative">
            <button type="button" id="notification-toggle" class="relative inline-flex h-10 w-10 items-center justify-center rounded-2xl text-slate-400 transition hover:bg-slate-100 hover:text-blue-600 dark:hover:bg-slate-900 dark:hover:text-blue-400" aria-haspopup="true" aria-expanded="false">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 0 1-6 0v-1"/>
                </svg>
                <span id="notification-badge" class="absolute right-2 top-2 hidden h-2.5 w-2.5 rounded-full bg-rose-500 ring-2 ring-white"></span>
            </button>

            <div
                id="notification-dropdown"
                data-empty-text="{{ __('messages.notifications.empty') }}"
                data-remove-url-template="{{ route('notifications.destroy', ['notification' => '__ID__']) }}"
                data-clear-url="{{ route('notifications.clear') }}"
                data-notifications='@json($serializedNotifications)'
                class="fixed right-4 top-24 z-50 hidden w-[min(calc(100vw-2rem),380px)] overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-950"
            >
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                    <div>
                        <p class="text-sm font-black text-slate-900">{{ __('messages.notifications.title') }}</p>
                        <p class="mt-1 text-[11px] font-medium text-slate-400">{{ __('messages.notifications.subtitle') }}</p>
                    </div>
                    <button type="button" id="notification-clear-all" class="text-xs font-black text-rose-600 hover:text-rose-700">
                        {{ __('messages.notifications.clear_all') }}
                    </button>
                </div>
                <div id="notification-list" class="max-h-[420px] space-y-2 overflow-y-auto p-3"></div>
            </div>
        </div>

        <div class="h-8 w-px bg-slate-200"></div>

        <a href="{{ route('profile.edit') }}" class="inline-flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl bg-blue-600 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700" title="{{ __('messages.account_settings') }}">
            <img src="{{ $user?->avatar_url }}" alt="{{ $user?->name ?? 'Admin' }}" class="h-full w-full object-cover">
        </a>
    </div>
</div>
