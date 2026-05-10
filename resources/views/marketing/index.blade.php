<x-layouts.app title="AI Director">
    <x-marketing.studio-layout active="director" title="AI Director Dashboard">
        <div class="grid gap-4 md:grid-cols-4">
            @foreach ($dashboard['metrics'] as $metric)
                <div class="rounded-3xl border border-white/10 bg-white/[0.07] p-5 shadow-xl shadow-black/20">
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">{{ $metric['label'] }}</p>
                    <div class="mt-4 flex items-end justify-between">
                        <span class="text-4xl font-black text-white">{{ $metric['value'] }}</span>
                        <span class="rounded-full bg-blue-500/15 px-3 py-1 text-[10px] font-black text-blue-200">{{ $metric['hint'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-5 grid gap-5 xl:grid-cols-[420px_minmax(0,1fr)]">
            <x-marketing.glass-card title="Đạo diễn AI" subtitle="Điều khiển prompt, nhân vật, camera, giọng đọc và nhạc nền trước khi render.">
                <form method="POST" action="{{ route('marketing.projects.store') }}" class="grid gap-4">
                    @csrf
                    <x-marketing.control-field label="Sản phẩm" name="product_id" :options="$studio['products']" placeholder="Chọn sản phẩm để gắn vào video" />
                    <x-marketing.control-field label="AI Model" name="ai_model" :options="$studio['aiModels']" />

                    <div class="grid grid-cols-2 gap-3">
                        <x-marketing.control-field label="Video style" name="style" :options="$studio['visualStyles']" />
                        <x-marketing.control-field label="Thời lượng" name="duration" :options="$studio['durations']" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <x-marketing.control-field label="Camera movement" name="camera" :options="$studio['cameraMoves']" />
                        <x-marketing.control-field label="Khung hình" name="aspect_ratio" :options="$studio['frames']" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <x-marketing.control-field label="Nhân vật" name="character" :options="$studio['characters']" />
                        <x-marketing.control-field label="Giới tính" name="gender" :options="$studio['genders']" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <x-marketing.control-field label="Giọng đọc" name="voice" :options="$studio['voices']" />
                        <x-marketing.control-field label="Nhạc nền" name="music" :options="$studio['music']" />
                    </div>

                    <x-marketing.control-field type="textarea" label="Prompt marketing" name="prompt" placeholder="Ví dụ: Tạo video luxury TikTok cho sản phẩm, mở bằng hook mạnh, ánh sáng studio tối, camera dolly-in, CTA rõ." />

                    <div class="grid grid-cols-3 gap-3">
                        <button type="button" class="rounded-2xl bg-white/10 px-4 py-3 text-xs font-black text-white transition hover:bg-white/15">Gợi ý script</button>
                        <button type="submit" name="intent" value="scenes" class="rounded-2xl bg-blue-500 px-4 py-3 text-xs font-black text-white shadow-lg shadow-blue-500/25 transition hover:bg-blue-400">Tạo cảnh</button>
                        <button type="submit" name="intent" value="render" class="rounded-2xl bg-gradient-to-r from-fuchsia-500 to-violet-500 px-4 py-3 text-xs font-black text-white shadow-lg shadow-fuchsia-500/25 transition hover:from-fuchsia-400 hover:to-violet-400">Render MP4</button>
                    </div>
                </form>
            </x-marketing.glass-card>

            <div class="space-y-5">
                <div class="cinema-player-glow relative min-h-[420px] overflow-hidden rounded-[2rem] border border-white/10 bg-black">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_38%,rgba(147,51,234,.32),transparent_22rem)]"></div>
                    <div class="absolute inset-0 opacity-60" style="background: linear-gradient(90deg, rgba(15,23,42,.96), rgba(15,23,42,.25), rgba(15,23,42,.96));"></div>
                    <div class="cinema-float absolute left-1/2 top-1/2 h-56 w-44 -translate-x-1/2 -translate-y-1/2 rounded-[2rem] border border-white/15 bg-gradient-to-br from-amber-200 via-orange-500 to-violet-950 shadow-2xl shadow-violet-900/50">
                        <div class="absolute inset-x-6 top-8 h-28 rounded-full bg-white/20 blur-xl"></div>
                        <div class="absolute bottom-8 left-1/2 h-20 w-28 -translate-x-1/2 rounded-full bg-black/25 blur-lg"></div>
                    </div>
                    <div class="absolute bottom-8 left-8 right-8">
                        <p class="text-[11px] font-black uppercase tracking-[0.5em] text-blue-200/80">Generated cinematic preview</p>
                        <h2 class="mt-3 max-w-3xl text-3xl font-black uppercase tracking-[0.18em] text-white">Luxury product reveal with real camera motion</h2>
                        <p class="mt-3 max-w-xl text-sm font-semibold leading-6 text-slate-300">Không dùng slideshow phẳng: bố cục có depth, parallax, glow, film grain, kinetic type và pacing cho social ads.</p>
                    </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-4">
                    @foreach ($dashboard['pipeline'] as $step)
                        <div class="rounded-3xl border border-white/10 bg-white/[0.06] p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-white">{{ $step['label'] }}</span>
                                <span class="text-[10px] font-black uppercase text-blue-200">{{ $step['status'] }}</span>
                            </div>
                            <div class="mt-4 h-2 overflow-hidden rounded-full bg-white/10">
                                <div class="h-full rounded-full bg-gradient-to-r from-blue-400 to-violet-400" style="width: {{ $step['progress'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,1fr)_420px]">
            <x-marketing.glass-card title="Scene timeline" subtitle="Bốn cảnh điện ảnh được tạo tự động cho video viral ecommerce.">
                <div class="grid gap-4 md:grid-cols-4">
                    @foreach (['Hook opening', 'Product reveal', 'Feature transformation', 'CTA ending'] as $index => $scene)
                        <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-violet-300">Scene {{ $index + 1 }}</p>
                            <h3 class="mt-2 text-sm font-black text-white">{{ $scene }}</h3>
                            <p class="mt-3 text-xs font-semibold leading-5 text-slate-400">Camera motion, voice-over, subtitle và transition được pipeline render xử lý.</p>
                        </div>
                    @endforeach
                </div>
            </x-marketing.glass-card>

            <x-marketing.glass-card title="Dự án gần đây" subtitle="Các project sẽ xuất hiện sau khi migrate và seed database.">
                <div class="space-y-3">
                    @forelse ($dashboard['recentProjects'] as $project)
                        <div class="rounded-2xl bg-slate-950/50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-black text-white">{{ $project['title'] }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-400">{{ $project['product'] }} / {{ $project['aspect'] }}</p>
                                </div>
                                <span class="rounded-full bg-blue-500/15 px-3 py-1 text-[10px] font-black uppercase text-blue-200">{{ $project['status'] }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-2xl border border-dashed border-white/10 p-5 text-center text-sm font-semibold text-slate-400">Chưa có project video nào.</p>
                    @endforelse
                </div>
            </x-marketing.glass-card>
        </div>
    </x-marketing.studio-layout>
</x-layouts.app>
