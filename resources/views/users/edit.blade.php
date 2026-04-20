<x-layouts.app :title="__('messages.users.edit_page_title')">
    <div class="mx-auto max-w-xl space-y-6">
        @if ($errors->any())
            <div class="rounded-r-2xl border-l-4 border-rose-500 bg-rose-50 p-4 shadow-sm dark:bg-rose-500/10 dark:text-rose-200">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-rose-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                    <h3 class="text-sm font-bold text-rose-800 dark:text-rose-200">{{ __('messages.auth.error_title') }}</h3>
                </div>
                <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-rose-700 dark:text-rose-200">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition-colors hover:text-indigo-600 dark:text-slate-400 dark:hover:text-slate-200">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            {{ __('messages.users.back_to_list') }}
        </a>

        <form method="POST" action="{{ route('users.update', $user) }}" class="rounded-[2rem] border border-slate-200 bg-white/90 p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900/80">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ __('messages.users.edit_heading') }}</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('messages.users.edit_description') }}</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('messages.users.name_label') }}</label>
                    <input name="name" value="{{ old('name', $user->name) }}" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-900 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100" required>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('messages.users.email_label') }}</label>
                    <input name="email" type="email" value="{{ old('email', $user->email) }}" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-900 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100" required>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('messages.users.role_label') }}</label>
                    <select name="role" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-900 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100" required>
                        <option value="administrator" @selected(old('role', $user->role ?? 'staff') === 'administrator')>{{ __('messages.users.role_administrator') }}</option>
                        <option value="staff" @selected(old('role', $user->role ?? 'staff') === 'staff')>{{ __('messages.users.role_staff') }}</option>
                    </select>
                </div>

                <div class="rounded-2xl border border-dashed border-slate-200 p-4 dark:border-slate-800">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ __('messages.users.change_password') }}</p>
                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-indigo-600 dark:text-indigo-300">{{ __('messages.users.new_password') }}</label>
                            <input type="password" name="password" placeholder="••••••••" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-900 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-indigo-600 dark:text-indigo-300">{{ __('messages.users.confirm_new_password') }}</label>
                            <input type="password" name="password_confirmation" placeholder="••••••••" class="w-full rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-900 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <button class="w-full rounded-xl bg-indigo-600 py-3 font-bold text-white shadow-lg shadow-indigo-200 transition-all hover:bg-indigo-700">
                    {{ __('messages.users.save_update') }}
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
