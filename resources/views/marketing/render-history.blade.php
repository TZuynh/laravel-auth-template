<x-layouts.app title="Render History">
    <x-marketing.studio-layout active="renders" title="Render History" eyebrow="Redis Queue Monitor">
        <div class="grid gap-4 md:grid-cols-3">
            @foreach ($renderHistory['queueStats'] as $stat)
                <div class="rounded-3xl border border-white/10 bg-white/[0.07] p-5">
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">{{ $stat['label'] }}</p>
                    <p class="mt-3 text-4xl font-black text-white">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>

        <x-marketing.glass-card class="mt-5" title="Lịch sử render" subtitle="Theo dõi job AI, FFmpeg, retry và tiến độ xuất video.">
            <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-blue-200">Render mode</p>
                    <p class="mt-1 text-sm font-semibold text-slate-400">
                        {{ config('ai_video.queue.mode') === 'sync' ? 'Local sync fallback: bấm render là chạy ngay, không bị kẹt queued.' : 'Queue worker: cần worker Redis/database đang chạy.' }}
                    </p>
                </div>

                <form method="POST" action="{{ route('marketing.render-history.clear-completed') }}" onsubmit="return confirm('Xóa tất cả job đã hoàn tất/lỗi và file MP4 liên quan?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-2xl bg-rose-500/90 px-4 py-3 text-xs font-black text-white shadow-lg shadow-rose-950/30 transition hover:bg-rose-400">
                        Xóa job cũ
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto rounded-3xl border border-white/10">
                <table class="w-full min-w-[1100px] text-left">
                    <thead class="bg-white/5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                        <tr>
                            <th class="px-5 py-4">Project</th>
                            <th class="px-5 py-4">Type</th>
                            <th class="px-5 py-4">Provider</th>
                            <th class="px-5 py-4">Step</th>
                            <th class="px-5 py-4">Progress</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10 text-sm font-semibold text-slate-300">
                        @forelse ($renderHistory['jobs'] as $job)
                            @php
                                $statusClass = match ($job['status']) {
                                    'completed' => 'bg-emerald-500/15 text-emerald-200',
                                    'failed' => 'bg-rose-500/15 text-rose-200',
                                    'queued' => 'bg-amber-500/15 text-amber-100',
                                    default => 'bg-blue-500/15 text-blue-100',
                                };
                            @endphp
                            <tr>
                                <td class="px-5 py-4">
                                    <div class="max-w-[280px] text-white">{{ $job['project'] }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $job['created'] }}</div>
                                </td>
                                <td class="px-5 py-4">{{ $job['type'] }}</td>
                                <td class="px-5 py-4">{{ $job['provider'] }}</td>
                                <td class="px-5 py-4">
                                    <div>{{ $job['step'] }}</div>
                                    @if (!empty($job['error']))
                                        <div class="mt-1 max-w-xl text-xs font-semibold leading-5 text-rose-300">{{ $job['error'] }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="h-2 w-36 overflow-hidden rounded-full bg-white/10">
                                        <div class="h-full rounded-full bg-blue-400" style="width: {{ $job['progress'] }}%"></div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="{{ $statusClass }} rounded-full px-3 py-1 text-xs font-black uppercase">{{ $job['status'] }}</span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <form method="POST" action="{{ route('marketing.render-history.destroy', $job['id']) }}" onsubmit="return confirm('Xóa render job này và file MP4 liên quan?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-2xl bg-white/10 px-4 py-2 text-xs font-black text-rose-100 transition hover:bg-rose-500/80 hover:text-white">
                                            Xóa
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-slate-500">Chưa có render job nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-marketing.glass-card>
    </x-marketing.studio-layout>
</x-layouts.app>
