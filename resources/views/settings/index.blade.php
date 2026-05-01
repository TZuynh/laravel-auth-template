<x-layouts.app :title="__('messages.settings.title')">
    @php($rate = $exchangeRateInfo['usd_to_vnd'] ?? null)

    <div class="space-y-6">
        <div>
            <h2 class="text-3xl font-black tracking-tight text-slate-900 dark:text-slate-100">{{ __('messages.settings.title') }}</h2>
            <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('messages.settings.description') }}</p>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <section class="rounded-[2rem] border border-slate-200 bg-white/90 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/80">
                <div class="flex flex-col gap-2">
                    <h3 class="text-xl font-black text-slate-900 dark:text-slate-100">{{ __('messages.settings.integration_title') }}</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('messages.settings.integration_description') }}</p>
                </div>

                <form action="{{ route('settings.integrations.update') }}" method="POST" class="mt-6 space-y-5">
                    @csrf

                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label for="gemini_model" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-300">{{ __('messages.settings.gemini_model') }}</label>
                            <input id="gemini_model" name="gemini_model" type="text" value="{{ old('gemini_model', $settingsValues['gemini_model'] ?? '') }}" class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100">
                            @error('gemini_model')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="gemini_api_key" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-300">{{ __('messages.settings.gemini_api_key') }}</label>
                            <textarea id="gemini_api_key" name="gemini_api_key" rows="3" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100">{{ old('gemini_api_key', $settingsValues['gemini_api_key'] ?? '') }}</textarea>
                            @error('gemini_api_key')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="product_export_usd_rate" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-300">{{ __('messages.settings.product_export_usd_rate') }}</label>
                            <div class="flex items-center gap-2">
                                <input id="product_export_usd_rate" name="product_export_usd_rate" type="number" step="0.01" min="0.01" value="{{ old('product_export_usd_rate', $settingsValues['product_export_usd_rate'] ?? '') }}" class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100">
                                @if ($rate)
                                    <button type="button" id="fill-current-rate" data-rate="{{ number_format((float) $rate, 2, '.', '') }}" class="inline-flex h-12 shrink-0 items-center rounded-2xl border border-sky-200 bg-sky-50 px-4 text-sm font-bold text-sky-700 transition-colors hover:bg-sky-100 dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-300">
                                        {{ __('messages.settings.use_live_rate') }}
                                    </button>
                                @endif
                            </div>
                            @error('product_export_usd_rate')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-300">
                            <p class="font-bold text-slate-900 dark:text-slate-100">{{ __('messages.settings.current_rate_title') }}</p>
                            @if ($rate)
                                <p class="mt-2">{{ __('messages.settings.current_rate_value', ['rate' => number_format((float) $rate, 2)]) }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('messages.settings.current_rate_updated', ['time' => $exchangeRateInfo['last_update_utc'] ?? '-']) }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('messages.settings.current_rate_next_update', ['time' => $exchangeRateInfo['next_update_utc'] ?? '-']) }}</p>
                                <a href="{{ $exchangeRateInfo['documentation'] ?? 'https://www.exchangerate-api.com/docs/free' }}" target="_blank" rel="noreferrer" class="mt-3 inline-flex text-xs font-semibold text-sky-600 hover:text-sky-700 dark:text-sky-300">
                                    {{ __('messages.settings.current_rate_source') }}
                                </a>
                            @else
                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ __('messages.settings.current_rate_unavailable') }}</p>
                            @endif
                        </div>
                    </div>

                    <button type="submit" class="inline-flex h-12 items-center rounded-2xl bg-indigo-600 px-5 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition-colors hover:bg-indigo-700">
                        {{ __('messages.settings.integration_save') }}
                    </button>
                </form>
            </section>

            <div class="space-y-6">
                <section class="rounded-[2rem] border border-slate-200 bg-white/90 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/80">
                    <h3 class="text-xl font-black text-slate-900 dark:text-slate-100">{{ __('messages.settings.cache_title') }}</h3>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ __('messages.settings.cache_description') }}</p>

                    <form action="{{ route('settings.cache-clear') }}" method="POST" class="mt-6">
                        @csrf
                        <button type="submit" class="inline-flex h-12 items-center rounded-2xl bg-indigo-600 px-5 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition-colors hover:bg-indigo-700">
                            {{ __('messages.settings.cache_button') }}
                        </button>
                    </form>
                </section>

                <section class="rounded-[2rem] border border-slate-200 bg-white/90 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/80">
                    <h3 class="text-xl font-black text-slate-900 dark:text-slate-100">{{ __('messages.settings.exchange_note_title') }}</h3>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ __('messages.settings.exchange_note_description') }}</p>
                    <div class="mt-5 rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-4 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-400">
                        {{ __('messages.settings.exchange_note_list') }}
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const fillButton = document.getElementById('fill-current-rate');
            const rateInput = document.getElementById('product_export_usd_rate');

            fillButton?.addEventListener('click', () => {
                if (!rateInput) {
                    return;
                }

                rateInput.value = fillButton.dataset.rate || rateInput.value;
                rateInput.focus();
            });
        });
    </script>
</x-layouts.app>
