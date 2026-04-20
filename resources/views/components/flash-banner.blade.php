@php
    $message = session('success') ?? session('error');
    $type = session('success') ? 'success' : (session('error') ? 'error' : null);
    $firstError = $errors->first();
    if (!$message && $firstError) {
        $message = $firstError;
        $type = 'error';
    }
@endphp

@if ($message)
    <div id="flash-banner" class="mb-6 rounded-2xl border px-4 py-3 text-sm font-semibold shadow-sm {{ $type === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300' : 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300' }}">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ $type === 'success' ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white' }}">
                    @if($type === 'success')
                        ✓
                    @else
                        !
                    @endif
                </span>
                <span>{{ $message }}</span>
            </div>
            <button type="button" data-flash-close class="text-current/60 hover:text-current">×</button>
        </div>
    </div>
@endif
