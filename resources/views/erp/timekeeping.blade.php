<x-layouts.app :title="__('messages.erp.sidebar.timekeeping')">
    @include('erp.partials.styles')

    <div class="space-y-8" data-timekeeping-page>
        <section class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div class="flex items-center gap-4">
                <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-950 text-amber-400 shadow-xl">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"/><path d="M12 7v5l4 2"/>
                    </svg>
                </span>
                <div>
                    <h2 class="text-3xl font-black tracking-tight text-slate-900 dark:text-slate-100">{{ __('messages.erp.sidebar.timekeeping') }}</h2>
                    <p class="mt-2 text-base font-semibold text-slate-500 dark:text-slate-400">{{ __('messages.erp.ui.timekeeping_desc') }}</p>
                </div>
            </div>

            <div class="erp-card flex flex-wrap items-center gap-3 p-2">
                <button type="button" class="erp-btn erp-btn-blue">{{ __('messages.erp.ui.today_timesheet') }}</button>
                <button type="button" class="erp-btn erp-btn-outline">{{ __('messages.erp.ui.monthly_summary') }}</button>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-[220px_260px_1fr_auto_auto] xl:items-center">
            <input type="date" value="{{ now()->format('Y-m-d') }}" class="erp-input">
            <select class="erp-input">
                @foreach ($departments as $department)
                    <option>{{ $department }}</option>
                @endforeach
            </select>
            <div></div>
            <button type="button" class="erp-btn border border-rose-200 bg-rose-50 text-rose-600" data-test-checkin>{{ __('messages.erp.ui.test_checkin') }}</button>
            <button type="button" class="erp-btn erp-btn-dark" data-gps-checkin>
                <svg class="h-5 w-5 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 21s7-5.1 7-11a7 7 0 1 0-14 0c0 5.9 7 11 7 11z"/><circle cx="12" cy="10" r="2"/>
                </svg>
                {{ __('messages.erp.ui.gps_checkin') }}
            </button>
        </section>

        <section class="erp-card overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-slate-100 p-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-xl font-black text-slate-900">{{ __('messages.erp.ui.today_timesheet') }}</h3>
                    <p class="mt-1 text-sm font-semibold text-slate-500" data-gps-status>{{ __('messages.erp.ui.gps_not_checked') }}</p>
                </div>
                <div class="flex gap-2 text-sm font-black">
                    <span class="rounded-xl bg-emerald-50 px-3 py-2 text-emerald-600">{{ count($attendanceRows) }} {{ __('messages.erp.ui.records') }}</span>
                    <span class="rounded-xl bg-amber-50 px-3 py-2 text-amber-600">1 {{ __('messages.erp.ui.working') }}</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="erp-table min-w-[980px]">
                    <thead>
                        <tr>
                            <th>{{ __('messages.erp.ui.employee') }}</th>
                            <th>{{ __('messages.erp.ui.department') }}</th>
                            <th>{{ __('messages.erp.ui.shift') }}</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>{{ __('messages.erp.ui.location') }}</th>
                            <th>{{ __('messages.erp.ui.status') }}</th>
                        </tr>
                    </thead>
                    <tbody id="attendance-body">
                        @foreach ($attendanceRows as $row)
                            <tr>
                                <td>{{ $row['name'] }}</td>
                                <td>{{ $row['department'] }}</td>
                                <td>{{ $row['shift'] }}</td>
                                <td>{{ $row['check_in'] }}</td>
                                <td>{{ $row['check_out'] }}</td>
                                <td>{{ $row['location'] }}</td>
                                <td>
                                    @php
                                        $tone = $row['tone'] ?? 'emerald';
                                        $statusClass = match ($tone) {
                                            'amber' => 'bg-amber-50 text-amber-600',
                                            'blue' => 'bg-blue-50 text-blue-600',
                                            default => 'bg-emerald-50 text-emerald-600',
                                        };
                                    @endphp
                                    <span class="rounded-xl px-3 py-1 text-xs font-black {{ $statusClass }}">{{ $row['status'] }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-timekeeping-page]');
            if (!root) return;
            const status = root.querySelector('[data-gps-status]');
            const body = document.getElementById('attendance-body');
            const labels = {{ \Illuminate\Support\Js::from([
                'testCheckinDone' => __('messages.erp.ui.test_checkin_done'),
                'browserNoGps' => __('messages.erp.ui.browser_no_gps'),
                'gettingGps' => __('messages.erp.ui.getting_gps'),
                'gpsSuccess' => __('messages.erp.ui.gps_success', ['location' => ':location']),
                'gpsDenied' => __('messages.erp.ui.gps_denied'),
                'testLocation' => __('messages.erp.ui.test_location'),
                'noGps' => __('messages.erp.ui.no_gps'),
                'gpsNeedsVerification' => __('messages.erp.ui.gps_needs_verification'),
                'justCheckedIn' => __('messages.erp.ui.just_checked_in'),
                'executiveBoard' => __('messages.erp.ui.executive_board'),
                'mainShift' => __('messages.erp.ui.main_shift'),
            ]) }};
            const browserLocale = {{ \Illuminate\Support\Js::from(app()->getLocale() === 'vi' ? 'vi-VN' : 'en-US') }};
            const addRow = (locationText) => {
                const now = new Date();
                const hhmm = now.toLocaleTimeString(browserLocale, { hour: '2-digit', minute: '2-digit' });
                body?.insertAdjacentHTML('afterbegin', `
                    <tr class="bg-blue-50/50">
                        <td>{{ auth()->user()->name ?? 'Admin' }}</td>
                        <td>${labels.executiveBoard}</td>
                        <td>${labels.mainShift}</td>
                        <td>${hhmm}</td>
                        <td>-</td>
                        <td>${locationText}</td>
                        <td><span class="rounded-xl bg-blue-50 px-3 py-1 text-xs font-black text-blue-600">${labels.justCheckedIn}</span></td>
                    </tr>
                `);
            };

            root.querySelector('[data-test-checkin]')?.addEventListener('click', () => {
                status.textContent = labels.testCheckinDone;
                addRow(labels.testLocation);
            });

            root.querySelector('[data-gps-checkin]')?.addEventListener('click', () => {
                if (!navigator.geolocation) {
                    status.textContent = labels.browserNoGps;
                    addRow(labels.noGps);
                    return;
                }

                status.textContent = labels.gettingGps;
                navigator.geolocation.getCurrentPosition((position) => {
                    const lat = position.coords.latitude.toFixed(6);
                    const lng = position.coords.longitude.toFixed(6);
                    const text = `GPS ${lat}, ${lng}`;
                    status.textContent = labels.gpsSuccess.replace(':location', text);
                    addRow(text);
                }, () => {
                    status.textContent = labels.gpsDenied;
                    addRow(labels.gpsNeedsVerification);
                }, { enableHighAccuracy: true, timeout: 8000 });
            });
        })();
    </script>
</x-layouts.app>
