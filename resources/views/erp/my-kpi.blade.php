<x-layouts.app :title="__('messages.erp.sidebar.my_kpi')">
    @include('erp.partials.styles')

    <div class="erp-card p-7 md:p-10" data-my-kpi-page>
        <section class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
            <div class="flex items-start gap-4">
                <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.5"/>
                    </svg>
                </span>
                <div>
                    <h2 class="text-4xl font-black uppercase tracking-tight text-slate-900">Quản Trị KPI Cá Nhân</h2>
                    <p class="mt-3 text-lg font-black italic text-slate-400">Tháng {{ now()->format('m / Y') }}</p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl bg-slate-950 px-8 py-5 text-center shadow-xl shadow-slate-200">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Hiệu suất dự kiến</p>
                    <p class="mt-2 text-4xl font-black text-amber-400" data-expected-score>0.00</p>
                </div>
                <div class="rounded-2xl bg-rose-50 px-8 py-5 text-center shadow-xl shadow-rose-100">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-rose-600">Tổng trọng số</p>
                    <p class="mt-2 text-4xl font-black text-red-600" data-total-weight>0%</p>
                </div>
            </div>
        </section>

        <section class="mt-10 overflow-hidden rounded-3xl border border-slate-200">
            <div class="grid grid-cols-[1fr_140px_1fr_140px_90px] bg-slate-50 px-6 py-5 text-xs font-black uppercase tracking-[0.16em] text-slate-500">
                <div>Chỉ tiêu công việc</div>
                <div>Tỷ trọng</div>
                <div>Minh chứng</div>
                <div>Tự chấm</div>
                <div></div>
            </div>
            <div id="my-kpi-list" class="divide-y divide-slate-100">
                @foreach ($kpiRows as $row)
                    <div class="grid grid-cols-[1fr_140px_1fr_140px_90px] items-center gap-4 px-6 py-5" data-kpi-row>
                        <input class="erp-input" value="{{ $row['target'] }}" data-kpi-target>
                        <input class="erp-input" type="number" min="0" max="100" value="{{ $row['weight'] }}" data-kpi-weight>
                        <input class="erp-input" value="{{ $row['evidence'] }}" data-kpi-evidence>
                        <input class="erp-input" type="number" min="0" max="100" value="{{ $row['self_score'] }}" data-kpi-score>
                        <button type="button" class="rounded-xl bg-rose-50 px-3 py-3 text-xs font-black text-rose-600" data-remove-kpi>Xóa</button>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="mt-8 grid gap-4 lg:grid-cols-[340px_1fr]">
            <button type="button" class="erp-btn h-20 border-2 border-dashed border-slate-300 bg-white text-slate-400" data-add-kpi>
                <span class="text-2xl">+</span>
                Thêm chỉ tiêu mới
            </button>
            <button type="button" class="erp-btn erp-btn-dark h-20 text-lg" data-save-kpi>
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                Lưu & Gửi Tự Đánh Giá
            </button>
        </section>

        <section class="mt-8 rounded-3xl border border-blue-200 bg-blue-50 p-6 text-blue-900">
            <h3 class="flex items-center gap-3 text-lg font-black uppercase">
                <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01M10.3 3.9 2.5 17.4A2 2 0 0 0 4.2 20h15.6a2 2 0 0 0 1.7-2.6L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
                Quy định chỉnh sửa KPI
            </h3>
            <p class="mt-3 text-sm font-semibold leading-7">Bạn chỉ có thể sửa tên chỉ tiêu và trọng số khi trạng thái là chờ duyệt. Sau khi đã phê duyệt, dữ liệu sẽ khóa từng để đảm bảo tính minh bạch. Bạn vẫn có quyền cập nhật minh chứng và điểm tự chấm cho đến khi kỳ đánh giá kết thúc.</p>
        </section>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-my-kpi-page]');
            if (!root) return;
            const list = document.getElementById('my-kpi-list');
            const totalWeight = root.querySelector('[data-total-weight]');
            const expectedScore = root.querySelector('[data-expected-score]');

            const recalc = () => {
                let weight = 0;
                let expected = 0;
                root.querySelectorAll('[data-kpi-row]').forEach((row) => {
                    const w = Number(row.querySelector('[data-kpi-weight]')?.value || 0);
                    const s = Number(row.querySelector('[data-kpi-score]')?.value || 0);
                    weight += w;
                    expected += (w * s) / 100;
                });
                totalWeight.textContent = `${weight}%`;
                expectedScore.textContent = expected.toFixed(2);
                totalWeight.classList.toggle('text-emerald-600', weight === 100);
                totalWeight.classList.toggle('text-red-600', weight !== 100);
            };

            root.addEventListener('input', recalc);
            root.addEventListener('click', (event) => {
                const remove = event.target.closest('[data-remove-kpi]');
                if (remove) {
                    remove.closest('[data-kpi-row]')?.remove();
                    recalc();
                    return;
                }

                if (event.target.closest('[data-add-kpi]')) {
                    list?.insertAdjacentHTML('beforeend', `
                        <div class="grid grid-cols-[1fr_140px_1fr_140px_90px] items-center gap-4 px-6 py-5" data-kpi-row>
                            <input class="erp-input" value="Chỉ tiêu mới" data-kpi-target>
                            <input class="erp-input" type="number" min="0" max="100" value="10" data-kpi-weight>
                            <input class="erp-input" value="Minh chứng cần bổ sung" data-kpi-evidence>
                            <input class="erp-input" type="number" min="0" max="100" value="80" data-kpi-score>
                            <button type="button" class="rounded-xl bg-rose-50 px-3 py-3 text-xs font-black text-rose-600" data-remove-kpi>Xóa</button>
                        </div>
                    `);
                    recalc();
                }

                if (event.target.closest('[data-save-kpi]')) {
                    event.target.closest('[data-save-kpi]').textContent = 'Đã lưu tự đánh giá';
                }
            });

            recalc();
        })();
    </script>
</x-layouts.app>
