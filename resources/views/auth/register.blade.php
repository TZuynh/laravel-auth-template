<x-layouts.auth :title="__('messages.auth.register_title')">
    <div class="w-full max-w-md px-4">
        <div class="rounded-[2.5rem] border border-slate-200/80 bg-white/90 p-10 shadow-[0_20px_50px_rgba(15,23,42,0.08)] backdrop-blur-xl transition-all duration-300 dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-none">
            <div class="mb-10 text-center">
                <h2 class="text-4xl font-black tracking-tight text-slate-900 dark:text-slate-100">
                    {{ __('messages.auth.register_heading') }}<span class="text-indigo-600">.</span>
                </h2>
                <p class="mt-3 text-sm font-medium text-slate-500 dark:text-slate-400">
                    {{ __('messages.auth.register_description') }}
                </p>
            </div>

            @if($errors->any())
                <div class="mb-6 rounded-r-2xl border-l-4 border-rose-500 bg-rose-50 p-4 text-xs text-rose-700 dark:bg-rose-500/10 dark:text-rose-300">
                    <p class="mb-1 font-black uppercase tracking-widest">{{ __('messages.auth.error_title') }}</p>
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('register.post') }}" method="POST" class="space-y-5">
                @csrf

                <div class="group">
                    <label class="mb-2 ml-1 block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 group-focus-within:text-indigo-500">
                        {{ __('messages.auth.name_label') }}
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="{{ __('messages.auth.name_label') }}" class="w-full rounded-2xl border border-transparent bg-slate-100/70 px-4 py-3.5 text-sm font-semibold text-slate-900 outline-none transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:bg-slate-800/80 dark:text-slate-100 dark:placeholder:text-slate-500">
                </div>

                <div class="group">
                    <label class="mb-2 ml-1 block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 group-focus-within:text-indigo-500">
                        {{ __('messages.auth.email_label') }}
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="example@domain.com" class="w-full rounded-2xl border border-transparent bg-slate-100/70 px-4 py-3.5 text-sm font-semibold text-slate-900 outline-none transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:bg-slate-800/80 dark:text-slate-100 dark:placeholder:text-slate-500">
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="group">
                        <label class="mb-2 ml-1 block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 group-focus-within:text-indigo-500">
                            {{ __('messages.auth.password_label') }}
                        </label>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full rounded-2xl border border-transparent bg-slate-100/70 px-4 py-3.5 text-sm font-semibold text-slate-900 outline-none transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:bg-slate-800/80 dark:text-slate-100 dark:placeholder:text-slate-500">
                    </div>
                    <div class="group">
                        <label class="mb-2 ml-1 block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 group-focus-within:text-indigo-500">
                            {{ __('messages.auth.confirm_label') }}
                        </label>
                        <input type="password" name="password_confirmation" required placeholder="••••••••" class="w-full rounded-2xl border border-transparent bg-slate-100/70 px-4 py-3.5 text-sm font-semibold text-slate-900 outline-none transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:bg-slate-800/80 dark:text-slate-100 dark:placeholder:text-slate-500">
                    </div>
                </div>

                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 py-4 font-bold text-white shadow-xl shadow-indigo-100 transition-all duration-300 hover:bg-indigo-600 active:scale-[0.98] dark:bg-indigo-600 dark:shadow-none dark:hover:bg-indigo-500">
                    <span>{{ __('messages.auth.submit_register') }}</span>
                    <svg class="h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </form>

            <div class="mt-10 text-center">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                    {{ __('messages.auth.have_account') }}
                    <a href="{{ route('login') }}" class="ml-1 font-bold text-indigo-600 underline decoration-2 underline-offset-4 decoration-indigo-100 transition-colors hover:text-indigo-700 hover:decoration-indigo-500">
                        {{ __('messages.auth.login_now') }}
                    </a>
                </p>
            </div>
        </div>

        <p class="mt-8 text-center text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">
            Nexus Core Security System
        </p>
    </div>
</x-layouts.auth>
