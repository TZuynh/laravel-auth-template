<x-layouts.app title="{{ $generation->title }}">
    <x-marketing.studio-layout active="bulk" title="AI Video Studio" eyebrow="Prompt to publish">
        @php
            $version = $generation->versions->sortBy('id')->first();
            $scenes = $version?->videoProject?->scenes?->sortBy('sort_order')->values() ?? collect();
            $pipelineSteps = (array) data_get($version?->videoProject?->settings, 'ai_movie_pipeline.steps', [
                'AI Script Writer',
                'Scene Splitter',
                'Scene Prompt Generator',
                'Asset Finder',
                'Voice Generator',
                'Subtitle Generator',
                'Timeline Composer',
                'Transition Engine',
                'Motion Engine',
                'Remotion Timeline Render',
                'FFmpeg Final Encode',
            ]);
            $hasRunnable = $generation->versions->contains(fn ($item) => in_array($item->status, ['queued', 'processing', 'assets_ready'], true));
            $hasSyncable = $generation->versions->contains(fn ($item) => in_array($item->status, ['queued', 'rendering'], true) && $item->renderJob && !$item->renderJob->status?->isTerminal());
            $canCancel = !in_array($generation->status, ['completed', 'failed', 'cancelled'], true);
            $renderStep = $version?->renderJob?->current_step ?: 'Waiting queue worker';
        @endphp

        <div class="mb-5 grid gap-4 lg:grid-cols-4">
            <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Status</p>
                <p class="mt-2 text-3xl font-black text-white">{{ $generation->status }}</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Progress</p>
                <p class="mt-2 text-3xl font-black text-white">{{ $version?->progress ?? 0 }}%</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Renderer</p>
                <p class="mt-2 text-3xl font-black text-white">{{ $generation->render_provider }}</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Completed</p>
                <p class="mt-2 text-3xl font-black text-white">{{ $generation->completed_versions }}/{{ $generation->requested_versions }}</p>
            </div>
        </div>

        <div class="grid gap-5 xl:grid-cols-[380px_minmax(0,1fr)]">
            <aside class="rounded-3xl border border-white/10 bg-slate-950/70 p-5 shadow-2xl shadow-black/25">
                <h2 class="text-lg font-black text-white">AI Director</h2>
                <p class="mt-2 text-xs font-semibold leading-5 text-slate-400">Kiểm soát tiến trình dựng video từ prompt, cảnh, voice, nhạc đến xuất MP4.</p>

                <div class="mt-4 space-y-3 rounded-2xl border border-white/10 bg-black/30 p-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Prompt</p>
                        <p class="mt-2 text-sm font-semibold leading-6 text-white">{{ $generation->prompt }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Voice</p>
                            <p class="mt-1 text-sm font-black text-white">{{ $version?->voice ?: 'none' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Music</p>
                            <p class="mt-1 text-sm font-black text-white">{{ $version?->music ?: 'none' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Frame</p>
                            <p class="mt-1 text-sm font-black text-white">{{ $version?->aspect_ratio ?: $generation->aspect_ratio }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Step</p>
                            <p class="mt-1 text-sm font-bold text-blue-200">{{ $renderStep }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    @if($hasRunnable)
                        <form method="POST" action="{{ route('marketing.bulk-video.run-now', $generation) }}">
                            @csrf
                            <button type="submit" class="w-full rounded-xl bg-blue-500 px-3 py-3 text-xs font-black text-white transition hover:bg-blue-400">Run Now</button>
                        </form>
                    @endif

                    @if($hasSyncable)
                        <form method="POST" action="{{ route('marketing.bulk-video.sync', $generation) }}">
                            @csrf
                            <button type="submit" class="w-full rounded-xl bg-cyan-500/20 px-3 py-3 text-xs font-black text-cyan-100 transition hover:bg-cyan-500/30">Sync Render</button>
                        </form>
                    @endif

                    @if($canCancel)
                        <form method="POST" action="{{ route('marketing.bulk-video.cancel', $generation) }}" data-confirm="Cancel this video generation?">
                            @csrf
                            <button type="submit" class="w-full rounded-xl bg-amber-500/20 px-3 py-3 text-xs font-black text-amber-100 transition hover:bg-amber-500/30">Cancel</button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('marketing.bulk-video.destroy', $generation) }}" data-confirm="Delete this generation and its project?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full rounded-xl bg-rose-500/20 px-3 py-3 text-xs font-black text-rose-100 transition hover:bg-rose-500/30">Delete</button>
                    </form>
                </div>

                <div class="mt-4 rounded-2xl border border-white/10 bg-black/30 p-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">AI Movie Pipeline</p>
                    <div class="mt-3 space-y-2">
                        @foreach($pipelineSteps as $index => $step)
                            <div class="flex items-center gap-2 text-xs font-bold text-slate-300">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-500/20 text-[10px] text-blue-100">{{ $index + 1 }}</span>
                                <span>{{ $step }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-white/10 bg-black/30 p-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Magic Edit Box</p>
                    <p class="mt-2 text-xs font-semibold leading-5 text-slate-400">Tạo bản mới tại trang AI Video với lệnh như: "đổi voice male_north", "rút scene 2 còn 2s", "đổi transition fade".</p>
                    <a href="{{ route('marketing.bulk-video.index') }}" class="mt-3 inline-flex rounded-lg bg-white/10 px-3 py-2 text-xs font-black text-white transition hover:bg-white/15">
                        Open AI Video Builder
                    </a>
                </div>
            </aside>

            <section class="space-y-5">
                <div class="rounded-3xl border border-white/10 bg-slate-950/70 p-5 shadow-2xl shadow-black/25">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-200">Preview</p>
                            <h3 class="mt-2 text-2xl font-black text-white">{{ $version?->title ?: $generation->title }}</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($version?->output_url)
                                <a href="{{ $version->output_url }}" target="_blank" class="rounded-xl bg-violet-500/80 px-3 py-2 text-[10px] font-black uppercase tracking-[0.12em] text-white transition hover:bg-violet-400">
                                    Export Final Video
                                </a>
                            @endif
                            <span class="rounded-full bg-white/10 px-3 py-1 text-[10px] font-black uppercase text-slate-200">{{ $version?->status ?: 'queued' }}</span>
                        </div>
                    </div>

                    <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-black">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_48%_20%,rgba(59,130,246,.35),transparent_20rem)]"></div>
                        @if($version?->output_url)
                            <video controls class="relative z-10 aspect-video w-full bg-black">
                                <source src="{{ $version->output_url }}" type="video/mp4">
                            </video>
                        @else
                            <div class="relative z-10 flex aspect-video items-center justify-center text-center">
                                <div>
                                    <p class="text-sm font-black uppercase tracking-[0.24em] text-slate-400">Studio Preview</p>
                                    <p class="mt-2 text-xs font-semibold text-slate-500">Render đang xử lý. Bấm Sync Render nếu tiến trình đứng lâu.</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4">
                        <div class="mb-2 flex items-center justify-between text-xs font-bold text-slate-400">
                            <span>Render progress</span>
                            <span>{{ $version?->progress ?? 0 }}%</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-white/10">
                            <div class="h-full rounded-full bg-gradient-to-r from-blue-400 to-violet-400 transition-all" style="width: {{ max(0, min(100, (int) ($version?->progress ?? 0))) }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-white/10 bg-white/[0.06] p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-black text-white">Scene Timeline</h3>
                        <span class="text-xs font-semibold text-slate-400">{{ $scenes->count() }} scenes</span>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        @foreach($scenes as $scene)
                            @php
                                $meta = $scene->metadata ?? [];
                            @endphp
                            <article class="rounded-2xl border border-white/10 bg-slate-950/60 p-4">
                                <div class="flex items-center justify-between">
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-200">{{ data_get($meta, 'scene_type', 'scene') }} {{ $scene->sort_order }}</p>
                                    <p class="text-[10px] font-black text-slate-400">{{ number_format((float) $scene->duration_seconds, 1) }}s</p>
                                </div>
                                <h4 class="mt-3 text-sm font-black text-white">{{ $scene->title }}</h4>
                                <p class="mt-2 line-clamp-4 text-xs font-semibold leading-5 text-slate-400">{{ $scene->subtitle_text ?: $scene->voice_over_text }}</p>
                                <div class="mt-3 grid grid-cols-2 gap-2 text-[10px] font-bold text-slate-400">
                                    <span class="rounded-lg bg-white/5 px-2 py-1">Camera: {{ $scene->camera_movement }}</span>
                                    <span class="rounded-lg bg-white/5 px-2 py-1">Shot: {{ data_get($meta, 'shot_type', '-') }}</span>
                                    <span class="rounded-lg bg-white/5 px-2 py-1">Transition: {{ data_get($meta, 'transition_type', '-') }}</span>
                                    <span class="rounded-lg bg-white/5 px-2 py-1">SFX: {{ data_get($meta, 'sound_effect', '-') }}</span>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    @if($version?->timeline_json)
                        <details class="mt-4 rounded-2xl border border-white/10 bg-black/35 p-3">
                            <summary class="cursor-pointer text-xs font-black uppercase tracking-[0.18em] text-slate-300">Timeline JSON</summary>
                            <pre class="mt-3 max-h-72 overflow-auto rounded-xl bg-black/60 p-3 text-[10px] leading-5 text-slate-300">{{ json_encode($version->timeline_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        </details>
                    @endif
                </div>
            </section>
        </div>
    </x-marketing.studio-layout>

    @if(in_array($generation->status, ['queued', 'processing'], true))
        <script>
            setTimeout(() => window.location.reload(), 15000);
        </script>
    @endif
</x-layouts.app>
