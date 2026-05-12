<x-layouts.auth :title="__('messages.auth.login_title')">
    <div class="w-full max-w-md px-4">
        <div class="rounded-[2.5rem] border border-slate-200/80 bg-white/90 p-10 shadow-[0_20px_50px_rgba(15,23,42,0.08)] backdrop-blur-xl transition-all duration-300 dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-none">
            <div class="mb-10 text-center">
                <div class="mb-4 inline-flex rounded-2xl bg-indigo-50 p-3 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h2 class="text-4xl font-black tracking-tight text-slate-900 dark:text-slate-100">
                    {{ __('messages.auth.login_heading') }}<span class="text-indigo-600">.</span>
                </h2>
                <p class="mt-3 text-sm font-medium text-slate-500 dark:text-slate-400">
                    {{ __('messages.auth.login_description') }}
                </p>
            </div>

            @if($errors->any())
                <div class="mb-6 rounded-r-2xl border-l-4 border-rose-500 bg-rose-50 p-4 text-xs text-rose-700 dark:bg-rose-500/10 dark:text-rose-300">
                    <p class="mb-1 font-black uppercase tracking-widest">{{ __('messages.auth.error_title') }}</p>
                    {{ $errors->first() }}
                </div>
            @endif

            @if(session('status'))
                <div class="mb-6 rounded-r-2xl border-l-4 border-emerald-500 bg-emerald-50 p-4 text-xs font-bold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-6">
                @csrf

                <div class="group">
                    <label class="mb-2 ml-2 block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 group-focus-within:text-indigo-500">
                        {{ __('messages.auth.email_label') }}
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="yourname@domain.com" class="w-full rounded-2xl border border-transparent bg-slate-100/70 px-5 py-4 text-sm font-semibold text-slate-900 outline-none transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:bg-slate-800/80 dark:text-slate-100 dark:placeholder:text-slate-500">
                </div>

                <div class="group" x-data="{ show: false }">
                    <div class="mb-2 ml-2 flex items-center justify-between">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 group-focus-within:text-indigo-500">
                            {{ __('messages.auth.password_label') }}
                        </label>
                        <a href="{{ route('password.request') }}" class="text-[10px] font-black uppercase tracking-widest text-indigo-600 transition-all hover:text-indigo-700">
                            {{ __('messages.auth.forgot') }}
                        </a>
                    </div>

                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password" required placeholder="••••••••" class="w-full rounded-2xl border border-transparent bg-slate-100/70 px-5 py-4 pr-14 text-sm font-semibold tracking-widest text-slate-900 outline-none transition-all duration-300 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:bg-slate-800/80 dark:text-slate-100 dark:placeholder:text-slate-500">

                        <div class="absolute right-2 top-1/2 flex -translate-y-1/2 items-center pr-2">
                            <button type="button" @click="show = !show" class="overflow-hidden rounded-xl p-2 text-slate-400 transition-all duration-300 hover:bg-indigo-50 hover:text-indigo-600 active:scale-90 dark:hover:bg-slate-700" title="{{ __('messages.toggle_theme') }}">
                                <div class="relative flex h-5 w-5 items-center justify-center transition-transform duration-500" :class="show ? 'rotate-180' : ''">
                                    <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg x-show="show" x-cloak class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                    </svg>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="flex w-full items-center justify-center gap-3 rounded-2xl bg-slate-900 py-4 font-bold text-white shadow-xl shadow-indigo-100 transition-all duration-300 hover:bg-indigo-600 active:scale-[0.98] dark:bg-indigo-600 dark:shadow-none dark:hover:bg-indigo-500">
                    <span>{{ __('messages.auth.sign_in') }}</span>
                    <svg class="h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>

            <p class="mt-10 text-center text-sm font-medium text-slate-500 dark:text-slate-400">
                Accounts are created by the administrator.
            </p>
        </div>

        <div class="mt-8 flex items-center justify-center gap-4">
            <span class="h-px w-8 bg-slate-200 dark:bg-slate-800"></span>
            <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">Nexus Core v3.0</p>
            <span class="h-px w-8 bg-slate-200 dark:bg-slate-800"></span>
        </div>
    </div>
</x-layouts.auth>
