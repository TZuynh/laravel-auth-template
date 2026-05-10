<x-layouts.app :title="$pageTitle">
    @include('erp.partials.styles')

    <div class="space-y-8" data-proposals-page>
        <section class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex items-center gap-4">
                <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 15l2 2 4-5"/>
                    </svg>
                </span>
                <h2 class="text-4xl font-black tracking-tight text-slate-900">{{ $pageTitle }}</h2>
            </div>

            <div class="erp-card flex flex-wrap gap-2 p-2">
                <button type="button" class="erp-btn {{ $activeGroup === 'approval' ? 'erp-btn-outline' : 'erp-btn-blue' }}" data-proposal-tab="mine">{{ __('messages.erp.ui.my_proposals') }}</button>
                <button type="button" class="erp-btn {{ $activeGroup === 'approval' ? 'erp-btn-blue' : 'erp-btn-outline' }}" data-proposal-tab="queue">{{ __('messages.erp.ui.approval_processing') }}</button>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[470px_1fr]">
            <article class="erp-card p-7">
                <div class="flex items-center gap-3">
                    <span class="text-4xl font-light text-emerald-500">+</span>
                    <h3 class="text-2xl font-black text-slate-900">{{ __('messages.erp.ui.create_proposal') }}</h3>
                </div>
                <form class="mt-6 space-y-5" data-proposal-form>
                    <div>
                        <label class="mb-2 block text-sm font-black text-slate-600">{{ __('messages.erp.ui.proposal_type') }}</label>
                        <select class="erp-input" name="type">
                            @foreach ($proposalTypes as $type)
                                <option>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-600">{{ __('messages.erp.ui.from_date') }}</label>
                            <input class="erp-input" name="from" type="date" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-600">{{ __('messages.erp.ui.to_date') }}</label>
                            <input class="erp-input" name="to" type="date" required>
                        </div>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-black text-slate-600">{{ __('messages.erp.ui.proposal_reason') }}</label>
                        <textarea class="erp-input min-h-32 py-4" name="reason" placeholder="{{ __('messages.erp.ui.proposal_reason_placeholder') }}" required></textarea>
                    </div>
                    <button class="erp-btn erp-btn-blue w-full" type="submit">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m22 2-7 20-4-9-9-4z"/></svg>
                        {{ __('messages.erp.ui.submit_proposal') }}
                    </button>
                </form>
            </article>

            <article class="erp-card min-h-[520px] overflow-hidden" data-tab-panel="mine">
                <div class="border-b border-slate-100 p-7">
                    <h3 class="text-2xl font-black text-slate-900">{{ __('messages.erp.ui.proposal_progress') }}</h3>
                    <p class="mt-2 text-sm font-semibold text-slate-500">{{ __('messages.erp.ui.proposal_progress_desc') }}</p>
                </div>
                <div class="p-7">
                    <div class="space-y-4" id="proposal-list">
                        @foreach ($myProposals as $proposal)
                            <div class="erp-soft p-5">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                    <div>
                                        <p class="text-sm font-black uppercase tracking-[0.16em] text-blue-600">{{ $proposal['code'] }}</p>
                                        <h4 class="mt-1 text-lg font-black text-slate-900">{{ $proposal['type'] }}</h4>
                                        <p class="mt-1 text-sm font-semibold text-slate-500">{{ $proposal['date_range'] }}</p>
                                    </div>
                                    <span class="rounded-xl bg-amber-50 px-3 py-2 text-xs font-black text-amber-600">{{ $proposal['status'] }}</span>
                                </div>
                                <div class="mt-4 h-3 overflow-hidden rounded-full bg-white">
                                    <div class="h-full rounded-full bg-gradient-to-r from-blue-600 to-emerald-500" style="width: {{ $proposal['progress'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>

            <article class="erp-card hidden min-h-[520px] overflow-hidden xl:col-start-2" data-tab-panel="queue">
                <div class="border-b border-slate-100 p-7">
                    <h3 class="text-2xl font-black text-slate-900">{{ __('messages.erp.ui.approval_queue_title') }}</h3>
                    <p class="mt-2 text-sm font-semibold text-slate-500">{{ __('messages.erp.ui.approval_queue_desc') }}</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="erp-table min-w-[760px]">
                        <thead>
                            <tr>
                                <th>{{ __('messages.erp.ui.employee') }}</th>
                                <th>{{ __('messages.erp.ui.proposal_type') }}</th>
                                <th>{{ __('messages.erp.ui.time') }}</th>
                                <th>{{ __('messages.erp.ui.status') }}</th>
                                <th>{{ __('messages.erp.ui.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($approvalQueue as $row)
                                <tr>
                                    <td>{{ $row['employee'] }}</td>
                                    <td>{{ $row['type'] }}</td>
                                    <td>{{ $row['date_range'] }}</td>
                                    <td><span class="rounded-xl bg-rose-50 px-3 py-1 text-xs font-black text-rose-600">{{ $row['status'] }}</span></td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button type="button" class="rounded-xl bg-emerald-50 px-3 py-2 text-xs font-black text-emerald-600" data-resolve-row>{{ __('messages.erp.ui.approve') }}</button>
                                            <button type="button" class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-black text-rose-600" data-resolve-row>{{ __('messages.erp.ui.reject') }}</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        </section>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-proposals-page]');
            if (!root) return;
            const panels = root.querySelectorAll('[data-tab-panel]');
            const buttons = root.querySelectorAll('[data-proposal-tab]');
            const labels = {{ \Illuminate\Support\Js::from([
                'justSent' => __('messages.erp.ui.just_sent'),
                'handled' => __('messages.erp.ui.handled'),
                'finished' => __('messages.erp.ui.finished'),
            ]) }};
            const show = (name) => {
                panels.forEach((panel) => panel.classList.toggle('hidden', panel.dataset.tabPanel !== name));
                buttons.forEach((button) => {
                    const active = button.dataset.proposalTab === name;
                    button.classList.toggle('erp-btn-blue', active);
                    button.classList.toggle('erp-btn-outline', !active);
                });
            };

            buttons.forEach((button) => button.addEventListener('click', () => show(button.dataset.proposalTab)));
            show('{{ $activeGroup === 'approval' ? 'queue' : 'mine' }}');

            root.querySelector('[data-proposal-form]')?.addEventListener('submit', (event) => {
                event.preventDefault();
                const form = event.currentTarget;
                const data = new FormData(form);
                const type = data.get('type');
                const from = data.get('from');
                const to = data.get('to');
                const code = `DX-${new Date().getTime().toString().slice(-6)}`;
                document.getElementById('proposal-list')?.insertAdjacentHTML('afterbegin', `
                    <div class="erp-soft border-blue-200 bg-blue-50/70 p-5">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="text-sm font-black uppercase tracking-[0.16em] text-blue-600">${code}</p>
                                <h4 class="mt-1 text-lg font-black text-slate-900">${type}</h4>
                                <p class="mt-1 text-sm font-semibold text-slate-500">${from} - ${to}</p>
                            </div>
                            <span class="rounded-xl bg-blue-100 px-3 py-2 text-xs font-black text-blue-600">${labels.justSent}</span>
                        </div>
                        <div class="mt-4 h-3 overflow-hidden rounded-full bg-white">
                            <div class="h-full w-[20%] rounded-full bg-gradient-to-r from-blue-600 to-emerald-500"></div>
                        </div>
                    </div>
                `);
                form.reset();
                show('mine');
            });

            root.querySelectorAll('[data-resolve-row]').forEach((button) => {
                button.addEventListener('click', () => {
                    const row = button.closest('tr');
                    if (!row) return;
                    row.style.opacity = '0.45';
                    row.querySelector('td:nth-child(4)').innerHTML = `<span class="rounded-xl bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-600">${labels.handled}</span>`;
                    row.querySelector('td:nth-child(5)').innerHTML = `<span class="text-xs font-black text-slate-400">${labels.finished}</span>`;
                });
            });
        })();
    </script>
</x-layouts.app>
