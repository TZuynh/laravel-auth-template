@props([
    'active' => 'director',
    'title' => 'AI Director',
    'eyebrow' => 'Cinematic AI Commerce Studio',
])

@php
    $tabs = [
        ['key' => 'content', 'label' => 'Content AI', 'route' => 'marketing.content.index'],
        ['key' => 'images', 'label' => 'AI Images', 'route' => 'marketing.images'],
        ['key' => 'brain', 'label' => 'Brain AI', 'route' => 'marketing.brain.index'],
    ];
@endphp

<section class="relative -m-4 min-h-[calc(100vh-112px)] overflow-hidden bg-slate-950 text-white md:-m-6">
    <style>
        .cinema-sheen {
            background:
                radial-gradient(circle at 20% 10%, rgba(124, 58, 237, .32), transparent 28rem),
                radial-gradient(circle at 78% 4%, rgba(37, 99, 235, .22), transparent 26rem),
                linear-gradient(135deg, #020617 0%, #111827 50%, #020617 100%);
        }

        .cinema-grid {
            background-image:
                linear-gradient(rgba(148, 163, 184, .08) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148, 163, 184, .08) 1px, transparent 1px);
            background-size: 44px 44px;
            mask-image: linear-gradient(to bottom, rgba(0, 0, 0, .85), transparent);
        }

        .cinema-player-glow {
            box-shadow: 0 0 0 1px rgba(255, 255, 255, .08), 0 30px 90px rgba(59, 130, 246, .24);
        }

        .cinema-float {
            animation: cinemaFloat 7s ease-in-out infinite;
        }

        @keyframes cinemaFloat {
            0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
            50% { transform: translate3d(0, -12px, 0) scale(1.01); }
        }
    </style>

    <div class="cinema-sheen absolute inset-0"></div>
    <div class="cinema-grid absolute inset-0 opacity-70"></div>
    <div class="relative z-10 px-4 py-5 md:px-8 md:py-7">
        <div class="mb-6 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.34em] text-blue-200/70">{{ $eyebrow }}</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-white md:text-4xl">{{ $title }}</h1>
                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-300">
                    Viết nội dung social, tạo hình ảnh quảng cáo chuyên nghiệp và huấn luyện bộ nhớ thương hiệu cho AI.
                </p>
            </div>

            <div class="flex flex-wrap gap-2 rounded-2xl border border-white/10 bg-white/5 p-2 shadow-2xl shadow-black/30 backdrop-blur-xl">
                @foreach ($tabs as $tab)
                    <a href="{{ route($tab['route']) }}"
                       class="rounded-xl px-4 py-2 text-xs font-black transition {{ $active === $tab['key'] ? 'bg-white text-slate-950 shadow-lg shadow-blue-500/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        {{ $slot }}
    </div>
</section>
