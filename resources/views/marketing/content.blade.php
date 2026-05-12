<x-layouts.app title="Content AI">
    @php
        $selected = $contentHub['selected'] ?? null;
        $activePlatform = old('platform', $selected['platform'] ?? 'facebook');
        $activeTab = request('tab', 'editor') === 'history' ? 'history' : 'editor';
        $platformIdeas = $contentHub['platform_ideas'] ?? [];
        $toneSamples = [
            'expert' => 'Rõ ràng, có lập luận, đáng tin và tập trung vào kết quả.',
            'friendly' => 'Gần gũi, dễ hiểu, giống đang tư vấn cho khách quen.',
            'premium' => 'Tinh gọn, sang, ít phóng đại, nhấn vào trải nghiệm.',
            'viral' => 'Câu ngắn, hook lớn, nhịp nhanh, tạo cảm giác muốn dừng cuộn.',
            'direct' => 'Đi thẳng vào lợi ích, ưu đãi, lý do mua và CTA mạnh.',
        ];
    @endphp

    <section class="-m-4 min-h-[calc(100vh-112px)] bg-slate-50 px-4 py-6 text-slate-900 md:-m-6 md:px-6">
        <div class="mx-auto grid w-full max-w-[1640px] gap-6 xl:grid-cols-[500px_minmax(0,1fr)]">
            <aside class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/70">
                <div class="mb-6 flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600 text-xl font-black text-white shadow-lg shadow-blue-200">AI</div>
                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-slate-900">Content AI</h1>
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-slate-400">Đa nền tảng - giọng văn thông minh</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('marketing.content.store') }}" class="space-y-5" id="contentAiForm">
                    @csrf
                    <input type="hidden" name="idea" id="contentIdeaInput">

                    <div>
                        <p class="mb-3 text-xs font-black uppercase tracking-[0.18em] text-slate-500">1. Nền tảng phân phối</p>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach ($contentHub['platforms'] as $platform)
                                <label class="cursor-pointer">
                                    <input type="radio" name="platform" value="{{ $platform['value'] }}" class="peer sr-only" @checked($activePlatform === $platform['value'])>
                                    <span class="flex h-14 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white text-xs font-black uppercase text-slate-500 transition peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-700 peer-checked:shadow-lg peer-checked:shadow-blue-100">
                                        <span class="flex h-4 w-4 items-center justify-center">
                                            @switch($platform['value'])
                                                @case('facebook')
                                                    <svg viewBox="0 0 24 24" class="h-4 w-4 fill-current" aria-hidden="true"><path d="M14 8.2V6.6c0-.8.5-1 1-1h1.5V3h-2.2C11.9 3 10.5 4.4 10.5 6.5v1.7H8.8V11h1.7v8h3.1v-8h2.2l.4-2.8H14Z"/></svg>
                                                    @break
                                                @case('instagram')
                                                    <svg viewBox="0 0 24 24" class="h-4 w-4 fill-none stroke-current stroke-[2.2]" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="5"/><circle cx="12" cy="12" r="3.5"/><circle cx="16.8" cy="7.2" r="1" class="fill-current stroke-0"/></svg>
                                                    @break
                                                @case('tiktok')
                                                    <svg viewBox="0 0 24 24" class="h-4 w-4 fill-current" aria-hidden="true"><path d="M15.2 3c.4 2.7 1.9 4.3 4.4 4.5v3a7 7 0 0 1-4.3-1.4v5.8c0 3.1-2.1 5.1-5.2 5.1-2.9 0-5-1.9-5-4.6 0-2.8 2.2-4.7 5.2-4.7.4 0 .7 0 1 .1v3.1c-.3-.1-.7-.2-1.1-.2-1.1 0-1.9.6-1.9 1.6 0 1 .7 1.6 1.7 1.6 1.2 0 1.9-.7 1.9-2.1V3h3.3Z"/></svg>
                                                    @break
                                                @case('linkedin')
                                                    <svg viewBox="0 0 24 24" class="h-4 w-4 fill-current" aria-hidden="true"><path d="M6.8 8.9H3.7V20h3.1V8.9ZM5.2 4C4.2 4 3.5 4.7 3.5 5.6c0 .9.7 1.6 1.7 1.6s1.7-.7 1.7-1.6C6.9 4.7 6.2 4 5.2 4Zm15.3 9.6c0-3.3-1.8-4.9-4.2-4.9-1.9 0-2.8 1.1-3.3 1.8V8.9H9.9V20H13v-6.2c0-1.6.3-3.1 2.2-3.1 1.9 0 1.9 1.8 1.9 3.2V20h3.1v-6.4Z"/></svg>
                                                    @break
                                                @case('email')
                                                    <svg viewBox="0 0 24 24" class="h-4 w-4 fill-none stroke-current stroke-[2.2]" aria-hidden="true"><rect x="3.5" y="5.5" width="17" height="13" rx="2"/><path d="m4.5 7 7.5 5.5L19.5 7"/></svg>
                                                    @break
                                                @default
                                                    <span class="text-xs font-black">Z</span>
                                            @endswitch
                                        </span>{{ $platform['label'] }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-2xl border border-blue-100 bg-blue-50/70 p-4">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-700">Chủ đề random theo nền tảng</p>
                            <button type="button" id="randomIdeaButton" class="rounded-xl bg-white px-3 py-2 text-[11px] font-black text-blue-700 shadow-sm transition hover:bg-blue-600 hover:text-white">Random</button>
                        </div>
                        <p id="platformIdeaText" class="min-h-[44px] text-sm font-semibold leading-6 text-slate-700"></p>
                    </div>

                    <label class="block">
                        <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-slate-500">2. Chủ đề / sản phẩm</span>
                        <select name="product_id" class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <option value="">-- Chọn sản phẩm / dịch vụ --</option>
                            @foreach ($contentHub['products'] as $product)
                                <option value="{{ $product['id'] }}">{{ $product['name'] }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <textarea name="prompt" id="contentPrompt" rows="5" placeholder="Để trống để AI tự lấy chủ đề random theo nền tảng..." class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-semibold leading-6 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100">{{ old('prompt') }}</textarea>
                    </label>

                    <div class="rounded-2xl border border-blue-100 bg-blue-50/60 p-4">
                        <p class="mb-3 text-xs font-black uppercase tracking-[0.18em] text-blue-700">Ngữ cảnh AI áp dụng</p>
                        <div class="grid gap-3">
                            <label>
                                <span class="mb-1 block text-[11px] font-bold text-slate-500">Đối tượng</span>
                                <input name="audience" value="{{ old('audience', 'Mass (All) tại khu vực Việt Nam') }}" class="h-11 w-full rounded-xl border border-blue-100 bg-white px-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500">
                            </label>
                            <label class="hidden">
                                <span class="mb-1 block text-[11px] font-bold text-slate-500">Giọng văn</span>
                                <select name="tone" id="toneSelect" class="h-11 w-full rounded-xl border border-blue-100 bg-white px-3 text-xs font-black text-slate-800 outline-none focus:border-blue-500">
                                    @foreach ($contentHub['tones'] as $tone)
                                        <option value="{{ $tone['value'] }}">{{ $tone['label'] }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                        <div class="hidden mt-3 rounded-xl bg-white px-3 py-2 text-xs font-semibold leading-5 text-slate-500" id="toneSampleText"></div>
                        <div class="mt-3 grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto]">
                            <label>
                                <span class="mb-1 block text-[11px] font-bold text-slate-500">Voice đọc văn bản</span>
                                <select id="voiceGenderSelect" class="h-11 w-full rounded-xl border border-blue-100 bg-white px-3 text-xs font-black text-slate-800 outline-none focus:border-blue-500">
                                    <option value="vi-VN-HoaiMyNeural">Nữ Hoài My - Edge Neural</option>
                                    <option value="vi-VN-NamMinhNeural">Nam Minh - Edge Neural</option>
                                </select>
                            </label>
                            <button type="button" id="refreshVoiceButton" class="mt-5 rounded-xl bg-white px-3 py-2 text-[11px] font-black text-blue-700 shadow-sm transition hover:bg-blue-600 hover:text-white">Edge TTS</button>
                        </div>
                        <p id="voiceStatusText" class="mt-2 text-[11px] font-semibold leading-5 text-slate-500"></p>
                        <audio id="edgeTtsAudio" class="mt-3 hidden w-full" controls preload="none"></audio>
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-blue-100 pt-4">
                            <div class="flex flex-wrap gap-4 text-sm font-bold text-slate-700">
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="include_emoji" value="1" checked class="h-4 w-4 rounded border-slate-300 text-blue-600">
                                    Dùng Emoji
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="include_hashtags" value="1" checked class="h-4 w-4 rounded border-slate-300 text-blue-600">
                                    Gắn Hashtags
                                </label>
                            </div>
                            <button type="button" id="tonePreviewButton" class="rounded-xl bg-slate-950 px-3 py-2 text-[11px] font-black text-white transition hover:bg-blue-700">Nghe giọng văn</button>
                        </div>
                    </div>

                    <button type="submit" class="flex h-14 w-full items-center justify-center rounded-2xl bg-slate-950 text-sm font-black uppercase tracking-[0.08em] text-white shadow-xl shadow-slate-300 transition hover:bg-blue-700">
                        Tạo bài viết ngay
                    </button>
                </form>
            </aside>

            <section class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-xl shadow-slate-200/70">
                <div class="grid border-b border-slate-200 sm:grid-cols-2">
                    <button type="button" data-content-tab-button="editor" class="content-tab-button px-7 py-5 text-left text-sm font-black uppercase tracking-[0.16em] transition {{ $activeTab === 'editor' ? 'border-b-2 border-blue-600 text-blue-700' : 'text-slate-400 hover:text-blue-700' }}">Trình soạn thảo</button>
                    <button type="button" data-content-tab-button="history" class="content-tab-button px-7 py-5 text-left text-sm font-black uppercase tracking-[0.16em] transition {{ $activeTab === 'history' ? 'border-b-2 border-blue-600 text-blue-700' : 'text-slate-400 hover:text-blue-700' }}">Lịch sử đã lưu</button>
                </div>

                <div class="{{ $activeTab === 'editor' ? '' : 'hidden' }} p-7" data-content-panel="editor">
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                        <span class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-xs font-black uppercase text-blue-700">
                            Bản thảo cho {{ $selected['platform_label'] ?? 'Facebook' }}
                        </span>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" id="readContentButton" class="rounded-xl bg-slate-950 px-4 py-2 text-xs font-black text-white transition hover:bg-blue-700" @disabled(!$selected)>Đọc văn bản</button>
                            <button type="button" id="stopReadButton" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-black text-slate-500 transition hover:bg-slate-200">Dừng</button>
                            <button type="button" id="copyContentButton" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-black text-slate-500 transition hover:bg-slate-200" @disabled(!$selected)>Copy</button>
                            @if ($selected)
                                <form method="POST" action="{{ route('marketing.content.update', $selected['id']) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="content" id="hiddenContentInput">
                                    <button type="submit" id="saveContentButton" class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-black text-white shadow-lg shadow-blue-100 transition hover:bg-blue-700">Lưu bài</button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <textarea id="contentOutput" rows="18" class="min-h-[520px] w-full resize-y rounded-2xl border border-slate-200 bg-slate-50 px-6 py-5 text-base font-medium leading-8 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100" placeholder="Kết quả sẽ hiển thị ở đây. Bạn có thể chỉnh sửa trực tiếp nội dung trước khi Copy hoặc Lưu...">{{ $selected['content'] ?? '' }}</textarea>
                </div>

                <div class="{{ $activeTab === 'history' ? '' : 'hidden' }} p-7" data-content-panel="history">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-black text-slate-900">Lịch sử đã lưu</h2>
                            <p class="mt-1 text-sm font-semibold text-slate-400">{{ count($contentHub['drafts']) }} bản thảo gần nhất</p>
                        </div>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-2">
                        @forelse ($contentHub['drafts'] as $draft)
                            <article class="rounded-2xl border {{ $selected && $selected['id'] === $draft['id'] ? 'border-blue-300 bg-blue-50/60' : 'border-slate-200 bg-slate-50' }} p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="line-clamp-1 text-sm font-black text-slate-900">{{ $draft['title'] }}</p>
                                        <p class="mt-1 text-[11px] font-bold text-slate-400">{{ $draft['created'] }} - {{ $draft['platform_label'] }} - {{ $draft['status'] }}</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <a href="{{ route('marketing.content.index', ['draft' => $draft['id'], 'tab' => 'editor']) }}" class="rounded-lg bg-white px-3 py-1 text-[11px] font-black text-blue-700 shadow-sm">Mở</a>
                                        <form method="POST" action="{{ route('marketing.content.destroy', $draft['id']) }}" data-confirm="Xóa bản thảo này?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg bg-rose-50 px-3 py-1 text-[11px] font-black text-rose-600">Xóa</button>
                                        </form>
                                    </div>
                                </div>
                                <p class="mt-3 line-clamp-4 whitespace-pre-line text-xs font-semibold leading-5 text-slate-500">{{ $draft['content'] }}</p>
                            </article>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-200 p-10 text-center text-sm font-semibold text-slate-400 lg:col-span-2">Chưa có bản thảo nào.</div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    </section>

    <script>
        (() => {
            const output = document.getElementById('contentOutput');
            const hidden = document.getElementById('hiddenContentInput');
            const save = document.getElementById('saveContentButton');
            const copy = document.getElementById('copyContentButton');
            const read = document.getElementById('readContentButton');
            const stopRead = document.getElementById('stopReadButton');
            const tone = document.getElementById('toneSelect');
            const tonePreview = document.getElementById('tonePreviewButton');
            const voiceGender = document.getElementById('voiceGenderSelect');
            const refreshVoice = document.getElementById('refreshVoiceButton');
            const voiceStatus = document.getElementById('voiceStatusText');
            const toneSample = document.getElementById('toneSampleText');
            const prompt = document.getElementById('contentPrompt');
            const ideaInput = document.getElementById('contentIdeaInput');
            const ideaText = document.getElementById('platformIdeaText');
            const randomIdeaButton = document.getElementById('randomIdeaButton');
            const ideas = @json($platformIdeas);
            const toneSamples = @json($toneSamples);
            const edgeTtsUrl = @json(route('marketing.content.edge-tts'));
            const csrfToken = @json(csrf_token());
            const edgeAudio = document.getElementById('edgeTtsAudio');
            let availableVoices = [];

            const pick = (items) => items[Math.floor(Math.random() * items.length)] || '';
            const loadVoices = () => {
                availableVoices = ('speechSynthesis' in window) ? window.speechSynthesis.getVoices() : [];
                if (typeof updateVoiceStatus === 'function') updateVoiceStatus();
            };
            const chooseVietnameseVoice = () => {
                const desiredGender = voiceGender?.value || 'female';
                const viVoices = availableVoices.filter((voice) => {
                    const name = `${voice.name} ${voice.lang}`.toLowerCase();
                    return voice.lang?.toLowerCase().startsWith('vi') || name.includes('vietnam') || name.includes('tiếng việt');
                });
                const pool = viVoices.length ? viVoices : availableVoices;
                const femaleHints = ['female', 'woman', 'nu', 'nữ', 'linh', 'hoai', 'hoài', 'mai', 'my', 'vy', 'an'];
                const maleHints = ['male', 'man', 'nam', 'minh', 'long', 'huy', 'quan', 'quân'];
                const hints = desiredGender === 'male' ? maleHints : femaleHints;

                return pool.find((voice) => hints.some((hint) => voice.name.toLowerCase().includes(hint)))
                    || viVoices[0]
                    || pool[0]
                    || null;
            };
            const activePlatform = () => document.querySelector('input[name="platform"]:checked')?.value || 'facebook';
            const setIdea = () => {
                const idea = pick(ideas[activePlatform()] || ideas.facebook || []);
                ideaInput.value = idea;
                ideaText.textContent = idea;
                if (prompt && !prompt.value.trim()) prompt.placeholder = idea;
            };
            const setToneSample = () => {
                if (toneSample) toneSample.textContent = toneSamples[tone?.value] || '';
            };
            const speak = (text) => {
                if (!('speechSynthesis' in window) || !text.trim()) return;
                loadVoices();
                window.speechSynthesis.cancel();
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'vi-VN';
                const selectedVoice = chooseVietnameseVoice();
                if (selectedVoice) {
                    utterance.voice = selectedVoice;
                    utterance.lang = selectedVoice.lang || 'vi-VN';
                }
                const toneValue = tone?.value || 'expert';
                utterance.rate = toneValue === 'viral' ? 1.06 : toneValue === 'premium' ? 0.88 : 0.95;
                utterance.pitch = voiceGender?.value === 'male'
                    ? (toneValue === 'direct' ? 0.82 : 0.88)
                    : (toneValue === 'friendly' ? 1.08 : 1.02);
                utterance.volume = 1;
                window.speechSynthesis.speak(utterance);
            };
            const chooseProfessionalVietnameseVoice = () => {
                const desiredGender = voiceGender?.value || 'female';
                const femaleHints = ['female', 'woman', 'nu', 'nữ', 'hoai', 'hoài', 'linh', 'mai', 'my', 'vy', 'an'];
                const maleHints = ['male', 'man', 'nam', 'minh', 'long', 'huy', 'quan', 'quân'];
                const hints = desiredGender === 'male' ? maleHints : femaleHints;
                const scored = availableVoices.map((voice) => {
                    const name = `${voice.name} ${voice.lang} ${voice.voiceURI}`.toLowerCase();
                    let score = 0;
                    if ((voice.lang || '').toLowerCase().startsWith('vi')) score += 100;
                    if (name.includes('vietnam') || name.includes('vietnamese') || name.includes('tiếng việt')) score += 70;
                    if (name.includes('microsoft') || name.includes('google')) score += 20;
                    if (hints.some((hint) => name.includes(hint))) score += 18;
                    if (voice.localService) score += 4;
                    return { voice, score };
                }).sort((a, b) => b.score - a.score);

                return scored[0]?.voice || null;
            };
            const updateVoiceStatus = () => {
                if (!voiceStatus) return;
                if (!('speechSynthesis' in window)) {
                    voiceStatus.textContent = 'Trình duyệt chưa hỗ trợ đọc văn bản.';
                    return;
                }
                const voice = chooseProfessionalVietnameseVoice();
                voiceStatus.textContent = voice
                    ? `Đang dùng voice: ${voice.name} (${voice.lang || 'vi-VN'})`
                    : 'Chưa tìm thấy voice tiếng Việt. Bấm Nạp voice hoặc cài Vietnamese voice trong Windows/Chrome.';
            };
            const cleanSpeechText = (text) => text
                .replace(/https?:\/\/\S+/g, '')
                .replace(/#[\p{L}\p{N}_-]+/gu, '')
                .replace(/[\u{1F1E6}-\u{1FAFF}\u{2600}-\u{27BF}]/gu, '')
                .replace(/[•*_~`>#|[\]{}]/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();
            const splitSpeechText = (text) => {
                const cleaned = cleanSpeechText(text);
                const sentences = cleaned.match(/[^.!?。！？\n]+[.!?。！？]?/g) || [cleaned];
                const chunks = [];
                let chunk = '';
                sentences.forEach((sentence) => {
                    const next = `${chunk} ${sentence}`.trim();
                    if (next.length > 220 && chunk) {
                        chunks.push(chunk);
                        chunk = sentence.trim();
                    } else {
                        chunk = next;
                    }
                });
                if (chunk) chunks.push(chunk);
                return chunks;
            };
            const speakVietnamese = (text) => {
                if (!('speechSynthesis' in window) || !text.trim()) return;
                loadVoices();
                window.speechSynthesis.cancel();
                const chunks = splitSpeechText(text);
                const selectedVoice = chooseProfessionalVietnameseVoice();
                const toneValue = tone?.value || 'expert';
                const speakNext = () => {
                    const chunk = chunks.shift();
                    if (!chunk) return;
                    const utterance = new SpeechSynthesisUtterance(chunk);
                    utterance.lang = 'vi-VN';
                    if (selectedVoice) {
                        utterance.voice = selectedVoice;
                        utterance.lang = selectedVoice.lang || 'vi-VN';
                    }
                    utterance.rate = toneValue === 'viral' ? 1.02 : toneValue === 'premium' ? 0.86 : 0.92;
                    utterance.pitch = voiceGender?.value === 'male'
                        ? (toneValue === 'direct' ? 0.82 : 0.88)
                        : (toneValue === 'friendly' ? 1.06 : 1);
                    utterance.volume = 1;
                    utterance.onend = speakNext;
                    window.speechSynthesis.speak(utterance);
                };
                speakNext();
            };
            const edgeVoiceLabel = () => {
                const value = voiceGender?.value || 'vi-VN-HoaiMyNeural';
                return value === 'vi-VN-NamMinhNeural' ? 'Nam Minh' : 'Hoài My';
            };
            const setEdgeStatus = (message) => {
                if (voiceStatus) voiceStatus.textContent = message;
            };
            const speakEdgeTts = async (text) => {
                const cleanText = (text || '').trim();
                if (!cleanText) return;

                const oldReadText = read?.textContent;
                const oldPreviewText = tonePreview?.textContent;
                read?.setAttribute('disabled', 'disabled');
                tonePreview?.setAttribute('disabled', 'disabled');
                if (read) read.textContent = 'Đang tạo voice...';
                setEdgeStatus(`Edge TTS đang tạo MP3 bằng giọng ${edgeVoiceLabel()}...`);

                try {
                    const response = await fetch(edgeTtsUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            text: cleanText,
                            voice: voiceGender?.value || 'vi-VN-HoaiMyNeural',
                            tone: tone?.value || 'expert',
                        }),
                    });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        throw new Error(data.message || 'Không tạo được voice Edge TTS.');
                    }

                    window.speechSynthesis?.cancel();
                    if (edgeAudio) {
                        edgeAudio.src = data.url;
                        edgeAudio.classList.remove('hidden');
                        edgeAudio.currentTime = 0;
                        await edgeAudio.play();
                    }
                    setEdgeStatus(`Đang phát Edge TTS: ${data.voice_label || edgeVoiceLabel()} (${data.voice || voiceGender?.value}).`);
                } catch (error) {
                    setEdgeStatus(error.message || 'Edge TTS chưa sẵn sàng.');
                } finally {
                    read?.removeAttribute('disabled');
                    tonePreview?.removeAttribute('disabled');
                    if (read && oldReadText) read.textContent = oldReadText;
                    if (tonePreview && oldPreviewText) tonePreview.textContent = oldPreviewText;
                }
            };
            const stopEdgeTts = () => {
                edgeAudio?.pause();
                if (edgeAudio) edgeAudio.currentTime = 0;
                window.speechSynthesis?.cancel();
                setEdgeStatus(`Edge TTS sẵn sàng: ${edgeVoiceLabel()}.`);
            };

            loadVoices();
            if ('speechSynthesis' in window) {
                window.speechSynthesis.onvoiceschanged = loadVoices;
            }
            refreshVoice?.addEventListener('click', () => setEdgeStatus(`Edge TTS sẵn sàng: ${edgeVoiceLabel()}.`));
            voiceGender?.addEventListener('change', () => setEdgeStatus(`Edge TTS sẵn sàng: ${edgeVoiceLabel()}.`));
            document.querySelectorAll('input[name="platform"]').forEach((item) => item.addEventListener('change', setIdea));
            randomIdeaButton?.addEventListener('click', setIdea);
            tone?.addEventListener('change', setToneSample);
            tonePreview?.addEventListener('click', () => speakEdgeTts(toneSamples[tone?.value] || 'Đây là giọng văn mẫu cho nội dung của bạn.'));

            save?.addEventListener('click', () => {
                hidden.value = output.value;
            });

            copy?.addEventListener('click', async () => {
                if (!output.value.trim()) return;
                await navigator.clipboard.writeText(output.value);
                copy.textContent = 'Copied';
                setTimeout(() => copy.textContent = 'Copy', 1200);
            });

            read?.addEventListener('click', () => speakEdgeTts(output?.value || ''));
            stopRead?.addEventListener('click', stopEdgeTts);

            document.querySelectorAll('[data-content-tab-button]').forEach((button) => {
                button.addEventListener('click', () => {
                    const tab = button.dataset.contentTabButton;
                    document.querySelectorAll('[data-content-panel]').forEach((panel) => panel.classList.toggle('hidden', panel.dataset.contentPanel !== tab));
                    document.querySelectorAll('[data-content-tab-button]').forEach((item) => {
                        item.classList.toggle('border-b-2', item === button);
                        item.classList.toggle('border-blue-600', item === button);
                        item.classList.toggle('text-blue-700', item === button);
                        item.classList.toggle('text-slate-400', item !== button);
                    });
                });
            });

            setIdea();
            setToneSample();
            setEdgeStatus(`Edge TTS sẵn sàng: ${edgeVoiceLabel()}.`);
        })();
    </script>
</x-layouts.app>
