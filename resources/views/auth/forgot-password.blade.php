<x-layouts.auth title="Forgot Password">
    <div class="w-full max-w-md px-4">
        <div class="rounded-[2rem] border border-slate-200/80 bg-white/95 p-8 shadow-[0_20px_50px_rgba(15,23,42,0.08)] dark:border-slate-800 dark:bg-slate-900/90">
            <div class="mb-8 text-center">
                <div class="mb-4 inline-flex rounded-2xl bg-indigo-50 p-3 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h2 class="text-3xl font-black tracking-tight text-slate-900 dark:text-slate-100">Reset password</h2>
                <p class="mt-3 text-sm font-medium leading-6 text-slate-500 dark:text-slate-400">
                    Enter your account email. We will send a 6-digit code to your Gmail inbox.
                </p>
            </div>

            @if($errors->any())
                <div class="mb-5 rounded-r-2xl border-l-4 border-rose-500 bg-rose-50 p-4 text-xs text-rose-700 dark:bg-rose-500/10 dark:text-rose-300">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="mb-2 ml-2 block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="yourname@gmail.com" class="w-full rounded-2xl border border-transparent bg-slate-100/70 px-5 py-4 text-sm font-semibold text-slate-900 outline-none transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:bg-slate-800/80 dark:text-slate-100">
                </div>

                <button type="submit" class="flex w-full items-center justify-center gap-3 rounded-2xl bg-slate-900 py-4 font-bold text-white shadow-xl shadow-indigo-100 transition-all hover:bg-indigo-600 active:scale-[0.98] dark:bg-indigo-600 dark:shadow-none">
                    Send code
                </button>
            </form>

            <p class="mt-6 text-center text-sm font-medium text-slate-500">
                <a href="{{ route('login') }}" class="font-bold text-indigo-600 hover:text-indigo-700">Back to login</a>
            </p>
        </div>
    </div>
</x-layouts.auth>
