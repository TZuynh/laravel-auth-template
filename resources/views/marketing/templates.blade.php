<x-layouts.app title="Template Manager">
    <x-marketing.studio-layout active="templates" title="Template Manager" eyebrow="Prompt System Library">
        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_420px]">
            <x-marketing.glass-card title="AI templates" subtitle="Template điều khiển kịch bản, prompt hình ảnh, prompt video, voice-over và scene structure.">
                <div class="grid gap-4 md:grid-cols-2">
                    @forelse ($templateManager['templates'] as $template)
                        <article class="rounded-3xl border border-white/10 bg-slate-950/50 p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-base font-black text-white">{{ $template['name'] }}</h3>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">{{ $template['slug'] }}</p>
                                </div>
                                <span class="rounded-full {{ $template['active'] ? 'bg-emerald-500/15 text-emerald-200' : 'bg-slate-500/15 text-slate-300' }} px-3 py-1 text-[10px] font-black uppercase">
                                    {{ $template['active'] ? 'active' : 'off' }}
                                </span>
                            </div>
                            <div class="mt-5 grid grid-cols-3 gap-2 text-xs font-bold text-slate-300">
                                <span class="rounded-xl bg-white/10 px-3 py-2">{{ $template['language'] }}</span>
                                <span class="rounded-xl bg-white/10 px-3 py-2">{{ $template['tone'] }}</span>
                                <span class="rounded-xl bg-white/10 px-3 py-2">{{ $template['platform'] }}</span>
                            </div>
                        </article>
                    @empty
                        <p class="rounded-3xl border border-dashed border-white/10 p-10 text-center text-sm font-semibold text-slate-500 md:col-span-2">Chưa có template. Chạy seeder để nạp template mặc định.</p>
                    @endforelse
                </div>
            </x-marketing.glass-card>

            <div class="space-y-5">
                <x-marketing.glass-card title="Voice profiles" subtitle="ElevenLabs / AI voice presets.">
                    <div class="space-y-3">
                        @foreach ($templateManager['voices'] as $voice)
                            <div class="rounded-2xl bg-slate-950/50 p-4">
                                <p class="text-sm font-black text-white">{{ $voice['name'] }}</p>
                                <p class="mt-1 text-xs font-semibold text-slate-400">{{ $voice['language'] }} / {{ $voice['gender'] }} / {{ $voice['tone'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </x-marketing.glass-card>

                <x-marketing.glass-card title="Music & transitions" subtitle="Nhạc nền và chuyển cảnh cinematic.">
                    <div class="space-y-3">
                        @foreach ($templateManager['music'] as $track)
                            <div class="rounded-2xl bg-slate-950/50 p-4 text-sm font-semibold text-slate-300">{{ $track['title'] }} / {{ $track['mood'] }} / {{ $track['bpm'] }} BPM</div>
                        @endforeach
                        @foreach ($templateManager['transitions'] as $transition)
                            <div class="rounded-2xl bg-violet-500/10 p-4 text-sm font-semibold text-violet-100">{{ $transition['name'] }} / {{ $transition['type'] }} / {{ $transition['remotion_component'] }}</div>
                        @endforeach
                    </div>
                </x-marketing.glass-card>
            </div>
        </div>
    </x-marketing.studio-layout>
</x-layouts.app>
