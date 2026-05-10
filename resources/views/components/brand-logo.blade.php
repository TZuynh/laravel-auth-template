@props(['compact' => false, 'dark' => true])

@php
    $textColor = $dark ? 'text-white' : 'text-slate-950';
    $subColor = $dark ? 'text-white/80' : 'text-slate-500';
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    <svg class="{{ $compact ? 'h-10 w-10' : 'h-12 w-12' }} shrink-0" viewBox="0 0 96 96" fill="none" aria-hidden="true">
        <path d="M30 22c12-12 32-11 45 3 12 13 12 33 0 46-13 14-35 15-49 2-12-12-14-29-5-43" stroke="currentColor" stroke-width="14" stroke-linecap="round" class="{{ $textColor }}"/>
        <path d="M20 25c17 2 27 10 36 20 6 7 15 9 22 4" stroke="currentColor" stroke-width="13" stroke-linecap="round" class="{{ $textColor }}"/>
        <path d="M53 45c-5-7-10-12-18-16" stroke="currentColor" stroke-width="13" stroke-linecap="round" class="{{ $textColor }}"/>
    </svg>

    @unless ($compact)
        <div class="leading-none" data-brand-wordmark>
            <div class="{{ $textColor }} text-[28px] font-black tracking-tight">Owl</div>
            <div class="{{ $subColor }} mt-0.5 text-xs font-black lowercase tracking-tight">agency</div>
        </div>
    @endunless
</div>
