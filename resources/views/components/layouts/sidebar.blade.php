<div class="flex h-full w-64 flex-col border-r border-slate-200/60 bg-slate-950 text-slate-200 shadow-xl dark:border-slate-800">
    <div class="flex items-center gap-3 px-5 pt-6">
        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-500 shadow-lg shadow-indigo-500/40">
            <span class="font-black italic text-white">M</span>
        </div>
        <h2 class="text-2xl font-black tracking-tight text-white">MyApp</h2>
    </div>

    <p class="px-5 pt-10 pb-4 text-[10px] font-semibold uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400">
        {{ __('messages.sidebar.main_menu') }}
    </p>

    <nav class="flex-1 px-3">
        <ul class="space-y-2">
            <li>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 transition-colors {{ request()->is('dashboard*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="font-medium">{{ __('messages.sidebar.dashboard') }}</span>
                </a>
            </li>

            <li>
                <a href="{{ route('users.index') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 transition-colors {{ request()->routeIs('users.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="font-medium">{{ __('messages.sidebar.users') }}</span>
                </a>
            </li>

            <li>
                <a href="{{ route('products.index') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 transition-colors {{ request()->routeIs('products.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span class="font-medium">{{ __('messages.sidebar.products') }}</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="mt-auto border-t border-slate-800 px-5 py-6">
        <p class="text-[10px] font-medium tracking-wide text-slate-500">{{ __('messages.sidebar.version') }}</p>
    </div>
</div>
