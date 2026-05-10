<x-layouts.app :title="__('messages.erp.sidebar.recruitment')">
    @include('erp.partials.styles')

    @php
        $tones = [
            'blue' => 'border-blue-200 bg-blue-50 text-blue-700',
            'amber' => 'border-amber-200 bg-amber-50 text-amber-700',
            'purple' => 'border-purple-200 bg-purple-50 text-purple-700',
            'rose' => 'border-rose-200 bg-rose-50 text-rose-700',
            'green' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        ];
    @endphp

    <div class="space-y-8" data-ats-page>
        <section class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex items-center gap-4">
                <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-950 text-amber-400 shadow-xl">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M15 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8" cy="7" r="4"/><path d="M20 8v6M23 11h-6"/>
                    </svg>
                </span>
                <h2 class="text-3xl font-black tracking-tight text-slate-900 dark:text-slate-100">{{ __('messages.erp.sidebar.recruitment') }}</h2>
            </div>
            <button type="button" class="erp-btn erp-btn-dark" data-add-candidate>{{ __('messages.erp.ui.add_cv') }}</button>
        </section>

        <section class="overflow-x-auto pb-4">
            <div class="grid min-w-[1320px] grid-cols-5 gap-6">
                @foreach ($atsColumns as $column)
                    @php($count = collect($candidates)->where('stage', $column['key'])->count())
                    <article class="min-h-[560px] rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-900/70" data-ats-column="{{ $column['key'] }}">
                        <header class="flex items-center justify-between rounded-2xl border px-5 py-3 text-sm font-black uppercase tracking-[0.16em] {{ $tones[$column['tone']] }}">
                            <span>{{ $column['title'] }}</span>
                            <span class="rounded-xl bg-white/80 px-3 py-1" data-column-count>{{ $count }}</span>
                        </header>
                        <div class="mt-5 space-y-4" data-card-list>
                            @foreach (collect($candidates)->where('stage', $column['key']) as $candidate)
                                <div class="cursor-grab rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950" draggable="true" data-candidate-card>
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h3 class="text-lg font-black text-slate-900 dark:text-slate-100">{{ $candidate['name'] }}</h3>
                                            <p class="mt-1 text-sm font-semibold text-slate-500">{{ $candidate['position'] }}</p>
                                        </div>
                                        <span class="rounded-xl bg-blue-50 px-3 py-1 text-xs font-black text-blue-600">{{ $candidate['score'] }}</span>
                                    </div>
                                    <p class="mt-4 text-sm font-bold text-slate-500">{{ $candidate['phone'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <div class="erp-modal" data-candidate-modal aria-hidden="true">
            <div class="erp-modal-panel max-w-lg p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 dark:text-slate-100">{{ __('messages.erp.ui.add_cv') }}</h3>
                        <p class="mt-1 text-sm font-semibold text-slate-500">{{ __('messages.erp.ui.add_cv_desc') }}</p>
                    </div>
                    <button type="button" class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" data-candidate-close>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="mt-5 space-y-4">
                    <input class="erp-input" data-candidate-name placeholder="{{ __('messages.erp.ui.applicant_name') }}">
                    <input class="erp-input" data-candidate-position placeholder="{{ __('messages.erp.ui.position') }}">
                    <input class="erp-input" data-candidate-phone placeholder="{{ __('messages.erp.ui.phone') }}">
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="erp-btn erp-btn-outline" data-candidate-close>{{ __('messages.erp.ui.cancel') }}</button>
                    <button type="button" class="erp-btn erp-btn-dark" data-candidate-save>{{ __('messages.erp.ui.add_new') }}</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-ats-page]');
            if (!root) return;
            let dragged = null;
            const modal = root.querySelector('[data-candidate-modal]');
            const nameInput = root.querySelector('[data-candidate-name]');
            const positionInput = root.querySelector('[data-candidate-position]');
            const phoneInput = root.querySelector('[data-candidate-phone]');
            const labels = {{ \Illuminate\Support\Js::from([
                'newPosition' => __('messages.erp.ui.new_position'),
                'phoneMissing' => __('messages.erp.ui.phone_missing'),
            ]) }};
            const openModal = () => {
                modal?.classList.add('is-open');
                modal?.setAttribute('aria-hidden', 'false');
                nameInput?.focus();
            };
            const closeModal = () => {
                modal?.classList.remove('is-open');
                modal?.setAttribute('aria-hidden', 'true');
                [nameInput, positionInput, phoneInput].forEach((input) => { if (input) input.value = ''; });
            };
            const refreshCounts = () => {
                root.querySelectorAll('[data-ats-column]').forEach((column) => {
                    column.querySelector('[data-column-count]').textContent = column.querySelectorAll('[data-candidate-card]').length;
                });
            };
            const wireCard = (card) => {
                card.addEventListener('dragstart', () => {
                    dragged = card;
                    card.classList.add('opacity-50');
                });
                card.addEventListener('dragend', () => {
                    card.classList.remove('opacity-50');
                    dragged = null;
                    refreshCounts();
                });
            };
            root.querySelectorAll('[data-candidate-card]').forEach(wireCard);
            root.querySelectorAll('[data-ats-column]').forEach((column) => {
                column.addEventListener('dragover', (event) => event.preventDefault());
                column.addEventListener('drop', () => {
                    if (!dragged) return;
                    column.querySelector('[data-card-list]')?.appendChild(dragged);
                    refreshCounts();
                });
            });
            root.querySelector('[data-add-candidate]')?.addEventListener('click', () => {
                openModal();
            });
            root.querySelectorAll('[data-candidate-close]').forEach((button) => button.addEventListener('click', closeModal));
            root.querySelector('[data-candidate-save]')?.addEventListener('click', () => {
                const name = nameInput?.value.trim();
                if (!name) return;
                const position = positionInput?.value.trim() || labels.newPosition;
                const phone = phoneInput?.value.trim() || labels.phoneMissing;
                const firstList = root.querySelector('[data-ats-column="new"] [data-card-list]');
                const card = document.createElement('div');
                card.className = 'cursor-grab rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950';
                card.setAttribute('draggable', 'true');
                card.setAttribute('data-candidate-card', '');
                card.innerHTML = `<div class="flex items-start justify-between gap-3"><div><h3 class="text-lg font-black text-slate-900 dark:text-slate-100">${name}</h3><p class="mt-1 text-sm font-semibold text-slate-500">${position}</p></div><span class="rounded-xl bg-blue-50 px-3 py-1 text-xs font-black text-blue-600">70</span></div><p class="mt-4 text-sm font-bold text-slate-500">${phone}</p>`;
                wireCard(card);
                firstList?.prepend(card);
                refreshCounts();
                closeModal();
            });
        })();
    </script>
</x-layouts.app>
