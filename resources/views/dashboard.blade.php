<x-layouts.app :title="__('Monthly Sales')">
    
    {{-- 1. CSS CUSTOM & ÉP NỀN TỐI --}}
    <style>
        /* Ép nền của phần main/body trong layout của bạn thành màu tối để đồng bộ */
        body, main, .bg-gray-50, .bg-gray-100, .bg-white {
            background-color: #1f1d2b !important;
        }
        
        /* Đảm bảo container không bị giới hạn padding hẹp của layout cũ */
        .dash-container {
            background-color: #1f1d2b;
            min-height: 100vh;
            padding: 1.5rem;
            color: #ffffff;
        }

        .dash-card {
            background-color: #252836;
            border: 1px solid #373a4b;
            border-radius: 16px;
            padding: 1.25rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease;
        }
        
        .dash-card:hover {
            transform: translateY(-2px);
        }

        .text-muted { color: #808191; }
        .text-light { color: #ffffff; }
    </style>

    {{-- 2. NỘI DUNG GIAO DIỆN --}}
    <div class="dash-container mx-auto w-full max-w-[1600px] space-y-6 pb-10">
        
        {{-- HEADER --}}
        <header class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-2">
            <div class="flex items-center gap-4">
                <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Monthly Sales
                </h1>
            </div>
            <div class="flex flex-wrap gap-2">
                <button class="px-4 py-2 text-sm font-medium bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl shadow-lg shadow-indigo-500/30 transition">+ Add Block</button>
                <button class="px-4 py-2 text-sm bg-[#252836] border border-[#373a4b] text-gray-300 hover:text-white rounded-xl transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Filter
                </button>
                <button class="px-4 py-2 text-sm bg-[#252836] border border-[#373a4b] text-gray-300 hover:text-white rounded-xl transition">Set Automation</button>
            </div>
        </header>

        {{-- BỐ CỤC GRID CHÍNH (3 Cột) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {{-- CỘT TRÁI (KPIs, Closed Won, Customer Sat) --}}
            <div class="col-span-1 lg:col-span-4 flex flex-col gap-6">
                {{-- Row 1: 2 KPIs --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="dash-card">
                        <p class="text-sm text-muted mb-1 font-medium">New Deals Amount</p>
                        <h2 class="text-2xl xl:text-3xl font-bold text-light mb-3 truncate">$9,125,100</h2>
                        <div class="flex items-end justify-between">
                            <div>
                                <p class="text-[11px] text-muted mb-0.5">YoY growth</p>
                                <p class="text-sm text-emerald-400 font-semibold flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                                    23%
                                </p>
                            </div>
                            <div class="w-16 h-8"><canvas id="miniChart1"></canvas></div>
                        </div>
                    </div>
                    <div class="dash-card">
                        <p class="text-sm text-muted mb-1 font-medium">Deals Won</p>
                        <h2 class="text-2xl xl:text-3xl font-bold text-light mb-3 truncate">34,345</h2>
                        <div class="flex items-end justify-between">
                            <div>
                                <p class="text-[11px] text-muted mb-0.5">YoY growth</p>
                                <p class="text-sm text-rose-400 font-semibold flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                    17%
                                </p>
                            </div>
                            <div class="w-16 h-8"><canvas id="miniChart2"></canvas></div>
                        </div>
                    </div>
                </div>

                {{-- Closed Won Bar Chart --}}
                <div class="dash-card flex-1 min-h-[300px] flex flex-col">
                    <h3 class="text-base font-semibold text-light mb-4">Closed Won</h3>
                    <div class="flex flex-wrap gap-4 text-xs mb-4">
                        <span class="flex items-center gap-1.5 text-muted"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Deals Closed Won</span>
                        <span class="flex items-center gap-1.5 text-muted"><span class="w-2.5 h-2.5 rounded-full bg-green-400"></span> Deals Created</span>
                    </div>
                    <div class="flex-1 relative w-full min-h-[200px]"><canvas id="closedWonChart"></canvas></div>
                </div>

                {{-- Customer Satisfaction Gauge --}}
                <div class="dash-card">
                    <h3 class="text-base font-semibold text-light mb-2">Customer Satisfaction</h3>
                    <div class="relative h-[160px] flex items-center justify-center">
                        <canvas id="satisfactionGauge"></canvas>
                        <div class="absolute text-center mt-12">
                            <p class="text-xs text-muted font-medium tracking-wide">NPS</p>
                            <p class="text-3xl font-bold text-white">48.6</p>
                        </div>
                    </div>
                    <div class="mt-4 text-sm">
                        <table class="w-full text-left text-muted">
                            <thead>
                                <tr class="border-b border-[#373a4b]"><th class="py-2.5 font-medium">Category</th><th class="py-2.5 font-medium text-right">Proportion</th><th class="py-2.5 font-medium text-right">Count</th></tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-[#373a4b]/50"><td class="py-2.5 text-white">Detractors <span class="text-xs text-gray-500 ml-1">1-6</span></td><td class="py-2.5 text-right font-medium">10.5%</td><td class="py-2.5 text-right">11</td></tr>
                                <tr class="border-b border-[#373a4b]/50"><td class="py-2.5 text-white">Passives <span class="text-xs text-gray-500 ml-1">7-8</span></td><td class="py-2.5 text-right font-medium">30.5%</td><td class="py-2.5 text-right">32</td></tr>
                                <tr><td class="py-2.5 text-white">Promoters <span class="text-xs text-gray-500 ml-1">9-10</span></td><td class="py-2.5 text-right font-medium">59%</td><td class="py-2.5 text-right">62</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- CỘT GIỮA (Radar, Line Chart) --}}
            <div class="col-span-1 lg:col-span-5 flex flex-col gap-6">
                {{-- Sales Capability Radar --}}
                <div class="dash-card flex-1 flex flex-col min-h-[400px]">
                    <div class="w-full flex justify-between items-start mb-2">
                        <h3 class="text-base font-semibold text-light">Sales Capability</h3>
                    </div>
                    <div class="flex flex-wrap gap-4 text-xs mb-4">
                        <span class="flex items-center gap-1.5 text-muted"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Team A</span>
                        <span class="flex items-center gap-1.5 text-muted"><span class="w-2.5 h-2.5 rounded-full bg-green-400"></span> Team B</span>
                        <span class="flex items-center gap-1.5 text-muted"><span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span> Team C</span>
                    </div>
                    <div class="flex-1 w-full relative flex justify-center items-center">
                        <div class="w-full max-w-[360px] aspect-square relative">
                            <canvas id="capabilityRadar"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Deals Line Chart --}}
                <div class="dash-card min-h-[350px] flex flex-col">
                    <h3 class="text-base font-semibold text-light mb-4">Deals</h3>
                    <div class="flex flex-wrap gap-4 text-xs mb-4">
                        <span class="flex items-center gap-1.5 text-muted"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Transaction Amount</span>
                        <span class="flex items-center gap-1.5 text-muted"><span class="w-2.5 h-2.5 rounded-full bg-green-400"></span> Revenue</span>
                    </div>
                    <div class="flex-1 relative w-full min-h-[250px]"><canvas id="dealsChart"></canvas></div>
                </div>
            </div>

            {{-- CỘT PHẢI (New Customers, Engagement, Ranking) --}}
            <div class="col-span-1 lg:col-span-3 flex flex-col gap-6">
                {{-- New Customers Bar --}}
                <div class="dash-card border-blue-500/20 bg-gradient-to-b from-[#252836] to-[#1f233b] relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-base font-semibold text-light">New Customers</h3>
                            <button class="bg-blue-500/10 px-3 py-1.5 text-[11px] font-medium rounded-lg text-blue-400 border border-blue-500/20 flex items-center gap-1.5 hover:bg-blue-500/20 transition">
                                ✨ Smart Analysis
                            </button>
                        </div>
                        <div class="h-[140px] relative w-full"><canvas id="newCustomersChart"></canvas></div>
                    </div>
                </div>

                {{-- Account Engagement Donut --}}
                <div class="dash-card">
                    <h3 class="text-base font-semibold text-light mb-4">Account Engagement</h3>
                    <div class="relative h-[220px] flex justify-center items-center">
                        <canvas id="engagementDonut"></canvas>
                        <div class="absolute text-center">
                            <p class="text-3xl font-bold text-white">129</p>
                            <p class="text-xs text-muted mt-1 uppercase tracking-wider">Total</p>
                        </div>
                    </div>
                </div>

                {{-- Sales Ranking --}}
                <div class="dash-card flex-1 flex flex-col">
                    <h3 class="text-base font-semibold text-light mb-8">Sales Ranking</h3>
                    
                    <div class="flex justify-around items-end mb-8 mt-2 px-2">
                        <div class="flex flex-col items-center">
                            <div class="w-14 h-14 rounded-full bg-gray-700 border-2 border-slate-500 mb-3 relative">
                                <img src="https://i.pravatar.cc/150?img=47" class="w-full h-full rounded-full object-cover" alt="User"/>
                                <span class="absolute -bottom-2 -right-1 w-6 h-6 bg-slate-500 text-white text-xs rounded-full flex items-center justify-center font-bold border-2 border-[#252836]">2</span>
                            </div>
                            <p class="text-sm text-light">Amy</p>
                            <p class="text-sm font-bold text-white mt-1">3,010</p>
                        </div>
                        <div class="flex flex-col items-center mb-4">
                            <div class="w-20 h-20 rounded-full bg-yellow-500 border-[3px] border-yellow-400 mb-3 relative shadow-[0_0_20px_rgba(234,179,8,0.3)]">
                                <img src="https://i.pravatar.cc/150?img=32" class="w-full h-full rounded-full object-cover" alt="User"/>
                                <span class="absolute -bottom-2 -right-1 w-7 h-7 bg-yellow-500 text-white text-sm rounded-full flex items-center justify-center font-bold border-2 border-[#252836]">1</span>
                            </div>
                            <p class="text-sm text-light font-medium">Kate Bush</p>
                            <p class="text-lg font-bold text-white mt-1">4,950</p>
                        </div>
                        <div class="flex flex-col items-center">
                            <div class="w-14 h-14 rounded-full bg-amber-700 border-2 border-amber-600 mb-3 relative">
                                <img src="https://i.pravatar.cc/150?img=11" class="w-full h-full rounded-full object-cover" alt="User"/>
                                <span class="absolute -bottom-2 -right-1 w-6 h-6 bg-amber-600 text-white text-xs rounded-full flex items-center justify-center font-bold border-2 border-[#252836]">3</span>
                            </div>
                            <p class="text-sm text-light">Yuge Bai</p>
                            <p class="text-sm font-bold text-white mt-1">2,800</p>
                        </div>
                    </div>

                    <div class="mt-auto bg-[#1f1d2b] p-4 rounded-xl border border-[#373a4b] flex justify-between items-center hover:bg-[#2a2d3e] transition cursor-pointer">
                        <div class="flex items-center gap-4">
                            <span class="text-sm text-muted font-bold w-4 text-center">4</span>
                            <img src="https://i.pravatar.cc/150?img=68" class="w-10 h-10 rounded-full object-cover border border-[#373a4b]" alt="User"/>
                            <span class="text-sm font-medium text-light">Andy Bonillo</span>
                        </div>
                        <span class="text-sm font-bold text-white">2,610</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. JAVASCRIPT & CHART.JS (Nạp trực tiếp vào file này) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cấu hình tổng thể Chart.js
            Chart.defaults.color = '#808191';
            Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
            const gridColor = '#373a4b';

            // Chart 1: Mini Line (New Deals)
            new Chart(document.getElementById('miniChart1'), {
                type: 'line',
                data: { labels: ['1','2','3','4','5','6'], datasets: [{ data: [10, 25, 20, 45, 30, 50], borderColor: '#6366f1', borderWidth: 2, tension: 0.4, pointRadius: 0 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { enabled: false } }, scales: { x: { display: false }, y: { display: false } } }
            });

            // Chart 2: Mini Line (Deals Won)
            new Chart(document.getElementById('miniChart2'), {
                type: 'line',
                data: { labels: ['1','2','3','4','5','6'], datasets: [{ data: [50, 40, 45, 20, 30, 10], borderColor: '#808191', borderWidth: 2, tension: 0.4, pointRadius: 0 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { enabled: false } }, scales: { x: { display: false }, y: { display: false } } }
            });

            // Chart 3: Closed Won (Bar)
            new Chart(document.getElementById('closedWonChart'), {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
                    datasets: [
                        { label: 'Deals Closed Won', data: [650, 400, 420, 480, 410, 430, 800, 400, 420], backgroundColor: '#3b82f6', borderRadius: 4, barPercentage: 0.5, categoryPercentage: 0.8 },
                        { label: 'Deals Created', data: [250, 550, 600, 580, 500, 620, 500, 600, 580], backgroundColor: '#4ade80', borderRadius: 4, barPercentage: 0.5, categoryPercentage: 0.8 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, border: { display: false } },
                        y: { grid: { color: gridColor, drawBorder: false }, border: { display: false }, ticks: { stepSize: 500, padding: 10 } }
                    }
                }
            });

            // Chart 4: Customer Satisfaction (Gauge)
            new Chart(document.getElementById('satisfactionGauge'), {
                type: 'doughnut',
                data: {
                    labels: ['Detractors', 'Passives', 'Promoters'],
                    datasets: [{ data: [10, 30, 60], backgroundColor: ['#3b82f6', '#c084fc', '#4ade80'], borderWidth: 0, cutout: '82%' }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, circumference: 180, rotation: 270,
                    plugins: { legend: { display: false }, tooltip: { enabled: true } }
                }
            });

            // Chart 5: Sales Capability (Radar)
            new Chart(document.getElementById('capabilityRadar'), {
                type: 'radar',
                data: {
                    labels: ['Risk Positioning', 'Resolve Object...', 'Expand Access', 'Account Planni...', 'Proactive Control', 'Target Prospect', 'Collaboration', 'Leverage Insig...'],
                    datasets: [
                        { label: 'Team A', data: [80, 60, 70, 80, 90, 70, 60, 50], borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.15)', borderWidth: 2, tension: 0.3 },
                        { label: 'Team B', data: [60, 90, 60, 50, 40, 80, 90, 70], borderColor: '#4ade80', backgroundColor: 'rgba(74, 222, 128, 0.15)', borderWidth: 2, tension: 0.3 },
                        { label: 'Team C', data: [40, 50, 90, 70, 60, 50, 70, 90], borderColor: '#c084fc', backgroundColor: 'rgba(192, 132, 252, 0.15)', borderWidth: 2, tension: 0.3 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        r: {
                            angleLines: { color: gridColor }, 
                            grid: { color: gridColor }, 
                            pointLabels: { color: '#808191', font: { size: 11 }, padding: 15 }, 
                            ticks: { display: false, max: 100, min: 0 }
                        }
                    }
                }
            });

            // Chart 6: Deals (Multi-line Area)
            const ctxDeals = document.getElementById('dealsChart').getContext('2d');
            const gradientBlue = ctxDeals.createLinearGradient(0, 0, 0, 300);
            gradientBlue.addColorStop(0, 'rgba(59, 130, 246, 0.4)'); gradientBlue.addColorStop(1, 'rgba(59, 130, 246, 0)');
            const gradientGreen = ctxDeals.createLinearGradient(0, 0, 0, 300);
            gradientGreen.addColorStop(0, 'rgba(74, 222, 128, 0.4)'); gradientGreen.addColorStop(1, 'rgba(74, 222, 128, 0)');

            new Chart(ctxDeals, {
                type: 'line',
                data: {
                    labels: ['02.20', '02.21', '02.22', '02.23', '02.24', '02.25', '02.26', '02.27', '02.28'],
                    datasets: [
                        { label: 'Transaction Amount', data: [12000, 10000, 21000, 11000, 14000, 10000, 20000, 13000, 16000], borderColor: '#3b82f6', backgroundColor: gradientBlue, borderWidth: 2, tension: 0.4, fill: true, pointRadius: 0, pointHoverRadius: 6 },
                        { label: 'Revenue', data: [16000, 15000, 14000, 14000, 13000, 16000, 14000, 15000, 16000], borderColor: '#4ade80', backgroundColor: gradientGreen, borderWidth: 2, tension: 0.4, fill: true, pointRadius: 0, pointHoverRadius: 6 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, 
                    plugins: { legend: { display: false } },
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        x: { grid: { display: false }, border: { display: false }, ticks: { padding: 10 } },
                        y: { grid: { color: gridColor, drawBorder: false }, border: { display: false }, ticks: { stepSize: 5000, padding: 10 } }
                    }
                }
            });

            // Chart 7: New Customers (Bar)
            new Chart(document.getElementById('newCustomersChart'), {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
                    datasets: [{ data: [30, 60, 45, 75, 40, 80, 45, 65, 40, 50], backgroundColor: '#38bdf8', borderRadius: 4, barPercentage: 0.6 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, 
                    plugins: { legend: { display: false } },
                    scales: { 
                        x: { grid: { display: false }, border: { display: false }, ticks: { font: { size: 10 } } }, 
                        y: { display: false } 
                    }
                }
            });

            // Chart 8: Account Engagement (Donut)
            new Chart(document.getElementById('engagementDonut'), {
                type: 'doughnut',
                data: {
                    labels: ['Team A', 'Team B', 'Team C', 'Team D'],
                    datasets: [{ data: [40, 25, 20, 15], backgroundColor: ['#3b82f6', '#c084fc', '#f59e0b', '#4ade80'], borderWidth: 0, cutout: '78%' }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
        });
    </script>

</x-layouts.app>