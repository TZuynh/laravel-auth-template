@props([
    'title' => null,
    'subtitle' => null,
    'accent' => 'from-blue-500 to-violet-500',
])

<div {{ $attributes->merge(['class' => 'rounded-3xl border border-white/10 bg-white/[0.07] p-5 shadow-2xl shadow-black/25 backdrop-blur-2xl transition duration-300 hover:border-white/20 hover:bg-white/[0.09]']) }}>
    @if ($title || $subtitle)
        <div class="mb-5 flex items-start justify-between gap-4">
            <div>
                @if ($title)
                    <h2 class="text-lg font-black tracking-tight text-white">{{ $title }}</h2>
                @endif
                @if ($subtitle)
                    <p class="mt-1 text-xs font-semibold leading-5 text-slate-400">{{ $subtitle }}</p>
                @endif
            </div>
            <div class="h-10 w-10 rounded-2xl bg-gradient-to-br {{ $accent }} opacity-90 shadow-lg shadow-blue-950/40"></div>
        </div>
    @endif

    {{ $slot }}
</div>
