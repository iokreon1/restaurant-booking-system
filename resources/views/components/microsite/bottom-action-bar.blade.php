@props([
    'href',
    'label',
    'icon' => 'arrow_forward',
    'subtext' => null,
    'bottomClass' => 'bottom-0',
])

<div @class([
    'fixed left-1/2 -translate-x-1/2 w-full max-w-[480px] z-50 p-6 bg-white/80 backdrop-blur-xl border-t border-outline-variant/15 shadow-[0px_-10px_30px_rgba(0,0,0,0.05)] flex flex-col items-center',
    $bottomClass,
])>
    <a href="{{ $href }}" class="w-full max-w-md py-4 bg-[#2D6A4F] text-white rounded-xl font-headline font-bold flex items-center justify-center gap-2 shadow-lg active:scale-95 transition-all duration-200">
        {{ $label }}
        <span class="material-symbols-outlined" data-icon="{{ $icon }}">{{ $icon }}</span>
    </a>

    @if ($subtext)
        <p class="mt-3 text-[10px] text-outline text-center font-medium uppercase tracking-[0.1em]">{{ $subtext }}</p>
    @endif
</div>
