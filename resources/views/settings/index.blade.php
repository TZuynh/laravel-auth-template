<x-layouts.app :title="__('messages.erp.settings.title')">
    @php
        $rate = $exchangeRateInfo['usd_to_vnd'] ?? null;
        $tabs = [
            ['id' => 'general', 'title' => __('messages.erp.settings.general'), 'sub' => __('messages.erp.settings.general_sub'), 'icon' => 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zM2 12h20M12 2c3 3 3 17 0 20M12 2c-3 3-3 17 0 20'],
            ['id' => 'api', 'title' => __('messages.erp.settings.api'), 'sub' => __('messages.erp.settings.api_sub'), 'icon' => 'M4 12h4l2-7 4 14 2-7h4'],
            ['id' => 'payment', 'title' => __('messages.erp.settings.payment'), 'sub' => __('messages.erp.settings.payment_sub'), 'icon' => 'M3 6h18v12H3zM3 10h18'],
            ['id' => 'smtp', 'title' => __('messages.erp.settings.smtp'), 'sub' => __('messages.erp.settings.smtp_sub'), 'icon' => 'M4 6h16v12H4zM4 8l8 6 8-6'],
            ['id' => 'security', 'title' => __('messages.erp.settings.security'), 'sub' => __('messages.erp.settings.security_sub'), 'icon' => 'M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z'],
            ['id' => 'backup', 'title' => __('messages.erp.settings.backup'), 'sub' => __('messages.erp.settings.backup_sub'), 'icon' => 'M4 7h16v12H4zM8 7V5h8v2M8 13h8'],
            ['id' => 'advanced', 'title' => __('messages.erp.settings.advanced'), 'sub' => __('messages.erp.settings.advanced_sub'), 'icon' => 'M4 7h16M7 12h10M9 17h6'],
        ];
        $currentBankCode = $vietQrPreview['bank_code'] ?? 'VCB';
        $currentQrUrl = $vietQrPreview['image_url'] ?? '';
    @endphp

    <style>
        .settings-shell { font-size: 14px; }
        .settings-card {
            border: 1px solid #e2e8f0;
            background: #fff;
            border-radius: 1.25rem;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }
        .settings-input {
            min-height: 3.25rem;
            width: 100%;
            border-radius: 0.9rem;
            border: 1px solid #dbe3ef;
            background: #f8fafc;
            padding: 0 1rem;
            font-weight: 750;
            color: #1e293b;
            outline: none;
        }
        textarea.settings-input { padding-top: 0.9rem; }
        .settings-label {
            margin-bottom: 0.5rem;
            display: block;
            color: #64748b;
            font-size: 0.76rem;
            font-weight: 950;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .settings-tab.is-active {
            background: #0f172a;
            color: #fff;
            box-shadow: 0 18px 34px rgba(15, 23, 42, 0.12);
        }
        .settings-tab.is-active .settings-tab-icon { background: rgba(255,255,255,.16); color: #60a5fa; }
        .settings-panel { display: none; }
        .settings-panel.is-active { display: block; }
        .settings-payment-grid {
            grid-template-columns: minmax(280px, 0.8fr) minmax(520px, 1.35fr);
        }
        .settings-payment-preview {
            min-height: 100%;
        }
        .settings-qr-card {
            max-width: 430px;
        }
        .settings-toggle input:checked + span { background: #2563eb; }
        .settings-toggle input:checked + span i { transform: translateX(1.45rem); }
        .dark .settings-card,
        .dark .settings-tab,
        .dark .settings-input {
            border-color: #1e293b;
            background: #0f172a;
            color: #e2e8f0;
        }
        .dark .settings-input:focus { background: #111827; }
        .dark .settings-tab:not(.is-active):hover { border-color: #334155; }
        .dark .settings-label { color: #94a3b8; }
        .dark main .text-slate-950,
        .dark main .text-slate-900,
        .dark main .text-slate-800,
        .dark main .text-slate-700 { color: #f1f5f9; }
        .dark main .text-slate-600,
        .dark main .text-slate-500,
        .dark main .text-slate-400 { color: #94a3b8; }
        @media (max-width: 1439px) {
            .settings-payment-grid { grid-template-columns: 1fr; }
        }
    </style>

    <form action="{{ route('settings.integrations.update') }}" method="POST" class="settings-shell space-y-6" data-settings-page>
        @csrf
        <section class="settings-card flex flex-col gap-4 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-4">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/><path d="M19.4 15a1.8 1.8 0 0 0 .4 2l.1.1-2 3.4-.2-.1a1.8 1.8 0 0 0-2.1.3l-.2.2a1.8 1.8 0 0 0-.5 1.1H9.1a1.8 1.8 0 0 0-.5-1.1l-.2-.2a1.8 1.8 0 0 0-2.1-.3l-.2.1-2-3.4.1-.1a1.8 1.8 0 0 0 .4-2 1.8 1.8 0 0 0-1.5-1.1H3v-4h.1a1.8 1.8 0 0 0 1.5-1.1 1.8 1.8 0 0 0-.4-2l-.1-.1 2-3.4.2.1a1.8 1.8 0 0 0 2.1-.3l.2-.2A1.8 1.8 0 0 0 9.1 2h5.8a1.8 1.8 0 0 0 .5 1.1l.2.2a1.8 1.8 0 0 0 2.1.3l.2-.1 2 3.4-.1.1a1.8 1.8 0 0 0-.4 2 1.8 1.8 0 0 0 1.5 1.1h.1v4h-.1a1.8 1.8 0 0 0-1.5 1.1z"/></svg>
                </span>
                <div>
                    <h2 class="text-2xl font-black tracking-tight text-slate-900 dark:text-slate-100">{{ __('messages.erp.settings.heading') }}</h2>
                    <p class="mt-1 text-xs font-black uppercase tracking-[0.18em] text-slate-500">{{ __('messages.erp.settings.subtitle') }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="button" class="inline-flex h-11 items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-700 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200" onclick="window.location.reload()">{{ __('messages.erp.settings.reload') }}</button>
                <button type="submit" class="inline-flex h-11 items-center gap-2 rounded-xl bg-blue-600 px-6 text-sm font-black text-white shadow-lg shadow-blue-600/20">{{ __('messages.erp.settings.save') }}</button>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[360px_1fr]">
            <aside class="space-y-3">
                @foreach ($tabs as $tab)
                    <button type="button" class="settings-tab flex w-full items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 text-left text-slate-700 transition hover:border-blue-200 {{ $loop->first ? 'is-active' : '' }}" data-settings-tab="{{ $tab['id'] }}">
                        <span class="settings-tab-icon inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-400">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $tab['icon'] }}"/></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-base font-black">{{ $tab['title'] }}</span>
                            <span class="mt-1 block truncate text-[10px] font-black uppercase tracking-[0.16em] opacity-70">{{ $tab['sub'] }}</span>
                        </span>
                    </button>
                @endforeach
            </aside>

            <main class="settings-card p-6 lg:p-8">
                <section class="settings-panel is-active" data-settings-panel="general">
                    <h3 class="flex items-center gap-3 border-b border-slate-100 pb-4 text-2xl font-black text-slate-900">{{ __('messages.erp.settings.store_info') }}</h3>
                    <div class="mt-6 grid gap-5 md:grid-cols-2">
                        <div><label class="settings-label" for="site_name">{{ __('messages.erp.settings.site_name') }}</label><input id="site_name" name="site_name" class="settings-input" value="{{ old('site_name', $settingsValues['site_name'] ?? 'Owl Agency') }}"></div>
                        <div><label class="settings-label" for="site_hotline">{{ __('messages.erp.settings.hotline') }}</label><input id="site_hotline" name="site_hotline" class="settings-input" value="{{ old('site_hotline', $settingsValues['site_hotline'] ?? '') }}" placeholder="0900 000 000"></div>
                        <div><label class="settings-label" for="support_email">{{ __('messages.erp.settings.support_email') }}</label><input id="support_email" name="support_email" type="email" class="settings-input" value="{{ old('support_email', $settingsValues['support_email'] ?? '') }}" placeholder="support@owlagency.vn"></div>
                        <div><label class="settings-label" for="office_address">{{ __('messages.erp.settings.office_address') }}</label><input id="office_address" name="office_address" class="settings-input" value="{{ old('office_address', $settingsValues['office_address'] ?? '') }}" placeholder="TP. Hồ Chí Minh"></div>
                        <div class="md:col-span-2"><label class="settings-label" for="seo_description">{{ __('messages.erp.settings.seo_description') }}</label><textarea id="seo_description" name="seo_description" class="settings-input min-h-28" placeholder="{{ __('messages.erp.settings.seo_description') }}...">{{ old('seo_description', $settingsValues['seo_description'] ?? '') }}</textarea></div>
                    </div>
                </section>

                <section class="settings-panel" data-settings-panel="api">
                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="text-2xl font-black text-slate-900">{{ __('messages.erp.settings.api_heading') }}</h3>
                            <p class="mt-1 text-sm font-semibold text-slate-500">{{ __('messages.erp.settings.api_description') }}</p>
                        </div>
                        <button type="button" class="rounded-xl bg-blue-50 px-4 py-3 text-sm font-black text-blue-600">{{ __('messages.erp.settings.add_ai') }}</button>
                    </div>
                    <div class="mt-6 space-y-5">
                        <div><label class="settings-label">Gemini model</label><input class="settings-input" name="gemini_model" value="{{ old('gemini_model', $settingsValues['gemini_model'] ?? 'gemini-2.5-flash') }}"></div>
                        <div><label class="settings-label" for="google_maps_api_key">Google Maps API</label><input id="google_maps_api_key" name="google_maps_api_key" class="settings-input" type="password" value="{{ old('google_maps_api_key', $settingsValues['google_maps_api_key'] ?? '') }}" placeholder="Nhập Google Maps API key..."></div>
                        <div><label class="settings-label">Gemini API key</label><textarea class="settings-input min-h-24" name="gemini_api_key">{{ old('gemini_api_key', $settingsValues['gemini_api_key'] ?? '') }}</textarea></div>
                        <div class="rounded-2xl border-2 border-dashed border-slate-200 p-8 text-center text-sm font-black text-slate-400">{{ __('messages.erp.settings.no_ai') }}</div>
                    </div>
                </section>

                <section class="settings-panel" data-settings-panel="payment">
                    <h3 class="border-b border-slate-100 pb-4 text-2xl font-black text-slate-900 dark:border-slate-800 dark:text-slate-100">{{ __('messages.erp.settings.payment') }}</h3>
                    <div class="settings-payment-grid mt-6 grid gap-6">
                        <div class="space-y-5">
                            <div><label class="settings-label" for="bank_name">{{ __('messages.erp.settings.bank_name') }}</label><input id="bank_name" name="bank_name" class="settings-input" value="{{ old('bank_name', $settingsValues['bank_name'] ?? 'Vietcombank (VCB)') }}" data-vietqr-bank></div>
                            <div><label class="settings-label" for="bank_account">{{ __('messages.erp.settings.bank_account') }}</label><input id="bank_account" name="bank_account" class="settings-input" value="{{ old('bank_account', $settingsValues['bank_account'] ?? '') }}" placeholder="Ví dụ: 0123456789" data-vietqr-account></div>
                            <div><label class="settings-label" for="bank_holder">{{ __('messages.erp.settings.bank_holder') }}</label><input id="bank_holder" name="bank_holder" class="settings-input" value="{{ old('bank_holder', $settingsValues['bank_holder'] ?? '') }}" placeholder="NGUYEN VAN A" data-vietqr-holder></div>
                            <div><label class="settings-label">{{ __('messages.erp.settings.usd_rate') }}</label><input class="settings-input" name="product_export_usd_rate" type="number" step="0.01" value="{{ old('product_export_usd_rate', $settingsValues['product_export_usd_rate'] ?? ($rate ? number_format((float) $rate, 2, '.', '') : '26295.55')) }}"></div>
                        </div>
                        <div class="settings-payment-preview rounded-[1.75rem] border border-blue-100 bg-blue-50 p-6 text-center dark:border-blue-900/40 dark:bg-blue-950/20">
                            <p class="text-sm font-black uppercase tracking-[0.16em] text-slate-700 dark:text-slate-200">{{ __('messages.erp.settings.qr_preview') }}</p>
                            <div class="settings-qr-card mx-auto mt-6 w-full rounded-[1.5rem] bg-white p-6 shadow-xl dark:bg-slate-950">
                                <img
                                    src="{{ $currentQrUrl }}"
                                    alt="VietQR"
                                    class="mx-auto aspect-square w-full max-w-[320px] rounded-2xl border border-slate-100 object-contain"
                                    data-vietqr-image
                                    data-initial-bank="{{ $currentBankCode }}"
                                >
                                <p class="mt-4 text-base font-black text-slate-900 dark:text-slate-100" data-vietqr-holder-preview>{{ old('bank_holder', $settingsValues['bank_holder'] ?? 'TÊN CHỦ TÀI KHOẢN') ?: 'TÊN CHỦ TÀI KHOẢN' }}</p>
                                <p class="mt-2 rounded-xl bg-slate-50 py-2 text-xs font-black text-slate-400 dark:bg-slate-900" data-vietqr-account-preview>{{ old('bank_account', $settingsValues['bank_account'] ?? '0000000000') ?: '0000000000' }}</p>
                                <p class="mt-3 inline-flex rounded-full bg-blue-100 px-4 py-2 text-[11px] font-black uppercase tracking-wider text-blue-600 dark:bg-blue-500/10" data-vietqr-bank-preview>{{ $currentBankCode }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="settings-panel" data-settings-panel="smtp">
                    <h3 class="border-b border-slate-100 pb-4 text-2xl font-black text-slate-900">{{ __('messages.erp.settings.email_heading') }}</h3>
                    <div class="mt-6 grid gap-5 md:grid-cols-2">
                        <div><label class="settings-label" for="smtp_host">SMTP Host</label><input id="smtp_host" name="smtp_host" class="settings-input" value="{{ old('smtp_host', $settingsValues['smtp_host'] ?? 'smtp.gmail.com') }}"></div>
                        <div><label class="settings-label" for="smtp_port">SMTP Port</label><input id="smtp_port" name="smtp_port" class="settings-input" value="{{ old('smtp_port', $settingsValues['smtp_port'] ?? '587') }}"></div>
                        <div><label class="settings-label" for="smtp_username">SMTP Username</label><input id="smtp_username" name="smtp_username" class="settings-input" value="{{ old('smtp_username', $settingsValues['smtp_username'] ?? '') }}" placeholder="your-email@gmail.com"></div>
                        <div><label class="settings-label" for="smtp_password">{{ __('messages.erp.settings.smtp_password') }}</label><input id="smtp_password" name="smtp_password" class="settings-input" type="password" value="{{ old('smtp_password', $settingsValues['smtp_password'] ?? '') }}" placeholder="{{ __('messages.erp.settings.smtp_password') }}..."></div>
                        <div><label class="settings-label" for="smtp_from_name">From name</label><input id="smtp_from_name" name="smtp_from_name" class="settings-input" value="{{ old('smtp_from_name', $settingsValues['smtp_from_name'] ?? 'Owl Agency') }}" placeholder="Owl Agency"></div>
                        <div><label class="settings-label" for="smtp_from_email">From email</label><input id="smtp_from_email" name="smtp_from_email" type="email" class="settings-input" value="{{ old('smtp_from_email', $settingsValues['smtp_from_email'] ?? '') }}" placeholder="no-reply@domain.com"></div>
                    </div>
                    <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold leading-7 text-amber-800">{{ __('messages.erp.settings.gmail_note') }}</div>
                </section>

                <section class="settings-panel" data-settings-panel="security">
                    <h3 class="border-b border-slate-100 pb-4 text-2xl font-black text-slate-900">{{ __('messages.erp.settings.security_heading') }}</h3>
                    <div class="mt-6 space-y-5">
                        <label class="settings-toggle flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <span><strong class="block text-lg text-slate-900">{{ __('messages.erp.settings.two_factor') }}</strong><span class="mt-1 block text-sm text-slate-500">{{ __('messages.erp.settings.two_factor_desc') }}</span></span>
                            <input name="two_factor_required" value="1" type="checkbox" class="hidden" @checked(old('two_factor_required', $settingsValues['two_factor_required'] ?? false))>
                            <span class="relative h-9 w-16 rounded-full bg-slate-300 transition"><i class="absolute left-1 top-1 h-7 w-7 rounded-full bg-white shadow transition"></i></span>
                        </label>
                        <div class="grid gap-5 md:grid-cols-2">
                            <div><label class="settings-label" for="session_lifetime">{{ __('messages.erp.settings.session_lifetime') }}</label><input id="session_lifetime" name="session_lifetime" type="number" min="5" max="10080" class="settings-input" value="{{ old('session_lifetime', $settingsValues['session_lifetime'] ?? '60') }}"></div>
                            <div><label class="settings-label" for="lock_ip_after">{{ __('messages.erp.settings.lock_ip_after') }}</label><input id="lock_ip_after" name="lock_ip_after" type="number" min="1" max="50" class="settings-input" value="{{ old('lock_ip_after', $settingsValues['lock_ip_after'] ?? '5') }}"></div>
                        </div>
                    </div>
                </section>

                <section class="settings-panel" data-settings-panel="backup">
                    <h3 class="border-b border-slate-100 pb-4 text-2xl font-black text-slate-900">{{ __('messages.erp.settings.backup_heading') }}</h3>
                    <div class="mt-6 space-y-6">
                        <label class="settings-toggle flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <span><strong class="block text-lg text-slate-900">{{ __('messages.erp.settings.backup_auto') }}</strong><span class="mt-1 block text-sm text-slate-500">{{ __('messages.erp.settings.backup_auto_desc') }}</span></span>
                            <input name="backup_enabled" value="1" type="checkbox" class="hidden" @checked(old('backup_enabled', $settingsValues['backup_enabled'] ?? false))>
                            <span class="relative h-9 w-16 rounded-full bg-slate-300 transition"><i class="absolute left-1 top-1 h-7 w-7 rounded-full bg-white shadow transition"></i></span>
                        </label>
                        <div class="grid gap-5 md:grid-cols-2">
                            <button type="button" class="min-h-40 rounded-2xl border-2 border-dashed border-blue-300 bg-blue-50 text-sm font-black uppercase tracking-[0.18em] text-blue-600">{{ __('messages.erp.settings.manual_backup') }}</button>
                            <button type="button" class="min-h-40 rounded-2xl border-2 border-dashed border-orange-300 bg-orange-50 text-sm font-black uppercase tracking-[0.18em] text-orange-600">{{ __('messages.erp.settings.restore_backup') }}</button>
                        </div>
                    </div>
                </section>

                <section class="settings-panel" data-settings-panel="advanced">
                    <h3 class="border-b border-slate-100 pb-4 text-2xl font-black text-slate-900">{{ __('messages.erp.settings.advanced_heading') }}</h3>
                    <div class="mt-6 grid gap-5 md:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <h4 class="text-lg font-black text-slate-900">{{ __('messages.erp.settings.cache_title') }}</h4>
                            <p class="mt-2 text-sm font-semibold text-slate-500">{{ __('messages.erp.settings.cache_desc') }}</p>
                            <button class="mt-5 rounded-xl bg-slate-950 px-5 py-3 text-sm font-black text-white" type="button" onclick="document.getElementById('cache-clear-form')?.submit()">{{ __('messages.erp.settings.clear_cache') }}</button>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <h4 class="text-lg font-black text-slate-900">{{ __('messages.erp.settings.maintenance') }}</h4>
                            <p class="mt-2 text-sm font-semibold text-slate-500">{{ __('messages.erp.settings.maintenance_desc') }}</p>
                            <button type="button" class="mt-5 rounded-xl bg-rose-50 px-5 py-3 text-sm font-black text-rose-600">{{ __('messages.erp.settings.enable_maintenance') }}</button>
                        </div>
                    </div>
                </section>
            </main>
        </section>
    </form>

    <form id="cache-clear-form" action="{{ route('settings.cache-clear') }}" method="POST" class="hidden">@csrf</form>

    <script>
        (() => {
            const root = document.querySelector('[data-settings-page]');
            if (!root) return;
            const tabs = root.querySelectorAll('[data-settings-tab]');
            const panels = root.querySelectorAll('[data-settings-panel]');
            const activate = (id) => {
                tabs.forEach((tab) => tab.classList.toggle('is-active', tab.dataset.settingsTab === id));
                panels.forEach((panel) => panel.classList.toggle('is-active', panel.dataset.settingsPanel === id));
            };
            tabs.forEach((tab) => tab.addEventListener('click', () => activate(tab.dataset.settingsTab)));

            const bankInput = root.querySelector('[data-vietqr-bank]');
            const accountInput = root.querySelector('[data-vietqr-account]');
            const holderInput = root.querySelector('[data-vietqr-holder]');
            const qrImage = root.querySelector('[data-vietqr-image]');
            const accountPreview = root.querySelector('[data-vietqr-account-preview]');
            const holderPreview = root.querySelector('[data-vietqr-holder-preview]');
            const bankPreview = root.querySelector('[data-vietqr-bank-preview]');
            const bankCode = (value) => {
                const text = (value || '').toLowerCase();
                if (text.includes('mb') || text.includes('military')) return 'MB';
                if (text.includes('techcombank') || text.includes('tcb')) return 'TCB';
                if (text.includes('vietinbank') || text.includes('ctg')) return 'ICB';
                if (text.includes('bidv')) return 'BIDV';
                if (text.includes('acb')) return 'ACB';
                if (text.includes('sacombank') || text.includes('stb')) return 'STB';
                return 'VCB';
            };
            const refreshQr = () => {
                const bank = bankCode(bankInput?.value);
                const account = (accountInput?.value || '').replace(/\D+/g, '') || '0000000000';
                const holder = (holderInput?.value || '').trim() || 'NGUYEN VAN A';
                if (qrImage) {
                    qrImage.src = `https://img.vietqr.io/image/${encodeURIComponent(bank)}-${encodeURIComponent(account)}-compact2.png?amount=0&addInfo=${encodeURIComponent('Thanh toan don hang')}&accountName=${encodeURIComponent(holder)}`;
                }
                if (accountPreview) accountPreview.textContent = account;
                if (holderPreview) holderPreview.textContent = holder;
                if (bankPreview) bankPreview.textContent = bank;
            };
            [bankInput, accountInput, holderInput].forEach((input) => input?.addEventListener('input', refreshQr));
        })();
    </script>
</x-layouts.app>
