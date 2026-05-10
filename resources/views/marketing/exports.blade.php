<x-layouts.app title="Export Manager">
    <x-marketing.studio-layout active="exports" title="Export Manager" eyebrow="MP4 Delivery Center">
        <div class="grid gap-4 md:grid-cols-3">
            @foreach ($exportManager['formats'] as $format)
                <div class="rounded-3xl border border-white/10 bg-white/[0.07] p-5">
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-blue-200">{{ $format['label'] }}</p>
                    <p class="mt-3 text-2xl font-black text-white">{{ $format['aspect'] }}</p>
                    <p class="mt-1 text-sm font-semibold text-slate-400">{{ $format['resolution'] }} MP4</p>
                </div>
            @endforeach
        </div>

        <x-marketing.glass-card class="mt-5" title="Video exports" subtitle="Tất cả video xuất ra phải là MP4 thật, không dùng text payload làm file tải.">
            <div class="grid gap-4">
                @forelse ($exportManager['exports'] as $export)
                    <div class="flex flex-col gap-4 rounded-3xl border border-white/10 bg-slate-950/50 p-5 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-sm font-black text-white">{{ $export['project'] }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-400">{{ $export['format'] }} / {{ $export['aspect'] }} / {{ $export['resolution'] }}</p>
                            <p class="mt-1 max-w-3xl break-all text-xs font-semibold text-slate-500">{{ $export['path'] ?: 'File path đang chờ render' }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="rounded-full bg-blue-500/15 px-3 py-1 text-xs font-black uppercase text-blue-200">{{ $export['status'] }}</span>
                            @if ($export['status'] === 'ready' && $export['path'])
                                <a href="{{ route('marketing.exports.download', $export['id']) }}" class="rounded-2xl bg-white/10 px-4 py-2 text-xs font-black text-white transition hover:bg-white/15">Tải MP4</a>
                            @else
                                <button type="button" disabled class="rounded-2xl bg-white/5 px-4 py-2 text-xs font-black text-slate-500">Tải MP4</button>
                            @endif
                            <form method="POST" action="{{ route('marketing.exports.destroy', $export['id']) }}" onsubmit="return confirm('Xóa file MP4 export này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-2xl bg-rose-500/15 px-4 py-2 text-xs font-black text-rose-100 transition hover:bg-rose-500 hover:text-white">
                                    Xóa
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="rounded-3xl border border-dashed border-white/10 p-10 text-center text-sm font-semibold text-slate-500">Chưa có video export.</p>
                @endforelse
            </div>
        </x-marketing.glass-card>
    </x-marketing.studio-layout>
</x-layouts.app>
