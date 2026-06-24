@props([
    'slug',
    'label',
    'active' => false,
])

<button
    type="button"
    wire:click="selectCategory('{{ $slug }}')"
    @class([
        'flex-none px-4 py-1.5 rounded-full font-semibold text-xs shadow-sm' => $active,
        'flex-none px-4 py-1.5 rounded-full font-medium text-xs' => ! $active,
        'bg-tertiary text-on-tertiary' => $active,
        'bg-surface-container-high text-on-surface-variant' => ! $active,
    ])
>
    {{ $label }}
</button>
