<x-layouts.app :title="__('messages.marketing.content_ai.title')">
    @php
        $selected = $contentHub['selected'] ?? null;
        $activePlatform = old('platform', $selected['platform'] ?? 'facebook');
        $activeTab = request('tab', 'editor') === 'history' ? 'history' : 'editor';
        $platformIdeas = $contentHub['platform_ideas'] ?? [];
        $toneSamples = __('messages.marketing.content_ai.tone_samples');
        $toneSamples = is_array($toneSamples) ? $toneSamples : [];
        $ttsVoices = $contentHub['tts_voices'] ?? [];
        $defaultVoice = $ttsVoices[0]['value'] ?? 'vi-VN-HoaiMyNeural';
        $voiceLabels = $contentHub['tts_voice_labels'] ?? [];
        $jsMessages = [
            'browserNoSpeech' => __('messages.marketing.content_ai.browser_no_speech'),
            'currentVoice' => __('messages.marketing.content_ai.current_voice'),
            'noLocaleVoice' => __('messages.marketing.content_ai.no_locale_voice'),
            'generatingVoice' => __('messages.marketing.content_ai.generating_voice'),
            'edgeCreating' => __('messages.marketing.content_ai.edge_creating'),
            'edgeCreateError' => __('messages.marketing.content_ai.edge_create_error'),
            'edgePlaying' => __('messages.marketing.content_ai.edge_playing'),
            'edgeNotReady' => __('messages.marketing.content_ai.edge_not_ready'),
            'edgeReady' => __('messages.marketing.content_ai.edge_ready'),
            'tonePreviewSample' => __('messages.marketing.content_ai.tone_preview_sample'),
            'copied' => __('messages.marketing.content_ai.copied'),
            'copy' => __('messages.marketing.content_ai.copy'),
        ];
    @endphp

    <section class="-m-4 min-h-[calc(100vh-112px)] bg-slate-50 px-4 py-6 text-slate-900 md:-m-6 md:px-6">
        <div class="mx-auto grid w-full max-w-[1640px] gap-6 xl:grid-cols-[500px_minmax(0,1fr)]">
            <aside class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/70">
                <div class="mb-6 flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600 text-xl font-black text-white shadow-lg shadow-blue-200">AI</div>
                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-slate-900">{{ __('messages.marketing.content_ai.title') }}</h1>
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-slate-400">{{ __('messages.marketing.content_ai.subtitle') }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('marketing.content.store') }}" class="space-y-5" id="contentAiForm">
                    @csrf
                    <input type="hidden" name="idea" id="contentIdeaInput">

                    <div>
                        <p class="mb-3 text-xs font-black uppercase tracking-[0.18em] text-slate-500">{{ __('messages.marketing.content_ai.platform_distribution') }}</p>
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
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-700">{{ __('messages.marketing.content_ai.random_topic_title') }}</p>
                            <button type="button" id="randomIdeaButton" class="rounded-xl bg-white px-3 py-2 text-[11px] font-black text-blue-700 shadow-sm transition hover:bg-blue-600 hover:text-white">{{ __('messages.marketing.content_ai.random_button') }}</button>
                        </div>
                        <p id="platformIdeaText" class="min-h-[44px] text-sm font-semibold leading-6 text-slate-700"></p>
                    </div>

                    <label class="block">
                        <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-slate-500">{{ __('messages.marketing.content_ai.topic_product') }}</span>
                        <select name="product_id" class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <option value="">{{ __('messages.marketing.content_ai.choose_product') }}</option>
                            @foreach ($contentHub['products'] as $product)
                                <option value="{{ $product['id'] }}">{{ $product['name'] }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <textarea name="prompt" id="contentPrompt" rows="5" placeholder="{{ __('messages.marketing.content_ai.prompt_placeholder') }}" class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-semibold leading-6 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100">{{ old('prompt') }}</textarea>
                    </label>

                    <div class="rounded-2xl border border-blue-100 bg-blue-50/60 p-4">
                        <p class="mb-3 text-xs font-black uppercase tracking-[0.18em] text-blue-700">{{ __('messages.marketing.content_ai.ai_context') }}</p>
                        <div class="grid gap-3">
                            <label>
                                <span class="mb-1 block text-[11px] font-bold text-slate-500">{{ __('messages.marketing.content_ai.audience') }}</span>
                                <input name="audience" value="{{ old('audience', __('messages.marketing.content_ai.default_audience')) }}" class="h-11 w-full rounded-xl border border-blue-100 bg-white px-3 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500">
                            </label>
                            <label class="hidden">
                                <span class="mb-1 block text-[11px] font-bold text-slate-500">{{ __('messages.marketing.content_ai.tone') }}</span>
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
                                <span class="mb-1 block text-[11px] font-bold text-slate-500">{{ __('messages.marketing.content_ai.voice_label') }}</span>
                                <select id="voiceGenderSelect" class="h-11 w-full rounded-xl border border-blue-100 bg-white px-3 text-xs font-black text-slate-800 outline-none focus:border-blue-500">
                                    @foreach ($ttsVoices as $voice)
                                        <option value="{{ $voice['value'] }}" data-gender="{{ $voice['gender'] }}">{{ $voice['label'] }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <button type="button" id="refreshVoiceButton" class="mt-5 rounded-xl bg-white px-3 py-2 text-[11px] font-black text-blue-700 shadow-sm transition hover:bg-blue-600 hover:text-white">{{ __('messages.marketing.content_ai.edge_tts') }}</button>
                        </div>
                        <p id="voiceStatusText" class="mt-2 text-[11px] font-semibold leading-5 text-slate-500"></p>
                        <audio id="edgeTtsAudio" class="mt-3 hidden w-full" controls preload="none"></audio>
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-blue-100 pt-4">
                            <div class="flex flex-wrap gap-4 text-sm font-bold text-slate-700">
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="include_emoji" value="1" checked class="h-4 w-4 rounded border-slate-300 text-blue-600">
                                    {{ __('messages.marketing.content_ai.include_emoji') }}
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="include_hashtags" value="1" checked class="h-4 w-4 rounded border-slate-300 text-blue-600">
                                    {{ __('messages.marketing.content_ai.include_hashtags') }}
                                </label>
                            </div>
                            <button type="button" id="tonePreviewButton" class="rounded-xl bg-slate-950 px-3 py-2 text-[11px] font-black text-white transition hover:bg-blue-700">{{ __('messages.marketing.content_ai.tone_preview') }}</button>
                        </div>
                    </div>

                    <button type="submit" class="flex h-14 w-full items-center justify-center rounded-2xl bg-slate-950 text-sm font-black uppercase tracking-[0.08em] text-white shadow-xl shadow-slate-300 transition hover:bg-blue-700">
                        {{ __('messages.marketing.content_ai.create_now') }}
                    </button>
                </form>
            </aside>

            <section class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-xl shadow-slate-200/70">
                <div class="grid border-b border-slate-200 sm:grid-cols-2">
                    <button type="button" data-content-tab-button="editor" class="content-tab-button px-7 py-5 text-left text-sm font-black uppercase tracking-[0.16em] transition {{ $activeTab === 'editor' ? 'border-b-2 border-blue-600 text-blue-700' : 'text-slate-400 hover:text-blue-700' }}">{{ __('messages.marketing.content_ai.editor_tab') }}</button>
                    <button type="button" data-content-tab-button="history" class="content-tab-button px-7 py-5 text-left text-sm font-black uppercase tracking-[0.16em] transition {{ $activeTab === 'history' ? 'border-b-2 border-blue-600 text-blue-700' : 'text-slate-400 hover:text-blue-700' }}">{{ __('messages.marketing.content_ai.history_tab') }}</button>
                </div>

                <div class="{{ $activeTab === 'editor' ? '' : 'hidden' }} p-7" data-content-panel="editor">
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                        <span class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-xs font-black uppercase text-blue-700">
                            {{ __('messages.marketing.content_ai.draft_for', ['platform' => $selected['platform_label'] ?? 'Facebook']) }}
                        </span>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" id="readContentButton" class="rounded-xl bg-slate-950 px-4 py-2 text-xs font-black text-white transition hover:bg-blue-700" @disabled(!$selected)>{{ __('messages.marketing.content_ai.read_text') }}</button>
                            <button type="button" id="stopReadButton" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-black text-slate-500 transition hover:bg-slate-200">{{ __('messages.marketing.content_ai.stop') }}</button>
                            <button type="button" id="copyContentButton" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-black text-slate-500 transition hover:bg-slate-200" @disabled(!$selected)>{{ __('messages.marketing.content_ai.copy') }}</button>
                            @if ($selected)
                                <form method="POST" action="{{ route('marketing.content.update', $selected['id']) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="content" id="hiddenContentInput">
                                    <button type="submit" id="saveContentButton" class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-black text-white shadow-lg shadow-blue-100 transition hover:bg-blue-700">{{ __('messages.marketing.content_ai.save_post') }}</button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <textarea id="contentOutput" rows="18" class="min-h-[520px] w-full resize-y rounded-2xl border border-slate-200 bg-slate-50 px-6 py-5 text-base font-medium leading-8 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100" placeholder="{{ __('messages.marketing.content_ai.output_placeholder') }}">{{ $selected['content'] ?? '' }}</textarea>
                </div>

                <div class="{{ $activeTab === 'history' ? '' : 'hidden' }} p-7" data-content-panel="history">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-black text-slate-900">{{ __('messages.marketing.content_ai.saved_history') }}</h2>
                            <p class="mt-1 text-sm font-semibold text-slate-400">{{ __('messages.marketing.content_ai.drafts_count', ['count' => count($contentHub['drafts'])]) }}</p>
                        </div>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-2">
                        @forelse ($contentHub['drafts'] as $draft)
                            <article class="rounded-2xl border {{ $selected && $selected['id'] === $draft['id'] ? 'border-blue-300 bg-blue-50/60' : 'border-slate-200 bg-slate-50' }} p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="line-clamp-1 text-sm font-black text-slate-900">{{ $draft['title'] }}</p>
                                        <p class="mt-1 text-[11px] font-bold text-slate-400">{{ $draft['created'] }} - {{ $draft['platform_label'] }} - {{ $draft['status_label'] ?? $draft['status'] }}</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <a href="{{ route('marketing.content.index', ['draft' => $draft['id'], 'tab' => 'editor']) }}" class="rounded-lg bg-white px-3 py-1 text-[11px] font-black text-blue-700 shadow-sm">{{ __('messages.marketing.content_ai.open') }}</a>
                                        <form method="POST" action="{{ route('marketing.content.destroy', $draft['id']) }}" data-confirm="{{ __('messages.marketing.content_ai.delete_confirm') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg bg-rose-50 px-3 py-1 text-[11px] font-black text-rose-600">{{ __('messages.marketing.content_ai.delete') }}</button>
                                        </form>
                                    </div>
                                </div>
                                <p class="mt-3 line-clamp-4 whitespace-pre-line text-xs font-semibold leading-5 text-slate-500">{{ $draft['content'] }}</p>
                            </article>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-200 p-10 text-center text-sm font-semibold text-slate-400 lg:col-span-2">{{ __('messages.marketing.content_ai.empty_drafts') }}</div>
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
            const locale = @json(app()->getLocale());
            const speechLang = locale === 'en' ? 'en-US' : 'vi-VN';
            const defaultVoice = @json($defaultVoice);
            const voiceLabels = @json($voiceLabels);
            const i18n = @json($jsMessages);
            const edgeAudio = document.getElementById('edgeTtsAudio');
            let availableVoices = [];

            const fill = (message, replacements = {}) => Object.entries(replacements)
                .reduce((text, [key, value]) => text.replace(`:${key}`, value), message);
            const pick = (items) => items[Math.floor(Math.random() * items.length)] || '';
            const loadVoices = () => {
                availableVoices = ('speechSynthesis' in window) ? window.speechSynthesis.getVoices() : [];
                if (typeof updateVoiceStatus === 'function') updateVoiceStatus();
            };
            const selectedVoiceGender = () => voiceGender?.selectedOptions?.[0]?.dataset.gender || 'female';
            const isLocaleVoice = (voice) => {
                const name = `${voice.name} ${voice.lang} ${voice.voiceURI}`.toLowerCase();
                const lang = (voice.lang || '').toLowerCase();
                const needles = locale === 'en'
                    ? ['english', 'united states', 'us']
                    : ['vietnam', 'vietnamese', 'tiếng việt'];

                return lang.startsWith(locale) || needles.some((needle) => name.includes(needle));
            };
            const chooseLocaleVoice = () => {
                const desiredGender = selectedVoiceGender();
                const localeVoices = availableVoices.filter((voice) => {
                    return isLocaleVoice(voice);
                });
                const pool = localeVoices.length ? localeVoices : availableVoices;
                const femaleHints = ['female', 'woman', 'jenny', 'zira', 'aria', 'nu', 'nữ', 'linh', 'hoai', 'hoài', 'mai', 'my', 'vy', 'an'];
                const maleHints = ['male', 'man', 'guy', 'david', 'nam', 'minh', 'long', 'huy', 'quan', 'quân'];
                const hints = desiredGender === 'male' ? maleHints : femaleHints;

                return pool.find((voice) => hints.some((hint) => voice.name.toLowerCase().includes(hint)))
                    || localeVoices[0]
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
                utterance.lang = speechLang;
                const selectedVoice = chooseLocaleVoice();
                if (selectedVoice) {
                    utterance.voice = selectedVoice;
                    utterance.lang = selectedVoice.lang || speechLang;
                }
                const toneValue = tone?.value || 'expert';
                utterance.rate = toneValue === 'viral' ? 1.06 : toneValue === 'premium' ? 0.88 : 0.95;
                utterance.pitch = selectedVoiceGender() === 'male'
                    ? (toneValue === 'direct' ? 0.82 : 0.88)
                    : (toneValue === 'friendly' ? 1.08 : 1.02);
                utterance.volume = 1;
                window.speechSynthesis.speak(utterance);
            };
            const chooseProfessionalLocaleVoice = () => {
                const desiredGender = selectedVoiceGender();
                const femaleHints = ['female', 'woman', 'jenny', 'zira', 'aria', 'nu', 'nữ', 'hoai', 'hoài', 'linh', 'mai', 'my', 'vy', 'an'];
                const maleHints = ['male', 'man', 'guy', 'david', 'nam', 'minh', 'long', 'huy', 'quan', 'quân'];
                const hints = desiredGender === 'male' ? maleHints : femaleHints;
                const scored = availableVoices.map((voice) => {
                    const name = `${voice.name} ${voice.lang} ${voice.voiceURI}`.toLowerCase();
                    let score = 0;
                    if (isLocaleVoice(voice)) score += 100;
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
                    voiceStatus.textContent = i18n.browserNoSpeech;
                    return;
                }
                const voice = chooseProfessionalLocaleVoice();
                voiceStatus.textContent = voice
                    ? fill(i18n.currentVoice, { voice: `${voice.name} (${voice.lang || speechLang})` })
                    : i18n.noLocaleVoice;
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
            const speakWithBrowserVoice = (text) => {
                if (!('speechSynthesis' in window) || !text.trim()) return;
                loadVoices();
                window.speechSynthesis.cancel();
                const chunks = splitSpeechText(text);
                const selectedVoice = chooseProfessionalLocaleVoice();
                const toneValue = tone?.value || 'expert';
                const speakNext = () => {
                    const chunk = chunks.shift();
                    if (!chunk) return;
                    const utterance = new SpeechSynthesisUtterance(chunk);
                    utterance.lang = speechLang;
                    if (selectedVoice) {
                        utterance.voice = selectedVoice;
                        utterance.lang = selectedVoice.lang || speechLang;
                    }
                    utterance.rate = toneValue === 'viral' ? 1.02 : toneValue === 'premium' ? 0.86 : 0.92;
                    utterance.pitch = selectedVoiceGender() === 'male'
                        ? (toneValue === 'direct' ? 0.82 : 0.88)
                        : (toneValue === 'friendly' ? 1.06 : 1);
                    utterance.volume = 1;
                    utterance.onend = speakNext;
                    window.speechSynthesis.speak(utterance);
                };
                speakNext();
            };
            const edgeVoiceLabel = () => {
                const value = voiceGender?.value || defaultVoice;
                return voiceLabels[value] || value;
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
                if (read) read.textContent = i18n.generatingVoice;
                setEdgeStatus(fill(i18n.edgeCreating, { voice: edgeVoiceLabel() }));

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
                            voice: voiceGender?.value || defaultVoice,
                            tone: tone?.value || 'expert',
                        }),
                    });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        throw new Error(data.message || i18n.edgeCreateError);
                    }

                    window.speechSynthesis?.cancel();
                    if (edgeAudio) {
                        edgeAudio.src = data.url;
                        edgeAudio.classList.remove('hidden');
                        edgeAudio.currentTime = 0;
                        await edgeAudio.play();
                    }
                    setEdgeStatus(fill(i18n.edgePlaying, { voice: `${data.voice_label || edgeVoiceLabel()} (${data.voice || voiceGender?.value || defaultVoice})` }));
                } catch (error) {
                    setEdgeStatus(error.message || i18n.edgeNotReady);
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
                setEdgeStatus(fill(i18n.edgeReady, { voice: edgeVoiceLabel() }));
            };

            loadVoices();
            if ('speechSynthesis' in window) {
                window.speechSynthesis.onvoiceschanged = loadVoices;
            }
            refreshVoice?.addEventListener('click', () => setEdgeStatus(fill(i18n.edgeReady, { voice: edgeVoiceLabel() })));
            voiceGender?.addEventListener('change', () => setEdgeStatus(fill(i18n.edgeReady, { voice: edgeVoiceLabel() })));
            document.querySelectorAll('input[name="platform"]').forEach((item) => item.addEventListener('change', setIdea));
            randomIdeaButton?.addEventListener('click', setIdea);
            tone?.addEventListener('change', setToneSample);
            tonePreview?.addEventListener('click', () => speakEdgeTts(toneSamples[tone?.value] || i18n.tonePreviewSample));

            save?.addEventListener('click', () => {
                hidden.value = output.value;
            });

            copy?.addEventListener('click', async () => {
                if (!output.value.trim()) return;
                await navigator.clipboard.writeText(output.value);
                copy.textContent = i18n.copied;
                setTimeout(() => copy.textContent = i18n.copy, 1200);
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
            setEdgeStatus(fill(i18n.edgeReady, { voice: edgeVoiceLabel() }));
        })();
    </script>
</x-layouts.app>
