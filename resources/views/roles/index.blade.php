<x-layouts.app :title="__('messages.roles.page_title')">
    @include('erp.partials.styles')

    @php
        $roleHeaderClasses = [
            'purple' => 'bg-purple-900',
            'indigo' => 'bg-indigo-900',
            'blue' => 'bg-blue-900',
            'slate' => 'bg-orange-950',
        ];
    @endphp

    <div class="space-y-7" data-roles-page>
        <section class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex items-center gap-4">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </span>
                <div>
                    <h2 class="text-3xl font-black tracking-tight text-slate-900">{{ __('messages.roles.title') }}</h2>
                    <p class="mt-2 text-sm font-semibold text-slate-500">{{ __('messages.roles.description') }}</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="button" class="erp-btn erp-btn-outline" data-refresh-roles>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 0 1-15.3 6.4M3 12A9 9 0 0 1 18.3 5.6M3 5v6h6M21 19v-6h-6"/></svg>
                </button>
                <button type="button" class="erp-btn bg-purple-600 text-white" data-save-roles>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2zM17 21v-8H7v8M7 3v5h8"/></svg>
                    {{ __('messages.roles.save_all') }}
                </button>
            </div>
        </section>

        <section class="erp-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1220px] border-collapse text-left">
                    <thead>
                        <tr>
                            <th class="bg-slate-950 px-7 py-5 text-xs font-black uppercase tracking-wider text-white">{{ __('messages.roles.module') }}</th>
                            @foreach ($matrix['roles'] as $role)
                                <th class="{{ $roleHeaderClasses[$role['tone']] ?? 'bg-slate-900' }} px-7 py-5 text-center text-xs font-black uppercase tracking-wider text-white">
                                    <div>{{ $role['label'] }}</div>
                                    <div class="mt-1 text-[10px] font-semibold text-white/60">{{ __('messages.roles.member_count', ['count' => $role['count']]) }}</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($matrix['modules'] as $module)
                            <tr>
                                <td class="bg-white px-7 py-5">
                                    <p class="font-black text-slate-800">{{ $module['label'] }}</p>
                                    <p class="mt-1 text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">{{ $module['key'] }}</p>
                                </td>
                                @foreach ($matrix['roles'] as $role)
                                    <td class="border-l border-slate-100 bg-white px-7 py-5 text-center">
                                        <div class="inline-flex flex-wrap justify-center gap-2">
                                            @foreach ($matrix['actions'] as $action => $label)
                                                @php($enabled = (bool) ($module['permissions'][$role['key']][$action] ?? false))
                                                <button
                                                    type="button"
                                                    class="inline-flex h-9 items-center gap-1 rounded-xl border px-3 text-xs font-black transition {{ $enabled ? 'border-blue-200 bg-blue-50 text-blue-600' : 'border-slate-200 bg-white text-slate-300' }}"
                                                    data-permission-toggle
                                                    aria-pressed="{{ $enabled ? 'true' : 'false' }}"
                                                    title="{{ $label }}"
                                                >
                                                    @if ($action === 'view')
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                                    @elseif ($action === 'edit')
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                                                    @else
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg>
                                                    @endif
                                                    <span>{{ $label }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const root = document.querySelector('[data-roles-page]');
            if (!root) return;

            root.querySelectorAll('[data-permission-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const next = button.getAttribute('aria-pressed') !== 'true';
                    button.setAttribute('aria-pressed', next ? 'true' : 'false');
                    button.classList.toggle('border-blue-200', next);
                    button.classList.toggle('bg-blue-50', next);
                    button.classList.toggle('text-blue-600', next);
                    button.classList.toggle('border-slate-200', !next);
                    button.classList.toggle('bg-white', !next);
                    button.classList.toggle('text-slate-300', !next);
                });
            });

            root.querySelector('[data-refresh-roles]')?.addEventListener('click', () => window.location.reload());
            root.querySelector('[data-save-roles]')?.addEventListener('click', (event) => {
                event.currentTarget.classList.add('opacity-70');
                window.setTimeout(() => event.currentTarget.classList.remove('opacity-70'), 600);
            });
        });
    </script>
</x-layouts.app>
