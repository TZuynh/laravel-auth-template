@props([
    'label',
    'name' => null,
    'type' => 'select',
    'options' => [],
    'placeholder' => null,
])

<label class="block">
    <span class="mb-2 block text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">{{ $label }}</span>

    @if ($type === 'textarea')
        <textarea name="{{ $name }}" rows="4" placeholder="{{ $placeholder }}"
                  class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm font-semibold text-white outline-none transition placeholder:text-slate-500 focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10"></textarea>
    @else
        <select name="{{ $name }}"
                class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm font-black text-white outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10">
            @if ($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach ($options as $option)
                <option value="{{ $option['value'] ?? $option['id'] ?? $option['label'] ?? $option }}">
                    {{ $option['label'] ?? $option['name'] ?? $option['title'] ?? $option }}
                </option>
            @endforeach
        </select>
    @endif
</label>
