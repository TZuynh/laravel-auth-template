<x-layouts.app :title="__('messages.users.page_title')">
    @include('erp.partials.styles')

    @php
        $isAdmin = in_array(strtolower(trim((string) (auth()->user()->role ?? ''))), ['administrator', 'admin'], true);
        $stats = [
            ['label' => __('messages.users.total_users'), 'value' => $userStats['total'] ?? 0, 'tone' => 'blue', 'icon' => 'M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM21 8v6M18 11h6'],
            ['label' => __('messages.users.active'), 'value' => $userStats['active'] ?? 0, 'tone' => 'emerald', 'icon' => 'm5 13 4 4L19 7'],
            ['label' => __('messages.users.locked'), 'value' => $userStats['locked'] ?? 0, 'tone' => 'red', 'icon' => 'M7 11V8a5 5 0 0 1 10 0v3M6 11h12v10H6z'],
            ['label' => __('messages.users.role_administrator'), 'value' => $userStats['admin'] ?? 0, 'tone' => 'purple', 'icon' => 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z'],
            ['label' => __('messages.users.role_staff'), 'value' => $userStats['staff'] ?? 0, 'tone' => 'orange', 'icon' => 'M16 21v-2a4 4 0 0 0-8 0v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM19 8v6M16 11h6'],
            ['label' => __('messages.users.role_customer'), 'value' => $userStats['customer'] ?? 0, 'tone' => 'slate', 'icon' => 'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z'],
        ];
        $toneClasses = [
            'blue' => 'from-blue-500 to-blue-700',
            'emerald' => 'from-emerald-500 to-green-700',
            'red' => 'from-red-500 to-rose-700',
            'purple' => 'from-purple-500 to-violet-700',
            'orange' => 'from-orange-500 to-orange-700',
            'slate' => 'from-slate-500 to-slate-700',
        ];
        $roleLabels = [
            'administrator' => __('messages.users.role_administrator'),
            'admin' => __('messages.users.role_administrator'),
            'manager' => __('messages.users.role_manager'),
            'staff' => __('messages.users.role_staff'),
            'customer' => __('messages.users.role_customer'),
        ];
    @endphp

    <div class="space-y-7" data-users-page>
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            @foreach ($stats as $stat)
                <article class="min-h-32 rounded-2xl bg-gradient-to-br {{ $toneClasses[$stat['tone']] }} p-5 text-white shadow-lg shadow-slate-200/60">
                    <svg class="h-7 w-7 opacity-90" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="{{ $stat['icon'] }}"/>
                    </svg>
                    <p class="mt-5 text-3xl font-black">{{ number_format((int) $stat['value']) }}</p>
                    <p class="mt-1 text-sm font-semibold text-white/85">{{ $stat['label'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/></svg>
                    </span>
                    <h2 class="text-3xl font-black tracking-tight text-slate-900">{{ __('messages.users.management_title') }}</h2>
                </div>
                <p class="mt-2 text-sm font-semibold text-slate-500">
                    {{ __('messages.users.showing_count', ['shown' => $users->count(), 'total' => $users->total()]) }}
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="button" class="erp-btn erp-btn-green" data-export-table="#users-table" data-filename="users.csv">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14"/></svg>
                    {{ __('messages.users.export_excel') }}
                </button>
                <button type="button" class="erp-btn erp-btn-dark" data-print-section="#users-table-card">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6z"/></svg>
                    {{ __('messages.users.print') }}
                </button>
                @if ($isAdmin)
                    <a href="{{ route('users.create') }}" class="erp-btn erp-btn-blue">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                        {{ __('messages.users.add') }}
                    </a>
                @endif
            </div>
        </section>

        <form method="GET" action="{{ route('users.index') }}" class="erp-card grid gap-4 p-5 xl:grid-cols-[1fr_190px_210px]">
            <div class="relative">
                <svg class="pointer-events-none absolute left-5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m21 21-5-5M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16z"/></svg>
                <input class="erp-input" style="padding-left: 3.35rem;" name="q" value="{{ $q }}" placeholder="{{ __('messages.users.search_placeholder_full') }}">
            </div>
            <select class="erp-input" name="role" onchange="this.form.submit()">
                <option value="all" @selected($role === 'all')>{{ __('messages.users.all_roles') }}</option>
                <option value="admin" @selected($role === 'admin')>{{ __('messages.users.role_administrator') }}</option>
                <option value="manager" @selected($role === 'manager')>{{ __('messages.users.role_manager') }}</option>
                <option value="staff" @selected($role === 'staff')>{{ __('messages.users.role_staff') }}</option>
                <option value="customer" @selected($role === 'customer')>{{ __('messages.users.role_customer') }}</option>
            </select>
            <select class="erp-input" name="status" onchange="this.form.submit()">
                <option value="all" @selected($status === 'all')>{{ __('messages.users.all_statuses') }}</option>
                <option value="active" @selected($status === 'active')>{{ __('messages.users.active') }}</option>
                <option value="locked" @selected($status === 'locked')>{{ __('messages.users.locked') }}</option>
            </select>
        </form>

        <section class="erp-card overflow-hidden" id="users-table-card">
            <div class="overflow-x-auto">
                <table class="erp-table min-w-[1100px]" id="users-table">
                    <thead>
                        <tr>
                            <th class="w-12"><input type="checkbox" class="h-4 w-4 rounded border-slate-300" data-users-select-all aria-label="{{ __('messages.users.select_all') }}"></th>
                            <th>{{ __('messages.users.table_user') }}</th>
                            <th>{{ __('messages.users.contact') }}</th>
                            <th>{{ __('messages.users.role') }}</th>
                            <th>{{ __('messages.users.joined_at') }}</th>
                            <th>{{ __('messages.users.status') }}</th>
                            <th class="erp-actions">{{ __('messages.users.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            @php
                                $normalizedRole = strtolower(trim((string) ($user->role ?? 'staff')));
                                $isLocked = $user->email_verified_at === null;
                            @endphp
                            <tr>
                                <td><input type="checkbox" class="h-4 w-4 rounded border-slate-300" data-users-select-row aria-label="{{ __('messages.users.select_row') }}"></td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-11 w-11 rounded-xl object-cover shadow-sm">
                                        <div>
                                            <p class="font-black text-slate-900">{{ $user->name }}</p>
                                            <p class="mt-1 text-xs font-semibold text-slate-400">#{{ $user->id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="font-semibold text-slate-700">{{ $user->email }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-400">{{ __('messages.users.no_phone') }}</p>
                                </td>
                                <td>
                                    <span class="rounded-xl bg-blue-50 px-3 py-1 text-xs font-black uppercase tracking-wider text-blue-600">
                                        {{ $roleLabels[$normalizedRole] ?? ucfirst($normalizedRole) }}
                                    </span>
                                </td>
                                <td>{{ optional($user->created_at)->format('d/m/Y') }}</td>
                                <td>
                                    <span class="rounded-xl px-3 py-1 text-xs font-black {{ $isLocked ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }}">
                                        {{ $isLocked ? __('messages.users.locked') : __('messages.users.active') }}
                                    </span>
                                </td>
                                <td class="erp-actions">
                                    <div class="flex gap-2">
                                        @if ($isAdmin)
                                            <a href="{{ route('users.edit', $user) }}" class="rounded-xl bg-blue-50 px-3 py-2 text-xs font-black text-blue-600">{{ __('messages.users.edit') }}</a>
                                            <button type="button" data-users-delete data-action="{{ route('users.destroy', $user) }}" data-user-name="{{ $user->name }}" class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-black text-rose-600">{{ __('messages.users.delete') }}</button>
                                        @else
                                            <span class="text-xs font-semibold text-slate-400">-</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-20 text-center">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full border-4 border-slate-200 text-slate-300">!</div>
                                    <p class="mt-4 text-base font-semibold text-slate-400">{{ __('messages.users.no_users') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">
                    {{ $users->links() }}
                </div>
            @endif
        </section>
    </div>

    @if ($isAdmin)
        <div id="users-delete-modal" class="fixed inset-0 z-[120] hidden">
            <div id="users-delete-overlay" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl">
                    <p class="text-base font-black text-slate-900">{{ __('messages.users.delete_title') }}</p>
                    <p class="mt-2 text-sm font-semibold text-slate-500">{{ __('messages.users.delete_desc') }} <span id="users-delete-name" class="text-slate-900"></span></p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button id="users-delete-cancel" type="button" class="erp-btn erp-btn-outline">{{ __('messages.users.cancel') }}</button>
                        <button id="users-delete-confirm" type="button" class="erp-btn bg-rose-600 text-white">{{ __('messages.users.delete_confirm') }}</button>
                    </div>
                    <form id="users-delete-form" method="POST" class="hidden">@csrf @method('DELETE')</form>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const root = document.querySelector('[data-users-page]');
            const selectAll = root?.querySelector('[data-users-select-all]');
            const rows = Array.from(root?.querySelectorAll('[data-users-select-row]') || []);
            const syncSelectAll = () => {
                if (!selectAll) return;
                const checkedCount = rows.filter((checkbox) => checkbox.checked).length;
                selectAll.checked = rows.length > 0 && checkedCount === rows.length;
                selectAll.indeterminate = checkedCount > 0 && checkedCount < rows.length;
            };

            selectAll?.addEventListener('change', () => {
                rows.forEach((checkbox) => {
                    checkbox.checked = selectAll.checked;
                });
                syncSelectAll();
            });

            rows.forEach((checkbox) => checkbox.addEventListener('change', syncSelectAll));
            syncSelectAll();
        });
    </script>
</x-layouts.app>
