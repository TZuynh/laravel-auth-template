<x-layouts.app :title="__('messages.users.page_title')">
    @php($isAdmin = in_array(strtolower(trim((string) (auth()->user()->role ?? ''))), ['administrator', 'admin'], true))

    <div class="space-y-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="text-3xl font-black tracking-tight text-slate-900 dark:text-slate-100">
                    {{ __('messages.users.title') }}
                </h2>
                <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">
                    {{ __('messages.users.description') }}
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <form method="GET" action="{{ route('users.index') }}" class="relative w-full sm:w-80" data-auto-search>
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" name="q" value="{{ $q ?? request('q') }}" placeholder="{{ __('messages.users.search_placeholder') }}" class="h-12 w-full rounded-2xl border border-slate-200 bg-white pl-10 pr-10 text-sm text-slate-900 shadow-sm outline-none transition-all placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500">

                    @if(!empty($q ?? request('q')))
                        <a href="{{ route('users.index') }}" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200" title="{{ __('messages.products.reset') }}">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                    @endif
                </form>

                @if ($isAdmin)
                    <a href="{{ route('users.create') }}" class="inline-flex h-12 items-center gap-2 rounded-2xl bg-indigo-600 px-5 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition-all hover:bg-indigo-700 active:scale-95">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('messages.users.add') }}
                    </a>
                @endif
            </div>
        </div>

        <div class="overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white/90 shadow-sm dark:border-slate-800 dark:bg-slate-900/80">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="bg-slate-50/70 dark:bg-slate-800/70">
                            <th class="px-8 py-5 text-[11px] font-black uppercase tracking-[0.15em] text-slate-400 dark:text-slate-500">{{ __('messages.users.table_user') }}</th>
                            <th class="px-6 py-5 text-[11px] font-black uppercase tracking-[0.15em] text-slate-400 dark:text-slate-500">{{ __('messages.users.email') }}</th>
                            <th class="px-6 py-5 text-[11px] font-black uppercase tracking-[0.15em] text-slate-400 dark:text-slate-500">{{ __('messages.users.role') }}</th>
                            <th class="px-8 py-5 text-right text-[11px] font-black uppercase tracking-[0.15em] text-slate-400 dark:text-slate-500">{{ __('messages.users.actions') }}</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($users as $user)
                            @php($role = strtolower(trim((string) ($user->role ?? 'staff'))))
                            @php($avatarUrl = $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&bg=6366f1&color=fff')

                            <tr class="transition-colors hover:bg-indigo-50/30 dark:hover:bg-slate-800/60">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <button type="button" class="relative group" data-user-trigger data-name="{{ $user->name }}" data-email="{{ $user->email }}" data-id="{{ $user->id }}" data-role="{{ $role }}" data-avatar="{{ $avatarUrl }}">
                                            <img class="h-11 w-11 rounded-2xl border-2 border-white object-cover shadow-md transition-transform duration-300 group-hover:scale-105 group-hover:rotate-3 dark:border-slate-900" src="{{ $avatarUrl }}" alt="Avatar">
                                            <span class="absolute -bottom-1 -right-1 h-3.5 w-3.5 rounded-full border-2 border-white bg-emerald-500 dark:border-slate-900"></span>
                                        </button>
                                        <button type="button" class="text-left" data-user-trigger data-name="{{ $user->name }}" data-email="{{ $user->email }}" data-id="{{ $user->id }}" data-role="{{ $role }}" data-avatar="{{ $avatarUrl }}">
                                            <p class="text-sm font-bold tracking-tight text-slate-900 transition-colors group-hover:text-indigo-600 dark:text-slate-100">{{ $user->name }}</p>
                                            <p class="text-[11px] font-medium text-slate-400">{{ __('messages.users.profile_id') }}: #{{ $user->id }}</p>
                                        </button>
                                    </div>
                                </td>

                                <td class="px-6 py-5">
                                    <span class="text-sm font-medium italic text-slate-600 dark:text-slate-300">{{ $user->email }}</span>
                                </td>

                                <td class="px-6 py-5">
                                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-wider {{ $role === 'administrator' || $role === 'admin' ? 'border-indigo-100 bg-indigo-50 text-indigo-600 dark:border-indigo-500/20 dark:bg-indigo-500/10 dark:text-indigo-300' : 'border-emerald-100 bg-emerald-50 text-emerald-600 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300' }}">
                                        {{ $role === 'administrator' || $role === 'admin' ? __('messages.users.role_administrator') : __('messages.users.role_staff') }}
                                    </span>
                                </td>

                                <td class="px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($isAdmin)
                                            <a href="{{ route('users.edit', $user) }}" class="rounded-xl p-2 text-slate-400 transition-all hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-slate-800" title="{{ __('messages.users.edit') }}">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2.0001 2.0001 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>

                                            <button type="button" data-users-delete="1" data-action="{{ route('users.destroy', $user) }}" data-user-name="{{ $user->name }}" class="rounded-xl p-2 text-slate-400 transition-all hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10" title="{{ __('messages.users.delete') }}">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                                    {{ __('messages.users.no_users') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/50 px-8 py-6 dark:border-slate-800 dark:bg-slate-900/80">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    <div id="profile-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" id="profile-modal-overlay"></div>
        <div class="relative w-full max-w-sm overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white p-8 text-center shadow-2xl dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col items-center">
                <div class="relative mb-4">
                    <img id="p-avatar" src="" class="h-24 w-24 rounded-[2rem] border-4 border-white object-cover shadow-xl dark:border-slate-900">
                    <span class="absolute -bottom-1 -right-1 h-6 w-6 rounded-full border-4 border-white bg-emerald-500 dark:border-slate-900"></span>
                </div>

                <h3 id="p-name" class="text-2xl font-black leading-tight text-slate-900 dark:text-slate-100"></h3>
                <p id="p-role-badge" class="mt-2 rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-widest"></p>

                <div class="mt-6 w-full space-y-3">
                    <div class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-4 text-left dark:border-slate-800 dark:bg-slate-800/70">
                        <div class="rounded-xl bg-white p-2 text-indigo-500 shadow-sm dark:bg-slate-900">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-[9px] font-black uppercase tracking-wider text-slate-400">{{ __('messages.users.email') }}</p>
                            <p id="p-email" class="truncate text-sm font-bold text-slate-700 dark:text-slate-200"></p>
                        </div>
                    </div>
                </div>

                <button type="button" id="p-close" class="mt-6 w-full rounded-2xl bg-slate-900 py-4 text-sm font-bold text-white shadow-lg shadow-slate-200 transition-all active:scale-95 hover:bg-indigo-600 dark:bg-indigo-600 dark:shadow-none dark:hover:bg-indigo-500">
                    {{ __('messages.users.close_profile') }}
                </button>
            </div>
        </div>
    </div>

    @if ($isAdmin)
        <div id="users-delete-modal" class="fixed inset-0 z-[70] hidden">
            <div id="users-delete-overlay" class="absolute inset-0 bg-slate-900/30 backdrop-blur-sm"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="w-full max-w-sm overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                    <div class="px-5 py-4">
                        <p class="text-sm font-black text-slate-900 dark:text-slate-100">{{ __('messages.users.delete_title') }}</p>
                        <p class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">
                            {{ __('messages.users.delete_desc') }} <span id="users-delete-name" class="font-black text-slate-700 dark:text-slate-200"></span>
                        </p>
                    </div>
                    <div class="flex items-center justify-end gap-2 px-5 pb-5">
                        <button id="users-delete-cancel" type="button" class="rounded-2xl px-4 py-2 text-sm font-bold text-slate-600 transition-all hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                            {{ __('messages.users.cancel') }}
                        </button>
                        <button id="users-delete-confirm" type="button" class="rounded-2xl bg-rose-600 px-4 py-2 text-sm font-black text-white shadow-lg shadow-rose-200 transition-all">
                            {{ __('messages.users.delete_confirm') }}
                        </button>
                    </div>
                    <form id="users-delete-form" method="POST" class="hidden">@csrf @method('DELETE')</form>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const profileModal = document.getElementById('profile-modal');
            const profileOverlay = document.getElementById('profile-modal-overlay');
            const profileClose = document.getElementById('p-close');

            document.querySelectorAll('[data-user-trigger]').forEach((trigger) => {
                trigger.addEventListener('click', function () {
                    const data = this.dataset;

                    document.getElementById('p-name').textContent = data.name;
                    document.getElementById('p-email').textContent = data.email;
                    document.getElementById('p-avatar').src = data.avatar;

                    const badge = document.getElementById('p-role-badge');
                    const isAdminRole = data.role === 'administrator' || data.role === 'admin';
                    badge.textContent = isAdminRole ? '{{ __('messages.users.role_administrator') }}' : '{{ __('messages.users.role_staff') }}';
                    badge.className = isAdminRole
                        ? 'mt-2 rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-indigo-600 dark:border-indigo-500/20 dark:bg-indigo-500/10 dark:text-indigo-300'
                        : 'mt-2 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300';

                    profileModal.classList.remove('hidden');
                    profileModal.classList.add('flex');
                });
            });

            const closeProfile = () => {
                profileModal.classList.add('hidden');
                profileModal.classList.remove('flex');
            };

            profileClose?.addEventListener('click', closeProfile);
            profileOverlay?.addEventListener('click', closeProfile);

            const deleteModal = document.getElementById('users-delete-modal');
            if (!deleteModal) return;

            document.querySelectorAll('[data-users-delete]').forEach((button) => {
                button.addEventListener('click', function () {
                    document.getElementById('users-delete-name').textContent = this.dataset.userName;
                    document.getElementById('users-delete-form').action = this.dataset.action;
                    deleteModal.classList.remove('hidden');
                });
            });

            document.getElementById('users-delete-cancel')?.addEventListener('click', () => deleteModal.classList.add('hidden'));
            document.getElementById('users-delete-overlay')?.addEventListener('click', () => deleteModal.classList.add('hidden'));
            document.getElementById('users-delete-confirm')?.addEventListener('click', () => document.getElementById('users-delete-form').submit());
        });
    </script>
</x-layouts.app>
