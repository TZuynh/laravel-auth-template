@props(['scene'])

<article class="group relative overflow-hidden rounded-3xl border border-white/10 bg-white/[0.06] p-5 shadow-xl shadow-black/20 transition duration-300 hover:-translate-y-1 hover:border-blue-300/40">
    <div class="absolute -right-10 -top-12 h-32 w-32 rounded-full bg-blue-500/20 blur-3xl transition group-hover:bg-violet-500/30"></div>
    <div class="relative z-10">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-blue-300">Scene {{ $scene['order'] ?? '1' }}</p>
                <h3 class="mt-2 text-base font-black text-white">{{ $scene['title'] ?? 'Untitled scene' }}</h3>
            </div>
            <span class="rounded-full bg-white/10 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-slate-300">
                {{ $scene['status'] ?? 'draft' }}
            </span>
        </div>

        <p class="mt-4 min-h-14 text-sm font-semibold leading-6 text-slate-300">{{ $scene['description'] ?? '' }}</p>

        <div class="mt-5 grid grid-cols-2 gap-3 text-xs font-bold text-slate-300">
            <div class="rounded-2xl bg-slate-950/50 p-3">
                <span class="block text-[9px] uppercase tracking-widest text-slate-500">Camera</span>
                {{ $scene['camera'] ?? 'dolly_in' }}
            </div>
            <div class="rounded-2xl bg-slate-950/50 p-3">
                <span class="block text-[9px] uppercase tracking-widest text-slate-500">Transition</span>
                {{ $scene['transition'] ?? 'Bloom' }}
            </div>
        </div>

        <div class="mt-4 rounded-2xl border border-white/10 bg-black/30 p-4">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-violet-300">Voice-over / Subtitle</p>
            <p class="mt-2 text-sm font-semibold italic leading-6 text-slate-300">{{ $scene['subtitle'] ?? 'Waiting for AI voice-over.' }}</p>
        </div>
    </div>
</article>
