<x-layouts.app :title="__('messages.erp.sidebar.categories')">
    @include('erp.partials.styles')

    <div class="space-y-8" data-categories-page>
        <section class="erp-card flex flex-col gap-5 p-7 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex items-center gap-5">
                <span class="inline-flex h-16 w-16 items-center justify-center rounded-3xl bg-orange-500 text-white shadow-xl shadow-orange-100">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 7h7l2 2h9v9a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3z"/></svg>
                </span>
                <div>
                    <h2 class="text-3xl font-black text-slate-900 dark:text-slate-100">{{ __('messages.erp.sidebar.categories') }}</h2>
                    <p class="mt-2 text-base font-semibold text-slate-500 dark:text-slate-400">{{ __('messages.erp.ui.categories_desc') }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="button" class="erp-btn erp-btn-outline" data-print-section="#category-print">{{ __('messages.erp.ui.print') }}</button>
                <button type="button" class="erp-btn erp-btn-dark" data-add-category>{{ __('messages.erp.ui.add_new') }}</button>
            </div>
        </section>

        <section class="erp-card grid gap-4 p-5 md:grid-cols-[1fr_auto]">
            <input class="erp-input" data-category-search placeholder="{{ __('messages.erp.ui.search') }}">
            <div class="flex rounded-2xl bg-slate-100 p-1">
                <button type="button" class="rounded-xl bg-white px-4 py-3 text-slate-500 shadow-sm" title="{{ __('messages.erp.ui.grid') }}">▦</button>
                <button type="button" class="rounded-xl px-4 py-3 text-orange-600" title="{{ __('messages.erp.ui.list') }}">☷</button>
            </div>
        </section>

        <section class="erp-card overflow-hidden" id="category-print">
            <div class="overflow-x-auto">
                <table class="erp-table min-w-[980px]">
                    <thead>
                        <tr>
                            <th>{{ __('messages.erp.ui.category_name') }}</th>
                            <th>{{ __('messages.erp.ui.description') }}</th>
                            <th>{{ __('messages.erp.ui.products') }}</th>
                            <th class="erp-actions">{{ __('messages.erp.ui.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="category-body">
                        @foreach ($categories as $category)
                            <tr data-category-row>
                                <td>
                                    <div class="font-black text-slate-900 dark:text-slate-100" data-category-name-cell>{{ $category['name'] }}</div>
                                    <div class="mt-1 text-xs text-slate-400" data-category-slug-cell>/{{ $category['slug'] }}</div>
                                </td>
                                <td data-category-description-cell>{{ $category['description'] }}</td>
                                <td><span class="rounded-xl bg-orange-50 px-4 py-2 text-xs font-black text-orange-600">{{ $category['products'] }}</span></td>
                                <td class="erp-actions">
                                    <div class="flex gap-2">
                                        <button type="button" class="rounded-xl bg-blue-50 px-3 py-2 text-xs font-black text-blue-600" data-edit-category>{{ __('messages.erp.ui.edit') }}</button>
                                        <button type="button" class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-black text-rose-600" data-delete-row>{{ __('messages.erp.ui.delete') }}</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <div class="erp-modal" data-category-modal aria-hidden="true">
            <div class="erp-modal-panel max-w-lg p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 dark:text-slate-100" data-category-modal-title>{{ __('messages.erp.ui.add_new') }}</h3>
                        <p class="mt-1 text-sm font-semibold text-slate-500">{{ __('messages.erp.ui.category_modal_desc') }}</p>
                    </div>
                    <button type="button" class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" data-category-close>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="mt-5 space-y-4">
                    <input type="hidden" data-category-editing>
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">{{ __('messages.erp.ui.category_name') }}</label>
                        <input class="erp-input" data-category-name placeholder="{{ __('messages.erp.ui.category_name') }}">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">{{ __('messages.erp.ui.description') }}</label>
                        <input class="erp-input" data-category-description placeholder="{{ __('messages.erp.ui.description') }}">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="erp-btn erp-btn-outline" data-category-close>{{ __('messages.erp.ui.cancel') }}</button>
                    <button type="button" class="erp-btn erp-btn-dark" data-category-save>{{ __('messages.erp.ui.save') }}</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-categories-page]');
            if (!root) return;
            const body = document.getElementById('category-body');
            const modal = root.querySelector('[data-category-modal]');
            const nameInput = root.querySelector('[data-category-name]');
            const descriptionInput = root.querySelector('[data-category-description]');
            const editingInput = root.querySelector('[data-category-editing]');
            const title = root.querySelector('[data-category-modal-title]');
            const labels = {{ \Illuminate\Support\Js::from([
                'add' => __('messages.erp.ui.add_new'),
                'edit' => __('messages.erp.ui.edit_category'),
                'fallbackDescription' => __('messages.erp.ui.updated_description'),
                'editButton' => __('messages.erp.ui.edit'),
                'deleteButton' => __('messages.erp.ui.delete'),
            ]) }};
            const slugify = (value) => value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
            const openModal = (row = null) => {
                if (editingInput) editingInput.value = row ? String([...body.children].indexOf(row)) : '';
                if (title) title.textContent = row ? labels.edit : labels.add;
                if (nameInput) nameInput.value = row?.querySelector('[data-category-name-cell]')?.textContent.trim() || '';
                if (descriptionInput) descriptionInput.value = row?.querySelector('[data-category-description-cell]')?.textContent.trim() || '';
                modal?.classList.add('is-open');
                modal?.setAttribute('aria-hidden', 'false');
                nameInput?.focus();
            };
            const closeModal = () => {
                modal?.classList.remove('is-open');
                modal?.setAttribute('aria-hidden', 'true');
            };
            root.querySelector('[data-category-search]')?.addEventListener('input', (event) => {
                const q = event.target.value.toLowerCase();
                root.querySelectorAll('[data-category-row]').forEach((row) => row.classList.toggle('hidden', !row.textContent.toLowerCase().includes(q)));
            });
            root.querySelectorAll('[data-category-close]').forEach((button) => button.addEventListener('click', closeModal));
            root.querySelector('[data-category-save]')?.addEventListener('click', () => {
                const name = nameInput?.value.trim();
                if (!name) return;
                const description = descriptionInput?.value.trim() || labels.fallbackDescription;
                const slug = slugify(name);
                const index = editingInput?.value;
                const row = index !== '' ? body.children[Number(index)] : null;
                if (row) {
                    row.querySelector('[data-category-name-cell]').textContent = name;
                    row.querySelector('[data-category-slug-cell]').textContent = `/${slug}`;
                    row.querySelector('[data-category-description-cell]').textContent = description;
                } else {
                    body?.insertAdjacentHTML('afterbegin', `<tr data-category-row><td><div class="font-black text-slate-900 dark:text-slate-100" data-category-name-cell>${name}</div><div class="mt-1 text-xs text-slate-400" data-category-slug-cell>/${slug}</div></td><td data-category-description-cell>${description}</td><td><span class="rounded-xl bg-orange-50 px-4 py-2 text-xs font-black text-orange-600">0</span></td><td class="erp-actions"><div class="flex gap-2"><button type="button" class="rounded-xl bg-blue-50 px-3 py-2 text-xs font-black text-blue-600" data-edit-category>${labels.editButton}</button><button type="button" class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-black text-rose-600" data-delete-row>${labels.deleteButton}</button></div></td></tr>`);
                }
                closeModal();
            });
            root.addEventListener('click', (event) => {
                if (event.target.closest('[data-delete-row]')) {
                    event.target.closest('tr')?.remove();
                    return;
                }
                if (event.target.closest('[data-add-category]')) {
                    openModal();
                    return;
                }
                if (event.target.closest('[data-edit-category]')) {
                    const row = event.target.closest('tr');
                    if (row) openModal(row);
                }
            });
        })();
    </script>
</x-layouts.app>
