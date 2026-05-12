<x-layouts.app title="Image AI">
    @php
        $latest = $imageStudio['generations'][0] ?? null;
        $randomPrompts = $imageStudio['random_prompts'] ?? [];
    @endphp

    <section class="-m-4 min-h-[calc(100vh-112px)] bg-slate-50 px-4 py-6 text-slate-900 md:-m-6 md:px-6">
        <div class="mx-auto grid w-full max-w-[1640px] gap-6 xl:grid-cols-[500px_minmax(0,1fr)]">
            <aside class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/70">
                <div class="mb-6 flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600 text-xl font-black text-white shadow-lg shadow-blue-200">IMG</div>
                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-slate-900">Image AI Studio</h1>
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-slate-400">Ảnh quảng cáo chuyên nghiệp</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('marketing.images.store') }}" class="space-y-5" id="imageAiForm">
                    @csrf

                    <label class="block">
                        <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-slate-500">Sản phẩm</span>
                        <select name="product_id" class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <option value="">-- Không gắn sản phẩm --</option>
                            @foreach ($imageStudio['products'] as $product)
                                <option value="{{ $product['id'] }}">{{ $product['name'] }}</option>
                            @endforeach
                        </select>
                    </label>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-slate-500">Phong cách</span>
                            <select name="style" id="imageStyleSelect" class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                @foreach ($imageStudio['styles'] as $style)
                                    <option value="{{ $style['value'] }}">{{ $style['label'] }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-slate-500">Tỷ lệ ảnh</span>
                            <select name="aspect_ratio" id="imageAspectSelect" class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                @foreach ($imageStudio['aspects'] as $aspect)
                                    <option value="{{ $aspect['value'] }}">{{ $aspect['label'] }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-slate-500">Provider</span>
                            <select name="provider" class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                @foreach ($imageStudio['providers'] as $provider)
                                    <option value="{{ $provider['value'] }}">{{ $provider['label'] }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-slate-500">Model</span>
                            <select name="model" class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                @foreach ($imageStudio['models'] as $model)
                                    <option value="{{ $model['value'] }}">{{ $model['label'] }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <label class="block">
                        <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-slate-500">Tệp khách hàng</span>
                        <input name="audience" value="Người mua trên TikTok, Facebook, Reels" class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-slate-500">Creative brief</span>
                        <textarea name="prompt" id="imagePrompt" rows="7" placeholder="Để trống hoặc bấm Random concept để AI tự dựng prompt quảng cáo cao cấp..." class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-semibold leading-6 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"></textarea>
                    </label>

                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" id="randomImageButton" class="flex h-14 items-center justify-center rounded-2xl border border-blue-200 bg-blue-50 text-sm font-black uppercase tracking-[0.06em] text-blue-700 transition hover:bg-blue-600 hover:text-white">
                            Random concept
                        </button>
                        <button type="submit" name="random" value="1" class="flex h-14 items-center justify-center rounded-2xl bg-slate-950 text-sm font-black uppercase tracking-[0.06em] text-white shadow-xl shadow-slate-300 transition hover:bg-blue-700">
                            Tạo random
                        </button>
                    </div>

                    <button type="submit" class="flex h-14 w-full items-center justify-center rounded-2xl bg-blue-600 text-sm font-black uppercase tracking-[0.08em] text-white shadow-xl shadow-blue-200 transition hover:bg-blue-700">
                        Tạo ảnh AI chuyên nghiệp
                    </button>
                </form>
            </aside>

            <section class="space-y-6">
                <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/70">
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-700">Visual Preview</p>
                            <h2 class="mt-1 text-2xl font-black text-slate-900">Ảnh mới nhất</h2>
                        </div>
                        @if ($latest && $latest['image'])
                            <a href="{{ $latest['image'] }}" download class="rounded-2xl bg-slate-950 px-4 py-3 text-xs font-black text-white transition hover:bg-blue-700">Tải ảnh</a>
                        @endif
                    </div>

                    @if ($latest && $latest['image'])
                        <div class="grid gap-5 2xl:grid-cols-[minmax(0,1fr)_340px]">
                            <a href="{{ $latest['image'] }}" target="_blank" class="group relative flex min-h-[620px] items-center justify-center overflow-hidden rounded-[24px] border border-slate-200 bg-slate-950 p-4">
                                <img src="{{ $latest['image'] }}" alt="{{ $latest['project'] }}" class="max-h-[760px] w-auto max-w-full rounded-2xl object-contain transition duration-500 group-hover:scale-[1.015]">
                                <div class="absolute inset-x-4 bottom-4 rounded-2xl bg-black/70 p-4 backdrop-blur">
                                    <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-100">{{ $latest['provider'] }} / {{ $latest['aspect'] }}</p>
                                    <p class="mt-2 text-xl font-black text-white">{{ $latest['project'] }}</p>
                                </div>
                            </a>
                            @php
                                $promptPackage = $latest['prompt_package'] ?? null;
                            @endphp
                            <div class="rounded-[24px] border border-slate-200 bg-slate-50 p-5">
                                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Creative brief</p>
                                <p class="mt-3 text-sm font-semibold leading-6 text-slate-700">{{ $latest['prompt'] }}</p>

                                @if ($promptPackage)
                                    <div class="mt-5 rounded-2xl border border-blue-100 bg-white p-4 shadow-sm">
                                        <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-700">Prompt package</p>
                                        <h3 class="mt-2 text-base font-black text-slate-950">{{ $promptPackage['title'] ?? 'Commercial Image Prompt' }}</h3>
                                        <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">{{ $promptPackage['concept'] ?? '' }}</p>

                                        <div class="mt-4 space-y-3 text-xs font-semibold leading-5 text-slate-600">
                                            <p><span class="font-black text-slate-950">Camera:</span> {{ $promptPackage['camera'] ?? '-' }}</p>
                                            <p><span class="font-black text-slate-950">Lighting:</span> {{ $promptPackage['lighting'] ?? '-' }}</p>
                                            <p><span class="font-black text-slate-950">Mood:</span> {{ $promptPackage['mood'] ?? '-' }}</p>
                                        </div>

                                        @if (!empty($promptPackage['colors']))
                                            <div class="mt-4 flex flex-wrap gap-2">
                                                @foreach ($promptPackage['colors'] as $color)
                                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase text-slate-500">{{ $color }}</span>
                                                @endforeach
                                            </div>
                                        @endif

                                        <details class="mt-4 rounded-2xl bg-slate-950 p-4 text-xs text-slate-200">
                                            <summary class="cursor-pointer font-black text-white">View full JSON prompt</summary>
                                            <pre class="mt-3 max-h-72 overflow-auto whitespace-pre-wrap break-words text-[11px] leading-5">{{ json_encode($promptPackage, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                                        </details>
                                    </div>
                                @elseif (!empty($latest['optimized_prompt']))
                                    <details class="mt-5 rounded-2xl bg-slate-950 p-4 text-xs text-slate-200">
                                        <summary class="cursor-pointer font-black text-white">View optimized prompt</summary>
                                        <p class="mt-3 leading-5">{{ $latest['optimized_prompt'] }}</p>
                                    </details>
                                @endif

                                <div class="mt-5 grid grid-cols-2 gap-3 text-xs font-black">
                                    <div class="rounded-2xl bg-white p-3 text-slate-500 shadow-sm">Size<br><span class="text-slate-900">{{ $latest['size'] }}</span></div>
                                    <div class="rounded-2xl bg-white p-3 text-slate-500 shadow-sm">Source<br><span class="text-slate-900">{{ $latest['source'] ?: 'provider' }}</span></div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="flex min-h-[520px] items-center justify-center rounded-[24px] border border-dashed border-slate-200 bg-slate-50 text-center">
                            <div>
                                <p class="text-6xl font-black text-slate-200">AI</p>
                                <p class="mt-3 text-sm font-semibold text-slate-400">Chưa có ảnh. Bấm Random concept hoặc nhập brief để tạo ảnh mới.</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/70">
                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="text-xl font-black text-slate-900">Thư viện ảnh AI</h2>
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700">{{ count($imageStudio['generations']) }} ảnh</span>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
                        @forelse ($imageStudio['generations'] as $generation)
                            <article class="overflow-hidden rounded-[24px] border border-slate-200 bg-slate-50">
                                @if ($generation['image'])
                                    <a href="{{ $generation['image'] }}" target="_blank" class="flex h-72 items-center justify-center bg-slate-950 p-2">
                                        <img src="{{ $generation['image'] }}" alt="{{ $generation['project'] }}" class="h-full w-full rounded-xl object-contain">
                                    </a>
                                @else
                                    <div class="flex h-72 items-center justify-center bg-slate-100 text-sm font-black text-slate-400">No image</div>
                                @endif
                                <div class="p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="line-clamp-2 text-sm font-black text-slate-900">{{ $generation['project'] }}</p>
                                            <p class="mt-1 text-[11px] font-bold text-slate-400">{{ $generation['created'] }} / {{ $generation['style'] }} / {{ $generation['aspect'] }}</p>
                                        </div>
                                        <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase text-emerald-700">{{ $generation['status'] }}</span>
                                    </div>
                                    <p class="mt-3 line-clamp-3 text-xs font-semibold leading-5 text-slate-500">{{ $generation['prompt'] }}</p>
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        @if ($generation['image'])
                                            <a href="{{ $generation['image'] }}" download class="rounded-2xl bg-slate-900 px-4 py-2 text-xs font-black text-white transition hover:bg-blue-700">Tải ảnh</a>
                                        @endif
                                        <form method="POST" action="{{ route('marketing.images.destroy', $generation['id']) }}" data-confirm="Xóa hình ảnh AI này?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-2xl bg-rose-50 px-4 py-2 text-xs font-black text-rose-600 transition hover:bg-rose-100">Xóa</button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <p class="rounded-3xl border border-dashed border-slate-200 p-10 text-center text-sm font-semibold text-slate-400 md:col-span-2 2xl:col-span-3">Chưa có hình ảnh AI.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    </section>

    <script>
        (() => {
            const prompt = document.getElementById('imagePrompt');
            const style = document.getElementById('imageStyleSelect');
            const aspect = document.getElementById('imageAspectSelect');
            const randomButton = document.getElementById('randomImageButton');
            const prompts = @json($randomPrompts);
            const styles = ['premium_packshot', 'luxury_editorial', 'clean_studio', 'lifestyle_ad', 'social_viral', 'award_campaign', 'cinematic'];
            const aspects = ['9:16', '16:9', '1:1', '4:5'];
            const pick = (items) => items[Math.floor(Math.random() * items.length)] || '';

            randomButton?.addEventListener('click', () => {
                prompt.value = pick(prompts);
                if (style) style.value = pick(styles);
                if (aspect) aspect.value = pick(aspects);
            });
        })();
    </script>
</x-layouts.app>
