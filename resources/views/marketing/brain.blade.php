<x-layouts.app title="Brain AI">
    @php
        $activeCategory = $brain['active_category'] ?? 'all';
    @endphp

    <section class="-m-4 min-h-[calc(100vh-112px)] bg-slate-50 px-4 py-6 text-slate-900 md:-m-6 md:px-6">
        <div class="mx-auto grid w-full max-w-[1640px] gap-6 xl:grid-cols-[500px_minmax(0,1fr)]">
            <aside class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/70">
                <div class="mb-6 flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-lg font-black text-white shadow-lg shadow-blue-200">AI</div>
                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-slate-900">Brain AI</h1>
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-slate-400">Bộ nhớ thương hiệu</p>
                    </div>
                </div>

                <div class="mb-5 rounded-2xl border border-blue-100 bg-blue-50/70 p-4">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-700">Preset điền nhanh</p>
                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                        @foreach ($brain['quick_starts'] as $preset)
                            <button type="button"
                                    data-brain-preset
                                    data-category="{{ $preset['category'] }}"
                                    data-topic="{{ $preset['topic'] }}"
                                    data-content="{{ $preset['content'] }}"
                                    class="rounded-xl bg-white px-3 py-3 text-left text-xs font-black leading-5 text-slate-700 shadow-sm transition hover:bg-blue-600 hover:text-white">
                                {{ $preset['topic'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <form method="POST" action="{{ route('marketing.brain.store') }}" class="space-y-5">
                    @csrf

                    <label class="block">
                        <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-slate-500">Loại bộ nhớ</span>
                        <select name="category" id="brainCategory" class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            @foreach (array_filter($brain['categories'], fn ($category) => $category['value'] !== 'all') as $category)
                                <option value="{{ $category['value'] }}" @selected($activeCategory === $category['value'])>{{ $category['label'] }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-slate-500">Tiêu đề gợi nhớ</span>
                        <input name="topic" id="brainTopic" placeholder="VD: Cam kết hoàn tiền, Freeship, giọng văn thương hiệu..." class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-slate-500">Nội dung AI cần nhớ</span>
                        <textarea name="content" id="brainContent" rows="8" required placeholder="Nhập quy tắc, USP, insight khách hàng, FAQ hoặc ưu đãi để Content AI và Image AI tự áp dụng..." class="w-full resize-none rounded-2xl border border-blue-500 bg-white px-4 py-4 text-sm font-semibold leading-6 text-slate-800 outline-none ring-4 ring-blue-100 placeholder:text-slate-400"></textarea>
                    </label>

                    <button type="submit" class="flex h-14 w-full items-center justify-center rounded-2xl bg-slate-950 text-sm font-black text-white shadow-xl shadow-slate-300 transition hover:bg-blue-700">
                        Lưu vào bộ nhớ AI
                    </button>
                </form>
            </aside>

            <section class="space-y-6">
                <div class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-xl shadow-slate-200/70">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-black text-slate-900">Bộ nhớ đang áp dụng</h2>
                            <p class="mt-1 text-sm font-semibold text-slate-400">Content AI và Image AI tự lấy các mục phù hợp khi generate.</p>
                        </div>
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700">{{ count($brain['memories']) }} mục</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($brain['categories'] as $category)
                            <button type="button" data-brain-filter="{{ $category['value'] }}" class="brain-filter rounded-2xl px-4 py-3 text-sm font-black transition {{ $activeCategory === $category['value'] ? 'bg-slate-950 text-white shadow-lg shadow-slate-200' : 'bg-slate-50 text-slate-600 hover:bg-blue-50 hover:text-blue-700' }}">
                                {{ $category['label'] }} ({{ $category['count'] }})
                            </button>
                        @endforeach
                    </div>
                </div>

                <div id="brainMemoryList" class="grid min-h-[420px] gap-4 rounded-[28px] border border-dashed border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/70 2xl:grid-cols-2">
                    @forelse ($brain['memories'] as $memory)
                        <article data-brain-category="{{ $memory['category'] }}" class="brain-memory rounded-2xl border border-slate-200 bg-slate-50 p-5" {{ $activeCategory !== 'all' && $activeCategory !== $memory['category'] ? 'hidden' : '' }}>
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-700">{{ $memory['category_label'] }}</p>
                                    <h2 class="mt-2 text-lg font-black text-slate-900">{{ $memory['topic'] }}</h2>
                                    <p class="mt-1 text-xs font-semibold text-slate-400">{{ $memory['created'] }}</p>
                                </div>
                                <form method="POST" action="{{ route('marketing.brain.destroy', $memory['id']) }}" data-confirm="Xóa dữ liệu huấn luyện này?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-black text-rose-600 transition hover:bg-rose-100">Xóa</button>
                                </form>
                            </div>
                            <p class="mt-4 whitespace-pre-line text-sm font-medium leading-7 text-slate-600">{{ $memory['content'] }}</p>
                        </article>
                    @empty
                        <div class="flex min-h-[320px] items-center justify-center text-center 2xl:col-span-2">
                            <div>
                                <p class="text-xl font-black text-slate-700">Chưa có bộ nhớ AI</p>
                                <p class="mt-2 text-sm font-semibold text-slate-400">Bấm một preset bên trái hoặc nhập quy tắc thương hiệu đầu tiên.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </section>

    <script>
        (() => {
            const filters = document.querySelectorAll('.brain-filter');
            const memories = document.querySelectorAll('.brain-memory');
            const category = document.getElementById('brainCategory');
            const topic = document.getElementById('brainTopic');
            const content = document.getElementById('brainContent');

            document.querySelectorAll('[data-brain-preset]').forEach((preset) => {
                preset.addEventListener('click', () => {
                    if (category) category.value = preset.dataset.category || 'voice_style';
                    if (topic) topic.value = preset.dataset.topic || '';
                    if (content) content.value = preset.dataset.content || '';
                    content?.focus();
                });
            });

            filters.forEach((filter) => {
                filter.addEventListener('click', () => {
                    const value = filter.dataset.brainFilter;
                    filters.forEach((item) => {
                        item.classList.remove('bg-slate-950', 'text-white', 'shadow-lg', 'shadow-slate-200');
                        item.classList.add('bg-slate-50', 'text-slate-600');
                    });
                    filter.classList.add('bg-slate-950', 'text-white', 'shadow-lg', 'shadow-slate-200');
                    filter.classList.remove('bg-slate-50', 'text-slate-600');

                    memories.forEach((memory) => {
                        memory.hidden = value !== 'all' && memory.dataset.brainCategory !== value;
                    });
                });
            });
        })();
    </script>
</x-layouts.app>
