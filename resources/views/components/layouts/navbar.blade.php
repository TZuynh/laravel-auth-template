@php($locale = app()->getLocale())
@php($notifications = \Illuminate\Support\Facades\Auth::check() ? \App\Models\ActivityNotification::query()->latest()->limit(20)->get() : collect())
@php($serializedNotifications = $notifications->map(fn ($notification) => $notification->toDisplayArray())->values()->all())

<div class="relative flex h-20 items-center justify-between border-b border-slate-200/50 bg-white/40 px-8 backdrop-blur-xl dark:border-slate-800/80 dark:bg-slate-950/70">
    <div class="flex items-center gap-6">
        <h1 class="text-xl font-extrabold text-slate-800 tracking-tightest font-heading dark:text-slate-100">
            {{ $title }}<span class="text-indigo-500">.</span>
        </h1>

        <div class="hidden md:flex items-center bg-slate-100/50 border border-slate-200/50 px-4 py-2 rounded-2xl group focus-within:bg-white focus-within:ring-4 focus-within:ring-indigo-500/10 transition-all dark:bg-slate-900/70 dark:border-slate-800 dark:focus-within:bg-slate-900">
            <svg class="w-4 h-4 text-slate-400 group-focus-within:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" placeholder="{{ __('messages.nav_search') }}" class="bg-transparent border-none text-xs font-bold focus:ring-0 placeholder-slate-400 w-48 dark:text-slate-100">
        </div>
    </div>

    <div class="flex items-center gap-3">
        <div class="flex items-center rounded-2xl border border-slate-200 bg-white/80 p-1 shadow-sm dark:border-slate-800 dark:bg-slate-900/80">
            <a href="{{ route('locale.switch', 'vi') }}" class="px-3 py-1.5 text-xs font-black rounded-xl {{ $locale === 'vi' ? 'bg-indigo-600 text-white' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100' }}">VI</a>
            <a href="{{ route('locale.switch', 'en') }}" class="px-3 py-1.5 text-xs font-black rounded-xl {{ $locale === 'en' ? 'bg-indigo-600 text-white' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100' }}">EN</a>
        </div>

        <button type="button" data-theme-toggle class="relative p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all dark:hover:bg-slate-800" title="{{ __('messages.toggle_theme') }}">
            <svg data-theme-icon-sun class="w-6 h-6 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364 6.364l-1.414-1.414M7.05 7.05 5.636 5.636m11.314 0-1.414 1.414M7.05 16.95 5.636 18.364M12 8a4 4 0 100 8 4 4 0 000-8z"/>
            </svg>
            <svg data-theme-icon-moon class="w-6 h-6 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 118.646 3.646a7 7 0 1011.708 11.708z"/>
            </svg>
        </button>

        <div class="relative">
            <button type="button" id="notification-toggle" class="relative rounded-xl p-2 text-slate-400 transition-all hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-slate-800" aria-haspopup="true" aria-expanded="false">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span id="notification-badge" class="absolute top-1 right-1 hidden h-2.5 w-2.5 rounded-full bg-rose-500 ring-2 ring-white dark:ring-slate-950"></span>
            </button>

            <div
                id="notification-dropdown"
                data-empty-text="{{ __('messages.notifications.empty') }}"
                data-remove-url-template="{{ route('notifications.destroy', ['notification' => '__ID__']) }}"
                data-clear-url="{{ route('notifications.clear') }}"
                data-notifications='@json($serializedNotifications)'
                class="hidden fixed right-4 top-20 z-50 w-[min(calc(100vw-2rem),380px)] overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                    <div>
                        <p class="text-sm font-black text-slate-900 dark:text-slate-100">{{ __('messages.notifications.title') }}</p>
                        <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500">{{ __('messages.notifications.subtitle') }}</p>
                    </div>
                    <button type="button" id="notification-clear-all" class="text-xs font-black text-rose-600 hover:text-rose-700 dark:text-rose-300">
                        {{ __('messages.notifications.clear_all') }}
                    </button>
                </div>
                <div id="notification-list" class="max-h-[420px] space-y-2 overflow-y-auto p-3"></div>
            </div>
        </div>

        <div class="h-8 w-[1px] bg-slate-200 dark:bg-slate-800"></div>

        <div class="flex items-center gap-4 group cursor-pointer">
            @auth
                <div class="text-right hidden sm:block">
                    <a href="{{ route('profile.edit') }}" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                        <p class="text-sm font-black text-slate-800 leading-none group-hover:text-indigo-600 transition-colors dark:text-slate-100">
                            {{ auth()->user()->name }}
                        </p>
                        {{ __('messages.account_settings') }}
                    </a>
                </div>

                <div class="relative">
                    <img class="w-10 h-10 rounded-2xl border-2 border-white shadow-md group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 object-cover"
                        src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&bg=6366f1&color=fff' }}"
                        alt="Avatar">
                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full shadow-sm"></div>
                </div>

                <form action="{{ route('logout') }}" method="POST" class="ml-2">
                    @csrf
                    <button type="submit" class="flex items-center justify-center w-10 h-10 bg-slate-100 text-slate-500 hover:bg-rose-50 hover:text-rose-600 rounded-xl transition-all group shadow-sm active:scale-90 dark:bg-slate-900 dark:hover:bg-rose-500/10">
                        <svg class="w-5 h-5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            @else
                <div class="text-right">
                    <a href="{{ route('login') }}" class="text-sm font-bold text-indigo-600 hover:underline">{{ __('messages.sign_in') }}</a>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-slate-200 flex items-center justify-center text-slate-400 dark:bg-slate-800">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/></svg>
                </div>
            @endauth
        </div>
    </div>
</div>
