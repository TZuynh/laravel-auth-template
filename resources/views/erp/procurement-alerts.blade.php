<x-layouts.app :title="__('messages.erp.sidebar.procurement')">
    @include('erp.partials.styles')

    <div class="space-y-8">
        <section class="rounded-3xl border border-emerald-200 bg-emerald-50 p-8">
            <div class="flex items-center gap-6">
                <span class="inline-flex h-20 w-20 items-center justify-center rounded-3xl bg-emerald-600 text-white">
                    <svg class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.2 2.2 4.8-5"/>
                    </svg>
                </span>
                <div>
                    <h2 class="text-4xl font-black tracking-tight text-emerald-700">Kho Vật Tư Đang An Toàn</h2>
                    <p class="mt-2 text-lg font-semibold text-emerald-700">Không có mặt hàng nào cần nhập thêm lúc này.</p>
                </div>
            </div>
        </section>

        <section class="erp-card overflow-hidden">
            <div class="border-b border-slate-100 p-6">
                <h3 class="text-2xl font-black text-slate-900">Ngưỡng an toàn vật tư</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="erp-table min-w-[900px]">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Vật tư</th>
                            <th>Tồn hiện tại</th>
                            <th>Tối thiểu</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($alerts as $alert)
                            <tr>
                                <td>{{ $alert['sku'] }}</td>
                                <td class="font-black text-slate-900">{{ $alert['name'] }}</td>
                                <td>{{ $alert['stock'] }}</td>
                                <td>{{ $alert['minimum'] }}</td>
                                <td><span class="rounded-xl px-3 py-1 text-xs font-black {{ $alert['status'] === 'An toan' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">{{ $alert['status'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-layouts.app>
