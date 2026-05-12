<x-layouts.app title="AI Video Generator">
    <x-marketing.studio-layout active="bulk" title="AI Video Generator" eyebrow="Prompt to publish-ready video">
        <div class="grid gap-5 xl:grid-cols-[390px_minmax(0,1fr)]">
            <aside class="rounded-3xl border border-white/10 bg-slate-950/75 p-5 shadow-2xl shadow-black/30">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-black text-white">AI Director</h2>
                    <a href="{{ route('marketing.images') }}" class="rounded-xl bg-white/10 px-3 py-2 text-xs font-black text-slate-200 transition hover:bg-white/15">AI Images</a>
                </div>

                <form method="POST" action="{{ route('marketing.bulk-video.store') }}" id="aiVideoForm" class="space-y-4">
                    @csrf

                    <label class="block">
                        <span class="mb-2 block text-xs font-black text-slate-200">Video idea</span>
                        <textarea name="prompt" id="promptInput" rows="6" required maxlength="5000" placeholder="A motivational video about success and discipline" class="w-full resize-none rounded-2xl border border-white/10 bg-black/40 px-4 py-3 text-sm font-semibold text-white outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">{{ old('prompt') }}</textarea>
                        <span class="mt-2 block text-right text-[10px] font-bold text-slate-500">max 5000</span>
                    </label>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="mb-2 block text-xs font-black text-slate-200">Platform</span>
                            <select id="platformInput" class="w-full rounded-2xl border border-white/10 bg-black/40 px-4 py-3 text-sm font-bold text-white outline-none">
                                <option value="tiktok">TikTok / Reels</option>
                                <option value="youtube">YouTube</option>
                                <option value="shorts">YouTube Shorts</option>
                                <option value="linkedin">LinkedIn</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-xs font-black text-slate-200">Language</span>
                            <select name="language" id="languageInput" class="w-full rounded-2xl border border-white/10 bg-black/40 px-4 py-3 text-sm font-bold text-white outline-none">
                                <option value="vi" @selected(old('language', 'vi') === 'vi')>VI</option>
                                <option value="en" @selected(old('language') === 'en')>EN</option>
                            </select>
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="mb-2 block text-xs font-black text-slate-200">Duration</span>
                            <select name="duration_seconds" id="durationInput" class="w-full rounded-2xl border border-white/10 bg-black/40 px-4 py-3 text-sm font-bold text-white outline-none">
                                <option value="20" @selected((string) old('duration_seconds') === '20')>20s</option>
                                <option value="30" @selected(old('duration_seconds', '30') === '30')>30s</option>
                                <option value="45" @selected((string) old('duration_seconds') === '45')>45s</option>
                                <option value="60" @selected((string) old('duration_seconds') === '60')>60s</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-xs font-black text-slate-200">Frame</span>
                            <select name="aspect_ratio" id="aspectInput" class="w-full rounded-2xl border border-white/10 bg-black/40 px-4 py-3 text-sm font-bold text-white outline-none">
                                <option value="9:16" @selected(old('aspect_ratio', '9:16') === '9:16')>9:16</option>
                                <option value="16:9" @selected(old('aspect_ratio') === '16:9')>16:9</option>
                                <option value="1:1" @selected(old('aspect_ratio') === '1:1')>1:1</option>
                            </select>
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="mb-2 block text-xs font-black text-slate-200">Visual style</span>
                            <select id="visualStyleInput" class="w-full rounded-2xl border border-white/10 bg-black/40 px-4 py-3 text-sm font-bold text-white outline-none">
                                <option value="clean premium visuals, smooth motion, product-first framing, natural cinematic light">Cinematic clean</option>
                                <option value="high contrast social energy, snap zooms, dynamic cuts, kinetic motion">Viral social</option>
                                <option value="soft pastel palette, elegant transitions, refined minimal direction">Minimal modern</option>
                                <option value="dramatic movie lighting, slow motion, epic composition">Epic cinematic</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-xs font-black text-slate-200">Pacing</span>
                            <select id="demoSpeed" class="w-full rounded-2xl border border-white/10 bg-black/40 px-4 py-3 text-sm font-bold text-white outline-none">
                                <option value="0.9x">Slow</option>
                                <option value="1x" selected>Normal</option>
                                <option value="1.2x">Fast</option>
                                <option value="1.4x">Very fast</option>
                            </select>
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="mb-2 block text-xs font-black text-slate-200">AI video engine</span>
                            <select name="provider" id="assetProviderInput" class="w-full rounded-2xl border border-white/10 bg-black/40 px-4 py-3 text-sm font-bold text-white outline-none">
                                <option value="kling" @selected(old('provider', 'kling') === 'kling')>Kling cinematic</option>
                                <option value="wan" @selected(old('provider') === 'wan')>Wan motion</option>
                                <option value="ltx" @selected(old('provider') === 'ltx')>LTX local motion</option>
                                <option value="minimax" @selected(old('provider') === 'minimax')>Minimax video</option>
                                <option value="veo" @selected(old('provider') === 'veo')>Veo cinematic</option>
                                <option value="fal" @selected(old('provider') === 'fal')>fal.ai direct</option>
                                <option value="replicate" @selected(old('provider') === 'replicate')>Replicate direct</option>
                                <option value="local" @selected(old('provider') === 'local')>Local fallback</option>
                            </select>
                        </label>
                        <div class="block">
                            <span class="mb-2 block text-xs font-black text-slate-200">Renderer</span>
                            <div class="rounded-2xl border border-white/10 bg-black/40 px-4 py-3 text-sm font-bold text-white">Remotion + FFmpeg</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="mb-2 block text-xs font-black text-slate-200">Voice</span>
                            <select id="demoVoice" class="w-full rounded-2xl border border-white/10 bg-black/40 px-4 py-3 text-sm font-bold text-white outline-none">
                                <option value="female_south">female_south</option>
                                <option value="male_north">male_north</option>
                                <option value="ai_en">ai_en</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-xs font-black text-slate-200">Music</span>
                            <select id="demoMusic" class="w-full rounded-2xl border border-white/10 bg-black/40 px-4 py-3 text-sm font-bold text-white outline-none">
                                <option value="Neutral Ambient Pulse">Neutral Ambient Pulse</option>
                                <option value="Trending TikTok Pulse">Trending TikTok Pulse</option>
                                <option value="Epic Cinematic Rise">Epic Cinematic Rise</option>
                            </select>
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="mb-2 block text-xs font-black text-slate-200">Subtitles</span>
                            <select id="demoSubtitleStyle" class="w-full rounded-2xl border border-white/10 bg-black/40 px-4 py-3 text-sm font-bold text-white outline-none">
                                <option value="clean bold captions">Clean bold</option>
                                <option value="animated bold captions">Animated bold</option>
                                <option value="minimal lower-third">Minimal lower-third</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-xs font-black text-slate-200">Transition</span>
                            <select id="demoTransition" class="w-full rounded-2xl border border-white/10 bg-black/40 px-4 py-3 text-sm font-bold text-white outline-none">
                                <option value="">Auto</option>
                                <option value="fade">fade</option>
                                <option value="zoom">zoom</option>
                                <option value="whipRight">whipRight</option>
                                <option value="wipeLeft">wipeLeft</option>
                                <option value="slideUp">slideUp</option>
                            </select>
                        </label>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-black/30 p-3">
                        <label class="block">
                            <span class="mb-2 block text-xs font-black text-blue-200">Magic command</span>
                            <input id="magicCommandInput" type="text" placeholder="Ví dụ: đổi voice male_north, scene 2 2s, transition fade" class="w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-xs font-semibold text-white outline-none">
                        </label>
                        <button type="button" id="applyMagicButton" class="mt-3 w-full rounded-xl bg-white/10 px-3 py-2 text-xs font-black text-white transition hover:bg-white/15">Apply command</button>
                    </div>

                    <input type="hidden" name="style_slug" value="ai_studio">
                    <input type="hidden" name="render_provider" value="remotion">
                    <input type="hidden" name="render_immediately" value="1">
                    <input type="hidden" name="scene_overrides" id="sceneOverridesInput" value="{{ old('scene_overrides') }}">
                    <input type="hidden" name="editor_settings" id="editorSettingsInput" value="{{ old('editor_settings') }}">

                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" id="buildDemoButton" class="rounded-2xl bg-white/10 px-4 py-3 text-xs font-black text-white transition hover:bg-white/15">Build demo</button>
                        <button type="submit" class="rounded-2xl bg-blue-500 px-4 py-3 text-xs font-black text-white transition hover:bg-blue-400">Generate video</button>
                    </div>
                </form>
            </aside>

            <section class="space-y-5">
                <div class="rounded-3xl border border-white/10 bg-slate-950/75 p-5 shadow-2xl shadow-black/30">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-200">AI Preview</p>
                            <h3 class="mt-1 text-2xl font-black text-white">Storyboard before generate</h3>
                        </div>
                        <button type="button" id="playDemoButton" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-black text-white transition hover:bg-white/15">Play</button>
                    </div>

                    <div class="rounded-3xl border border-white/10 bg-black p-4">
                        <div id="demoFrame" class="relative mx-auto w-full max-w-[820px] overflow-hidden rounded-2xl border border-white/10 bg-slate-900" style="aspect-ratio: 9 / 16;">
                            <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(59,130,246,.52),transparent_45%),linear-gradient(145deg,#0f172a,#1e293b_50%,#020617)]"></div>
                            <div class="absolute left-4 top-4 rounded-lg bg-black/45 px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-blue-200" id="demoSceneBadge">Scene 1</div>
                            <div class="absolute inset-x-5 bottom-6">
                                <p class="rounded-xl bg-black/60 px-4 py-3 text-sm font-black leading-6 text-white" id="demoSubtitle">Build demo to preview your AI video timeline.</p>
                            </div>
                        </div>
                        <input id="demoProgress" type="range" min="0" max="100" value="0" step="0.1" class="mt-4 h-1.5 w-full cursor-pointer appearance-none rounded-full bg-white/15">
                    </div>
                </div>

                <div class="rounded-3xl border border-white/10 bg-white/[0.06] p-5">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-lg font-black text-white">Timeline editor</h3>
                        <span class="text-xs font-semibold text-slate-400">Edit scenes before render</span>
                    </div>
                    <div id="sceneEditorList" class="grid gap-3 md:grid-cols-2"></div>
                </div>

                <div class="rounded-3xl border border-white/10 bg-white/[0.06] p-5">
                    <h3 class="text-lg font-black text-white">Recent generations</h3>
                    <div class="mt-3 grid gap-3">
                        @forelse ($generations as $generation)
                            <a href="{{ route('marketing.bulk-video.show', $generation) }}" class="block rounded-2xl border border-white/10 bg-slate-950/60 p-4 transition hover:border-blue-400/40">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-black text-white">{{ $generation->title }}</p>
                                        <p class="mt-1 text-xs font-semibold text-slate-400">{{ \Illuminate\Support\Str::limit($generation->prompt, 120) }}</p>
                                    </div>
                                    <span class="rounded-full bg-blue-500/15 px-3 py-1 text-[10px] font-black uppercase text-blue-200">{{ $generation->status }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-2xl border border-dashed border-white/10 p-8 text-center text-sm font-semibold text-slate-400">No video generation yet.</div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    </x-marketing.studio-layout>

    <script>
        (() => {
            const form = document.getElementById('aiVideoForm');
            const promptInput = document.getElementById('promptInput');
            const durationInput = document.getElementById('durationInput');
            const platformInput = document.getElementById('platformInput');
            const aspectInput = document.getElementById('aspectInput');
            const sceneOverridesInput = document.getElementById('sceneOverridesInput');
            const editorSettingsInput = document.getElementById('editorSettingsInput');
            const buildButton = document.getElementById('buildDemoButton');
            const playButton = document.getElementById('playDemoButton');
            const progressInput = document.getElementById('demoProgress');
            const subtitleEl = document.getElementById('demoSubtitle');
            const sceneBadgeEl = document.getElementById('demoSceneBadge');
            const sceneEditorList = document.getElementById('sceneEditorList');
            const frame = document.getElementById('demoFrame');
            const speedInput = document.getElementById('demoSpeed');
            const transitionInput = document.getElementById('demoTransition');
            const musicInput = document.getElementById('demoMusic');
            const voiceInput = document.getElementById('demoVoice');
            const subtitleStyleInput = document.getElementById('demoSubtitleStyle');
            const visualStyleInput = document.getElementById('visualStyleInput');
            const magicCommandInput = document.getElementById('magicCommandInput');
            const applyMagicButton = document.getElementById('applyMagicButton');

            const baseTitles = ['Hook opening', 'Problem setup', 'Solution reveal', 'CTA ending'];
            const state = {
                scenes: [],
                isPlaying: false,
                timer: null,
                progressSeconds: 0,
                currentIndex: 0,
            };

            function splitPrompt(prompt) {
                return prompt
                    .split(/[.!?]+/)
                    .map((part) => part.trim())
                    .filter(Boolean);
            }

            function applyPlatformPreset() {
                const platform = platformInput.value;
                if (platform === 'youtube') {
                    aspectInput.value = '16:9';
                } else if (platform === 'linkedin') {
                    aspectInput.value = '1:1';
                } else {
                    aspectInput.value = '9:16';
                }
                frame.style.aspectRatio = aspectInput.value.replace(':', ' / ');
            }

            function buildDefaultScenes() {
                const promptParts = splitPrompt(promptInput.value || '');
                const totalDuration = Number.parseFloat(durationInput.value || '30');
                const perScene = Math.max(1.2, totalDuration / 4);
                const transition = transitionInput.value || '';

                state.scenes = baseTitles.map((title, index) => {
                    const sentence = promptParts[index] || promptParts[0] || 'AI generated scene';
                    return {
                        title,
                        subtitle: sentence,
                        voice_over: sentence,
                        duration: Number(perScene.toFixed(2)),
                        transition,
                        camera: 'auto',
                    };
                });
            }

            function totalDuration() {
                return state.scenes.reduce((sum, scene) => sum + Number(scene.duration || 0), 0);
            }

            function locateScene(seconds) {
                let cursor = 0;
                for (let i = 0; i < state.scenes.length; i++) {
                    cursor += Number(state.scenes[i].duration || 0);
                    if (seconds <= cursor) {
                        return i;
                    }
                }
                return Math.max(0, state.scenes.length - 1);
            }

            function setProgress(seconds) {
                const total = Math.max(0.1, totalDuration());
                state.progressSeconds = Math.max(0, Math.min(seconds, total));
                state.currentIndex = locateScene(state.progressSeconds);
                progressInput.value = ((state.progressSeconds / total) * 100).toFixed(2);
                updatePreview();
            }

            function updatePreview() {
                const scene = state.scenes[state.currentIndex];
                if (!scene) {
                    subtitleEl.textContent = 'Build demo to preview your AI video timeline.';
                    sceneBadgeEl.textContent = 'Scene 1';
                    return;
                }

                subtitleEl.textContent = scene.subtitle || scene.title;
                sceneBadgeEl.textContent = `Scene ${state.currentIndex + 1}`;
            }

            function stopPlayback(resetButton = true) {
                state.isPlaying = false;
                if (state.timer) {
                    clearInterval(state.timer);
                    state.timer = null;
                }
                if (resetButton) {
                    playButton.textContent = 'Play';
                }
            }

            function startPlayback() {
                if (state.scenes.length === 0) {
                    buildDemo();
                }

                stopPlayback(false);
                state.isPlaying = true;
                playButton.textContent = 'Pause';

                let last = Date.now();
                state.timer = setInterval(() => {
                    const now = Date.now();
                    const delta = (now - last) / 1000;
                    last = now;
                    const speedScale = Number.parseFloat((speedInput.value || '1x').replace('x', '')) || 1;
                    const next = state.progressSeconds + (delta * Math.max(0.25, speedScale));
                    const total = totalDuration();

                    if (next >= total) {
                        setProgress(total);
                        stopPlayback(true);
                        return;
                    }

                    setProgress(next);
                }, 120);
            }

            function normalizeScene(scene, fallbackTitle) {
                return {
                    title: scene.title || fallbackTitle,
                    subtitle: scene.subtitle || scene.title || fallbackTitle,
                    voice_over: scene.voice_over || scene.subtitle || scene.title || fallbackTitle,
                    duration: Math.max(1.2, Number.parseFloat(scene.duration || 2)),
                    transition: scene.transition || transitionInput.value || '',
                    camera: scene.camera || 'auto',
                };
            }

            function updateHiddenFields() {
                sceneOverridesInput.value = JSON.stringify(state.scenes.map((scene, index) => normalizeScene(scene, `Scene ${index + 1}`)));
                editorSettingsInput.value = JSON.stringify({
                    transition: transitionInput.value || '',
                    music: musicInput.value || 'Neutral Ambient Pulse',
                    subtitle_style: subtitleStyleInput.value || 'clean bold captions',
                    voice: voiceInput.value || 'female_south',
                    pacing: speedInput.value || '1x',
                    visual_direction: visualStyleInput.value || '',
                });
            }

            function renderSceneEditor() {
                sceneEditorList.innerHTML = '';

                state.scenes.forEach((scene, index) => {
                    const item = document.createElement('div');
                    item.className = 'rounded-2xl border border-white/10 bg-black/35 p-4';
                    item.innerHTML = `
                        <div class="mb-2 flex items-center justify-between">
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-blue-200">Scene ${index + 1}</p>
                            <input type="number" min="1.2" max="30" step="0.1" value="${Number(scene.duration || 2).toFixed(1)}" data-index="${index}" class="scene-duration w-20 rounded-lg border border-white/10 bg-slate-950/70 px-2 py-1 text-xs font-black text-white">
                        </div>
                        <input type="text" value="${scene.title || ''}" data-index="${index}" class="scene-title mb-2 w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-xs font-black text-white">
                        <textarea rows="3" data-index="${index}" class="scene-subtitle w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-xs font-semibold text-slate-200">${scene.subtitle || ''}</textarea>
                    `;

                    sceneEditorList.appendChild(item);
                });

                sceneEditorList.querySelectorAll('.scene-title').forEach((input) => {
                    input.addEventListener('input', (event) => {
                        const index = Number(event.target.dataset.index);
                        state.scenes[index].title = event.target.value;
                        updatePreview();
                        updateHiddenFields();
                    });
                });

                sceneEditorList.querySelectorAll('.scene-subtitle').forEach((input) => {
                    input.addEventListener('input', (event) => {
                        const index = Number(event.target.dataset.index);
                        state.scenes[index].subtitle = event.target.value;
                        state.scenes[index].voice_over = event.target.value;
                        updatePreview();
                        updateHiddenFields();
                    });
                });

                sceneEditorList.querySelectorAll('.scene-duration').forEach((input) => {
                    input.addEventListener('input', (event) => {
                        const index = Number(event.target.dataset.index);
                        state.scenes[index].duration = Math.max(1.2, Number.parseFloat(event.target.value || 2));
                        setProgress(state.progressSeconds);
                        updateHiddenFields();
                    });
                });
            }

            function buildDemo() {
                buildDefaultScenes();
                renderSceneEditor();
                stopPlayback();
                setProgress(0);
                updateHiddenFields();
            }

            function applyMagicCommand() {
                const raw = (magicCommandInput.value || '').trim();
                if (!raw) {
                    return;
                }

                const command = raw.toLowerCase();

                const sceneDuration = command.match(/scene\s*(\d+).*(\d+(?:\.\d+)?)s/);
                if (sceneDuration) {
                    const index = Number(sceneDuration[1]) - 1;
                    const seconds = Number.parseFloat(sceneDuration[2]);
                    if (state.scenes[index]) {
                        state.scenes[index].duration = Math.max(1.2, seconds);
                    }
                }

                const removeScene = command.match(/(delete|remove|xoa)\s*scene\s*(\d+)/);
                if (removeScene) {
                    const index = Number(removeScene[2]) - 1;
                    if (state.scenes[index]) {
                        state.scenes.splice(index, 1);
                    }
                }

                const transition = command.match(/transition\s+([a-z0-9]+)/);
                if (transition) {
                    transitionInput.value = transition[1];
                    state.scenes.forEach((scene) => {
                        scene.transition = transition[1];
                    });
                }

                const voice = command.match(/voice\s+([a-z0-9_]+)/);
                if (voice) {
                    voiceInput.value = voice[1];
                }

                if (command.includes('add intro') || command.includes('them intro')) {
                    state.scenes.unshift({
                        title: 'Intro hook',
                        subtitle: 'Strong opening hook to stop the scroll.',
                        voice_over: 'Strong opening hook to stop the scroll.',
                        duration: 2.0,
                        transition: transitionInput.value || '',
                        camera: 'auto',
                    });
                }

                if (state.scenes.length === 0) {
                    buildDefaultScenes();
                }

                state.scenes = state.scenes.slice(0, 12);
                renderSceneEditor();
                setProgress(Math.min(state.progressSeconds, totalDuration()));
                updateHiddenFields();
                magicCommandInput.value = '';
            }

            applyPlatformPreset();
            buildDemo();

            platformInput.addEventListener('change', () => {
                applyPlatformPreset();
                updateHiddenFields();
            });
            aspectInput.addEventListener('change', () => {
                frame.style.aspectRatio = aspectInput.value.replace(':', ' / ');
                updateHiddenFields();
            });
            buildButton.addEventListener('click', buildDemo);
            applyMagicButton.addEventListener('click', applyMagicCommand);
            magicCommandInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    applyMagicCommand();
                }
            });
            playButton.addEventListener('click', () => {
                if (state.isPlaying) {
                    stopPlayback(true);
                    return;
                }
                startPlayback();
            });
            progressInput.addEventListener('input', () => {
                stopPlayback();
                setProgress((Number.parseFloat(progressInput.value || 0) / 100) * totalDuration());
            });

            [durationInput, promptInput, speedInput, transitionInput, musicInput, voiceInput, subtitleStyleInput, visualStyleInput].forEach((input) => {
                input.addEventListener('change', updateHiddenFields);
            });

            form.addEventListener('submit', () => {
                if (state.scenes.length === 0) {
                    buildDemo();
                }
                updateHiddenFields();
            });
        })();
    </script>
</x-layouts.app>
