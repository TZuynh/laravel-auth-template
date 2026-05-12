<x-layouts.auth title="Reset Password">
    <div class="w-full max-w-md px-4">
        <div class="rounded-[2rem] border border-slate-200/80 bg-white/95 p-8 shadow-[0_20px_50px_rgba(15,23,42,0.08)] dark:border-slate-800 dark:bg-slate-900/90">
            <div class="mb-8 text-center">
                <div class="mb-4 inline-flex rounded-2xl bg-indigo-50 p-3 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-3xl font-black tracking-tight text-slate-900 dark:text-slate-100">Enter reset code</h2>
                <p class="mt-3 text-sm font-medium leading-6 text-slate-500 dark:text-slate-400">
                    Check your Gmail inbox, enter the 6-digit code, then choose a new password.
                </p>
            </div>

            @if(session('status'))
                <div class="mb-5 rounded-r-2xl border-l-4 border-emerald-500 bg-emerald-50 p-4 text-xs font-bold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 rounded-r-2xl border-l-4 border-rose-500 bg-rose-50 p-4 text-xs text-rose-700 dark:bg-rose-500/10 dark:text-rose-300">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="mb-2 ml-2 block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Email</label>
                    <input type="email" name="email" value="{{ old('email', $email ?? '') }}" required class="w-full rounded-2xl border border-transparent bg-slate-100/70 px-5 py-4 text-sm font-semibold text-slate-900 outline-none transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:bg-slate-800/80 dark:text-slate-100">
                </div>

                <div>
                    <label class="mb-2 ml-2 block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Reset code</label>
                    <input type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" name="code" value="{{ old('code') }}" required autofocus placeholder="123456" class="w-full rounded-2xl border border-transparent bg-slate-100/70 px-5 py-4 text-center text-xl font-black tracking-[0.45em] text-slate-900 outline-none transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:bg-slate-800/80 dark:text-slate-100">
                </div>

                <div>
                    <label class="mb-2 ml-2 block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">New password</label>
                    <input type="password" name="password" required class="w-full rounded-2xl border border-transparent bg-slate-100/70 px-5 py-4 text-sm font-semibold text-slate-900 outline-none transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:bg-slate-800/80 dark:text-slate-100">
                </div>

                <div>
                    <label class="mb-2 ml-2 block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Confirm password</label>
                    <input type="password" name="password_confirmation" required class="w-full rounded-2xl border border-transparent bg-slate-100/70 px-5 py-4 text-sm font-semibold text-slate-900 outline-none transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:bg-slate-800/80 dark:text-slate-100">
                </div>

                <button type="submit" class="flex w-full items-center justify-center rounded-2xl bg-slate-900 py-4 font-bold text-white shadow-xl shadow-indigo-100 transition-all hover:bg-indigo-600 active:scale-[0.98] dark:bg-indigo-600 dark:shadow-none">
                    Update password
                </button>
            </form>
        </div>
    </div>
</x-layouts.auth>
