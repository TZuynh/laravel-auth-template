<x-layouts.app title="AI Image Generator">
    <x-marketing.studio-layout active="images" title="Tạo hình ảnh AI" eyebrow="Marketing & Content Visual Lab">
        <div class="grid gap-5 xl:grid-cols-[420px_minmax(0,1fr)]">
            <x-marketing.glass-card title="AI Image Director" subtitle="Tạo ảnh quảng cáo sản phẩm theo style cinematic, luxury, clean studio hoặc viral social.">
                <form method="POST" action="{{ route('marketing.images.store') }}" class="space-y-4">
                    @csrf

                    <label class="block">
                        <span class="mb-2 block text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Sản phẩm</span>
                        <select name="product_id" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm font-black text-white outline-none transition focus:border-fuchsia-400 focus:ring-4 focus:ring-fuchsia-500/10">
                            <option value="">-- Không gắn sản phẩm --</option>
                            @foreach ($imageStudio['products'] as $product)
                                <option value="{{ $product['id'] }}">{{ $product['name'] }}</option>
                            @endforeach
                        </select>
                    </label>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="mb-2 block text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Style</span>
                            <select name="style" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm font-black text-white outline-none transition focus:border-fuchsia-400 focus:ring-4 focus:ring-fuchsia-500/10">
                                @foreach ($imageStudio['styles'] as $style)
                                    <option value="{{ $style['value'] }}">{{ $style['label'] }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Khung hình</span>
                            <select name="aspect_ratio" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm font-black text-white outline-none transition focus:border-fuchsia-400 focus:ring-4 focus:ring-fuchsia-500/10">
                                @foreach ($imageStudio['aspects'] as $aspect)
                                    <option value="{{ $aspect['value'] }}">{{ $aspect['label'] }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="mb-2 block text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Provider</span>
                            <select name="provider" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm font-black text-white outline-none transition focus:border-fuchsia-400 focus:ring-4 focus:ring-fuchsia-500/10">
                                @foreach ($imageStudio['providers'] as $provider)
                                    <option value="{{ $provider['value'] }}">{{ $provider['label'] }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Model</span>
                            <select name="model" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm font-black text-white outline-none transition focus:border-fuchsia-400 focus:ring-4 focus:ring-fuchsia-500/10">
                                @foreach ($imageStudio['models'] as $model)
                                    <option value="{{ $model['value'] }}">{{ $model['label'] }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <label class="block">
                        <span class="mb-2 block text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Tệp khách hàng</span>
                        <input name="audience" value="Người mua trên TikTok, Facebook, Reels" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm font-semibold text-white outline-none transition placeholder:text-slate-500 focus:border-fuchsia-400 focus:ring-4 focus:ring-fuchsia-500/10">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Prompt</span>
                        <textarea name="prompt" rows="6" required placeholder="VD: packshot sản phẩm đặt trên nền kính đen, ánh sáng viền tím, cảm giác cao cấp, có khoảng trống cho headline..." class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm font-semibold leading-6 text-white outline-none transition placeholder:text-slate-500 focus:border-fuchsia-400 focus:ring-4 focus:ring-fuchsia-500/10"></textarea>
                    </label>

                    <button type="submit" class="flex h-12 w-full items-center justify-center rounded-2xl bg-gradient-to-r from-fuchsia-500 to-blue-500 text-sm font-black text-white shadow-xl shadow-fuchsia-950/40 transition hover:scale-[1.01] hover:shadow-fuchsia-700/30">
                        Tạo hình ảnh AI
                    </button>
                </form>
            </x-marketing.glass-card>

            <div class="space-y-5">
                <x-marketing.glass-card title="Visual preview" subtitle="Ảnh mới nhất sẽ nằm đầu danh sách, có thể tải hoặc xóa trực tiếp.">
                    @php $latest = $imageStudio['generations'][0] ?? null; @endphp
                    @if ($latest && $latest['image'])
                        <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_280px]">
                            <a href="{{ $latest['image'] }}" target="_blank" class="group relative overflow-hidden rounded-3xl border border-white/10 bg-black">
                                <img src="{{ $latest['image'] }}" alt="{{ $latest['project'] }}" class="h-[520px] w-full object-cover transition duration-500 group-hover:scale-[1.02]">
                                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black via-black/55 to-transparent p-5">
                                    <p class="text-xs font-black uppercase tracking-[0.22em] text-fuchsia-200">{{ $latest['provider'] }} / {{ $latest['aspect'] }}</p>
                                    <p class="mt-2 text-xl font-black text-white">{{ $latest['project'] }}</p>
                                </div>
                            </a>
                            <div class="rounded-3xl border border-white/10 bg-slate-950/50 p-5">
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Prompt</p>
                                <p class="mt-3 text-sm font-semibold leading-6 text-slate-200">{{ $latest['prompt'] }}</p>
                                <div class="mt-5 grid grid-cols-2 gap-3 text-xs font-black">
                                    <div class="rounded-2xl bg-white/5 p-3 text-slate-300">Size<br><span class="text-white">{{ $latest['size'] }}</span></div>
                                    <div class="rounded-2xl bg-white/5 p-3 text-slate-300">Source<br><span class="text-white">{{ $latest['source'] ?: 'provider' }}</span></div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="flex min-h-[360px] items-center justify-center rounded-3xl border border-dashed border-white/10 bg-slate-950/40 text-center">
                            <div>
                                <p class="text-5xl font-black text-white/10">AI</p>
                                <p class="mt-3 text-sm font-semibold text-slate-500">Chưa có hình ảnh nào. Nhập prompt và bấm tạo ảnh.</p>
                            </div>
                        </div>
                    @endif
                </x-marketing.glass-card>

                <x-marketing.glass-card title="Lịch sử hình ảnh" subtitle="Quản lý các ảnh AI đã tạo cho marketing và nội dung.">
                    <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
                        @forelse ($imageStudio['generations'] as $generation)
                            <div class="overflow-hidden rounded-3xl border border-white/10 bg-slate-950/50">
                                @if ($generation['image'])
                                    <a href="{{ $generation['image'] }}" target="_blank" class="block bg-black">
                                        <img src="{{ $generation['image'] }}" alt="{{ $generation['project'] }}" class="h-64 w-full object-cover">
                                    </a>
                                @else
                                    <div class="flex h-64 items-center justify-center bg-black/60 text-sm font-black text-slate-500">No image</div>
                                @endif
                                <div class="p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="line-clamp-2 text-sm font-black text-white">{{ $generation['project'] }}</p>
                                            <p class="mt-1 text-[11px] font-bold text-slate-500">{{ $generation['created'] }} / {{ $generation['style'] }} / {{ $generation['aspect'] }}</p>
                                        </div>
                                        <span class="shrink-0 rounded-full bg-emerald-500/15 px-3 py-1 text-[10px] font-black uppercase text-emerald-200">{{ $generation['status'] }}</span>
                                    </div>
                                    <p class="mt-3 line-clamp-3 text-xs font-semibold leading-5 text-slate-400">{{ $generation['prompt'] }}</p>
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        @if ($generation['image'])
                                            <a href="{{ $generation['image'] }}" download class="rounded-2xl bg-white/10 px-4 py-2 text-xs font-black text-white transition hover:bg-white/15">Tải ảnh</a>
                                        @endif
                                        <form method="POST" action="{{ route('marketing.images.destroy', $generation['id']) }}" onsubmit="return confirm('Xóa hình ảnh AI này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-2xl bg-rose-500/15 px-4 py-2 text-xs font-black text-rose-100 transition hover:bg-rose-500 hover:text-white">Xóa</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="rounded-3xl border border-dashed border-white/10 p-10 text-center text-sm font-semibold text-slate-500 md:col-span-2 2xl:col-span-3">Chưa có hình ảnh AI.</p>
                        @endforelse
                    </div>
                </x-marketing.glass-card>
            </div>
        </div>
    </x-marketing.studio-layout>
</x-layouts.app>
