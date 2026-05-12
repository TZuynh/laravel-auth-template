@php
    $message = session('success') ?? session('warning') ?? session('error');
    $type = session('success') ? 'success' : (session('warning') ? 'warning' : (session('error') ? 'error' : null));
    $firstError = $errors->first();

    if (!$message && $firstError) {
        $message = $firstError;
        $type = 'error';
    }

    $toastTheme = match ($type) {
        'error' => 'border-rose-200 bg-white text-rose-700 shadow-rose-200/60 dark:border-rose-500/30 dark:bg-slate-950 dark:text-rose-300',
        'warning' => 'border-amber-200 bg-white text-amber-700 shadow-amber-200/60 dark:border-amber-500/30 dark:bg-slate-950 dark:text-amber-300',
        default => 'border-emerald-200 bg-white text-emerald-700 shadow-emerald-200/60 dark:border-emerald-500/30 dark:bg-slate-950 dark:text-emerald-300',
    };

    $iconTheme = match ($type) {
        'error' => 'bg-rose-500 text-white',
        'warning' => 'bg-amber-500 text-white',
        default => 'bg-emerald-500 text-white',
    };
@endphp

@if ($message)
    <div id="flash-banner"
         data-flash-type="{{ $type }}"
         data-flash-message="{{ $message }}"
         class="fixed right-5 top-24 z-[80] w-[min(calc(100vw-2rem),420px)] translate-y-0 rounded-2xl border px-4 py-3 text-sm font-semibold opacity-100 shadow-2xl transition duration-300 {{ $toastTheme }}">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-black {{ $iconTheme }}">
                    @if ($type === 'error')
                        !
                    @elseif ($type === 'warning')
                        i
                    @else
                        ✓
                    @endif
                </span>
                <span class="leading-6">{{ $message }}</span>
            </div>
            <button type="button" data-flash-close class="rounded-lg px-2 py-1 text-current/60 transition hover:bg-slate-100 hover:text-current dark:hover:bg-white/10">x</button>
        </div>
    </div>
@endif

<div id="app-confirm-modal" class="fixed inset-0 z-[90] hidden items-center justify-center bg-slate-950/50 px-4 backdrop-blur-sm">
    <div class="w-full max-w-md scale-95 rounded-[28px] border border-slate-200 bg-white p-6 opacity-0 shadow-2xl shadow-slate-900/20 transition duration-200 dark:border-slate-700 dark:bg-slate-950" data-confirm-card>
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-50 text-xl font-black text-rose-600 dark:bg-rose-500/10 dark:text-rose-300">!</div>
            <div>
                <h2 class="text-lg font-black text-slate-900 dark:text-white">Xác nhận thao tác</h2>
                <p id="app-confirm-message" class="mt-2 text-sm font-semibold leading-6 text-slate-500 dark:text-slate-300">Bạn có chắc chắn muốn tiếp tục?</p>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" data-confirm-cancel class="rounded-2xl bg-slate-100 px-5 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Hủy</button>
            <button type="button" data-confirm-ok class="rounded-2xl bg-rose-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-rose-200 transition hover:bg-rose-700">Xóa</button>
        </div>
    </div>
</div>

<script>
    (() => {
        const toast = document.getElementById('flash-banner');
        const closeToast = () => {
            if (!toast) return;
            toast.classList.add('translate-y-2', 'opacity-0');
            window.setTimeout(() => toast.remove(), 260);
        };

        toast?.querySelector('[data-flash-close]')?.addEventListener('click', closeToast);
        if (toast) window.setTimeout(closeToast, 4200);

        const modal = document.getElementById('app-confirm-modal');
        if (!modal || modal.dataset.ready === '1') return;
        modal.dataset.ready = '1';

        const card = modal.querySelector('[data-confirm-card]');
        const message = document.getElementById('app-confirm-message');
        const cancel = modal.querySelector('[data-confirm-cancel]');
        const ok = modal.querySelector('[data-confirm-ok]');
        let pendingForm = null;

        const open = (form) => {
            pendingForm = form;
            if (message) message.textContent = form.dataset.confirm || 'Bạn có chắc chắn muốn tiếp tục?';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            requestAnimationFrame(() => {
                card?.classList.remove('scale-95', 'opacity-0');
                card?.classList.add('scale-100', 'opacity-100');
            });
        };

        const close = () => {
            card?.classList.add('scale-95', 'opacity-0');
            card?.classList.remove('scale-100', 'opacity-100');
            window.setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                pendingForm = null;
            }, 160);
        };

        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement) || !form.dataset.confirm || form.dataset.confirmed === '1') {
                return;
            }

            event.preventDefault();
            open(form);
        });

        ok?.addEventListener('click', () => {
            if (!pendingForm) return;
            pendingForm.dataset.confirmed = '1';
            pendingForm.requestSubmit();
        });

        cancel?.addEventListener('click', close);
        modal.addEventListener('click', (event) => {
            if (event.target === modal) close();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) close();
        });
    })();
</script>
