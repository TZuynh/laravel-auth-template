<x-layouts.app :title="__('messages.erp.sidebar.employees')">
    @include('erp.partials.styles')

    <div class="space-y-8" data-employees-page>
        <section class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div class="flex items-center gap-4">
                <span class="inline-flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-950 text-amber-400 shadow-xl">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </span>
                <div>
                    <h2 class="text-4xl font-black tracking-tight text-slate-900">Hồ Sơ Nhân Sự</h2>
                    <p class="mt-2 text-lg font-semibold text-slate-500">Quản lý danh sách {{ $employees->count() }} nhân sự toàn công ty.</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="button" class="erp-btn erp-btn-outline" data-print-section="#employee-print">In Danh Sách Tổng</button>
                <button type="button" class="erp-btn erp-btn-orange" data-open-employee-modal>Thêm Hồ Sơ Mới</button>
            </div>
        </section>

        <section class="erp-card p-5">
            <form method="GET" action="{{ route('erp.employees') }}" class="grid gap-4 md:grid-cols-[1fr_auto]">
                <input class="erp-input" name="q" value="{{ $q }}" placeholder="Tìm theo tên hoặc SĐT...">
                <button class="erp-btn erp-btn-dark" type="submit">Tìm kiếm</button>
            </form>
        </section>

        <section class="erp-card overflow-hidden" id="employee-print">
            <div class="overflow-x-auto">
                <table class="erp-table min-w-[1120px]" id="employee-table">
                    <thead>
                        <tr>
                            <th>Mã NV</th>
                            <th>Nhân sự</th>
                            <th>Số điện thoại</th>
                            <th>Email</th>
                            <th>Phòng ban</th>
                            <th>Chức danh</th>
                            <th>Trạng thái</th>
                            <th class="erp-actions">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="employee-body">
                        @forelse ($employees as $employee)
                            <tr data-employee-row>
                                <td>{{ $employee['id'] }}</td>
                                <td class="font-black text-slate-900">{{ $employee['name'] }}</td>
                                <td>{{ $employee['phone'] }}</td>
                                <td>{{ $employee['email'] }}</td>
                                <td>{{ $employee['department'] }}</td>
                                <td>{{ $employee['title'] }}</td>
                                <td><span class="rounded-xl bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-600">{{ $employee['status'] }}</span></td>
                                <td class="erp-actions">
                                    <div class="flex gap-2">
                                        <button type="button" class="rounded-xl bg-blue-50 px-3 py-2 text-xs font-black text-blue-600" data-edit-employee>Sửa</button>
                                        <button type="button" class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-black text-rose-600" data-delete-row>Xóa</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="py-16 text-center text-slate-400">Chưa có dữ liệu</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="erp-modal" id="employee-modal">
            <div class="erp-modal-panel">
                <form class="p-6" data-employee-form>
                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-5">
                        <h3 class="text-2xl font-black text-slate-900">Hồ sơ nhân sự</h3>
                        <button type="button" class="rounded-xl p-2 text-slate-400 hover:bg-slate-100" data-close-modal>Đóng</button>
                    </div>
                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        <input class="erp-input" name="id" placeholder="Mã NV" required>
                        <input class="erp-input" name="name" placeholder="Họ tên" required>
                        <input class="erp-input" name="phone" placeholder="Số điện thoại" required>
                        <input class="erp-input" name="email" type="email" placeholder="Email" required>
                        <select class="erp-input" name="department">
                            @foreach ($departments as $department)
                                @if ($department !== 'Tat ca phong ban')
                                    <option>{{ $department }}</option>
                                @endif
                            @endforeach
                        </select>
                        <input class="erp-input" name="title" placeholder="Chức danh" required>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" class="erp-btn erp-btn-outline" data-close-modal>Hủy</button>
                        <button class="erp-btn erp-btn-blue" type="submit">Lưu hồ sơ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-employees-page]');
            if (!root) return;
            const modal = document.getElementById('employee-modal');
            const form = root.querySelector('[data-employee-form]');
            const body = document.getElementById('employee-body');
            let editingRow = null;

            const open = () => modal?.classList.add('is-open');
            const close = () => {
                modal?.classList.remove('is-open');
                form?.reset();
                editingRow = null;
            };

            root.querySelector('[data-open-employee-modal]')?.addEventListener('click', open);
            root.querySelectorAll('[data-close-modal]').forEach((button) => button.addEventListener('click', close));
            modal?.addEventListener('click', (event) => {
                if (event.target === modal) close();
            });

            root.addEventListener('click', (event) => {
                const deleteButton = event.target.closest('[data-delete-row]');
                if (deleteButton) {
                    deleteButton.closest('tr')?.remove();
                    return;
                }
                const editButton = event.target.closest('[data-edit-employee]');
                if (editButton && form) {
                    editingRow = editButton.closest('tr');
                    const cells = editingRow.querySelectorAll('td');
                    form.id.value = cells[0].textContent.trim();
                    form.name.value = cells[1].textContent.trim();
                    form.phone.value = cells[2].textContent.trim();
                    form.email.value = cells[3].textContent.trim();
                    form.department.value = cells[4].textContent.trim();
                    form.title.value = cells[5].textContent.trim();
                    open();
                }
            });

            form?.addEventListener('submit', (event) => {
                event.preventDefault();
                const data = Object.fromEntries(new FormData(form).entries());
                const rowHtml = `
                    <td>${data.id}</td>
                    <td class="font-black text-slate-900">${data.name}</td>
                    <td>${data.phone}</td>
                    <td>${data.email}</td>
                    <td>${data.department}</td>
                    <td>${data.title}</td>
                    <td><span class="rounded-xl bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-600">Đang làm việc</span></td>
                    <td class="erp-actions"><div class="flex gap-2"><button type="button" class="rounded-xl bg-blue-50 px-3 py-2 text-xs font-black text-blue-600" data-edit-employee>Sửa</button><button type="button" class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-black text-rose-600" data-delete-row>Xóa</button></div></td>
                `;
                if (editingRow) {
                    editingRow.innerHTML = rowHtml;
                } else {
                    body?.insertAdjacentHTML('afterbegin', `<tr data-employee-row>${rowHtml}</tr>`);
                }
                close();
            });
        })();
    </script>
</x-layouts.app>
