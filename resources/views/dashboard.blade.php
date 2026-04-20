<x-layouts.app title="Executive Dashboard">
    <div class="max-w-7xl mx-auto space-y-10 pb-12">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-xs font-bold text-indigo-600 uppercase tracking-widest mb-2">
                    <span class="bg-indigo-600 w-2 h-2 rounded-full animate-pulse"></span>
                    Hệ thống trực tuyến
                </nav>
                <h1 class="text-4xl font-black text-slate-900 tracking-tighter">Tổng quan hệ thống</h1>
            </div>
            <div class="flex items-center gap-3 bg-white p-1.5 rounded-2xl shadow-sm border border-slate-200">
                <button class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50 rounded-xl transition-all">Xuất dữ liệu</button>
                <button class="px-5 py-2 text-sm font-bold bg-slate-900 text-white rounded-xl shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all">
                    Tạo báo cáo mới
                </button>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="group cursor-pointer">
                <div class="flex items-end justify-between mb-4">
                    <p class="text-sm font-bold text-slate-500 uppercase tracking-tight">Doanh thu tháng</p>
                    <span class="text-emerald-500 text-xs font-black bg-emerald-50 px-2 py-1 rounded-lg">+18.4%</span>
                </div>
                <div class="flex items-baseline gap-2">
                    <h2 class="text-5xl font-black text-slate-900 tracking-tighter">$42,850</h2>
                    <span class="text-slate-400 font-medium italic">USD</span>
                </div>
                <div class="mt-6 h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-slate-900 w-3/4 group-hover:bg-indigo-600 transition-all duration-500"></div>
                </div>
            </div>

            <div class="group cursor-pointer">
                <div class="flex items-end justify-between mb-4">
                    <p class="text-sm font-bold text-slate-500 uppercase tracking-tight">Người dùng active</p>
                    <span class="text-indigo-500 text-xs font-black bg-indigo-50 px-2 py-1 rounded-lg">Mục tiêu: 2k</span>
                </div>
                <div class="flex items-baseline gap-2">
                    <h2 class="text-5xl font-black text-slate-900 tracking-tighter">1,842</h2>
                    <span class="text-slate-400 font-medium italic">User</span>
                </div>
                <div class="mt-6 flex -space-x-3 overflow-hidden">
                    @foreach([1,2,3,4,5] as $i)
                        <img class="inline-block h-8 w-8 rounded-full ring-4 ring-slate-50 bg-slate-200" src="https://i.pravatar.cc/150?u={{$i}}" alt="">
                    @endforeach
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 ring-4 ring-slate-50">
                        <span class="text-[10px] font-bold text-white">+12</span>
                    </div>
                </div>
            </div>

            <div class="group cursor-pointer">
                <div class="flex items-end justify-between mb-4">
                    <p class="text-sm font-bold text-slate-500 uppercase tracking-tight">Tỷ lệ chuyển đổi</p>
                    <span class="text-rose-500 text-xs font-black bg-rose-50 px-2 py-1 rounded-lg">-2.1%</span>
                </div>
                <div class="flex items-baseline gap-2">
                    <h2 class="text-5xl font-black text-slate-900 tracking-tighter">14.2</h2>
                    <span class="text-slate-400 font-medium italic">%</span>
                </div>
                <div class="mt-6 flex gap-1 items-end h-8">
                    @foreach([40,70,50,90,60,80,100] as $h)
                        <div class="flex-1 bg-slate-200 rounded-sm group-hover:bg-rose-400 transition-all duration-500" style="height: {{$h}}%"></div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-8">
            
            <div class="col-span-12 lg:col-span-8 bg-white border border-slate-200 rounded-[2.5rem] p-10 shadow-[0_20px_50px_rgba(0,0,0,0.02)]">
                <div class="flex items-center justify-between mb-10">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900">Phân tích dòng tiền</h3>
                        <p class="text-slate-400 text-sm">Dữ liệu được cập nhật 5 phút trước</p>
                    </div>
                    <div class="flex gap-2 bg-slate-50 p-1.5 rounded-2xl">
                        <button class="px-6 py-2 bg-white shadow-sm rounded-xl text-xs font-black text-slate-900">Income</button>
                        <button class="px-6 py-2 text-xs font-bold text-slate-400 hover:text-slate-600">Expenses</button>
                    </div>
                </div>
                <div class="h-[400px]">
                    <canvas id="ultraChart"></canvas>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-4 space-y-8">
                <div class="bg-indigo-600 rounded-[2.5rem] p-8 text-white relative overflow-hidden group shadow-2xl shadow-indigo-200">
                    <div class="relative z-10">
                        <h4 class="text-3xl font-black leading-tight mb-4">Sẵn sàng để bùng nổ?</h4>
                        <p class="text-indigo-100 text-sm mb-6 opacity-80">Khám phá các tính năng AI mới nhất để tối ưu hóa quy trình làm việc.</p>
                        <a href="#" class="inline-block bg-white text-indigo-600 px-8 py-3 rounded-2xl font-black text-sm hover:bg-indigo-50 transition-all">Thử ngay</a>
                    </div>
                    <svg class="absolute right-[-10%] bottom-[-10%] w-48 h-48 text-white opacity-10 group-hover:rotate-12 transition-transform duration-700" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L4.5 20.29l.71.71L12 18l6.79 3 .71-.71z"/></svg>
                </div>

                <div class="bg-white border border-slate-200 rounded-[2.5rem] p-8">
                    <h4 class="text-xl font-black text-slate-900 mb-6">Giao dịch gần đây</h4>
                    <div class="space-y-6">
                        @foreach([
                            ['name' => 'Figma Pro Subscription', 'type' => 'Software', 'amount' => '-$15.00'],
                            ['name' => 'Payment from Client', 'type' => 'Invoice', 'amount' => '+$2,400.00'],
                            ['name' => 'Amazon AWS Cloud', 'type' => 'Infrastructure', 'amount' => '-$120.50']
                        ] as $item)
                        <div class="flex items-center justify-between group cursor-pointer">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center group-hover:bg-indigo-50 transition-colors">
                                    <div class="w-2 h-2 bg-slate-400 rounded-full group-hover:bg-indigo-500"></div>
                                </div>
                                <div>
                                    <p class="text-sm font-black text-slate-800">{{ $item['name'] }}</p>
                                    <p class="text-xs text-slate-400 font-medium">{{ $item['type'] }}</p>
                                </div>
                            </div>
                            <p class="text-sm font-black {{ str_contains($item['amount'], '+') ? 'text-emerald-500' : 'text-slate-900' }}">
                                {{ $item['amount'] }}
                            </p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('ultraChart').getContext('2d');
        
        // Tạo Gradient xịn hơn
        const gradientStroke = ctx.createLinearGradient(0, 230, 0, 50);
        gradientStroke.addColorStop(1, 'rgba(99, 102, 241, 0.2)');
        gradientStroke.addColorStop(0.2, 'rgba(99, 102, 241, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
                datasets: [{
                    label: 'Premium Data',
                    borderColor: '#0f172a', // Màu Slate-900 cực sang
                    borderWidth: 4,
                    pointRadius: 0,
                    pointHoverRadius: 8,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#0f172a',
                    pointBorderWidth: 3,
                    fill: true,
                    backgroundColor: gradientStroke,
                    tension: 0.4,
                    data: [2000, 4500, 2800, 6000, 4800, 7500, 9000]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 12, weight: '600' }, color: '#94a3b8' }
                    },
                    y: {
                        grid: { color: 'rgba(226, 232, 240, 0.5)', borderDash: [5, 5] },
                        ticks: { font: { size: 12, weight: '600' }, color: '#94a3b8', stepSize: 2000 }
                    }
                },
                interaction: { intersect: false, mode: 'index' }
            }
        });
    </script>

</x-layouts.app>