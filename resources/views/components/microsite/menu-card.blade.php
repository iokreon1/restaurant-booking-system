@props([
    'id',
    'name',
    'priceLabel',
    'priceValue' => 0,
    'imageUrl',
    'imageAlt',
    'rating',
    'available' => true,
    'quantity' => 0,
])

<div {{ $attributes->class(['flex flex-col bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm border border-outline-variant/10']) }}>
    <div class="relative h-32 overflow-hidden">
        <img alt="{{ $imageAlt }}" class="w-full h-full object-cover" src="{{ $imageUrl }}"/>
        @if ($available)
            <div class="absolute top-1.5 left-1.5">
                <span class="bg-tertiary/90 text-white px-1 py-[2px] rounded text-[8px] font-bold backdrop-blur-sm inline-flex items-center gap-0.5">
                    <span class="material-symbols-outlined text-[9px] leading-none text-amber-300" style="font-variation-settings: 'FILL' 1; font-size: 1rem;">star</span>
                    {{ $rating }}
                </span>
            </div>
        @endif
    </div>
    <div class="p-2.5 flex flex-col flex-1">
        <h3 class="font-headline text-sm font-bold text-on-surface leading-tight line-clamp-1">{{ $name }}</h3>
        <div class="flex items-baseline gap-1 mt-0.5">
            <span class="font-headline font-extrabold text-sm text-primary">{{ $priceLabel }}</span>
        </div>
        <div class="mt-auto pt-3">
            <div class="flex items-center justify-between gap-2">
                <button
                    type="button"
                    x-on:click="$store.micrositeCart.decrement({{ $id }}, @js($name), {{ $priceValue }}, @js($imageUrl))"
                    x-bind:disabled="$store.micrositeCart.quantity({{ $id }}) === 0"
                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-outline-variant text-primary transition active:bg-primary/10 disabled:cursor-not-allowed disabled:opacity-40"
                >
                    <span class="material-symbols-outlined text-sm">remove</span>
                </button>
                <span class="min-w-6 text-center text-sm font-bold text-on-surface" x-text="$store.micrositeCart.quantity({{ $id }})">{{ $quantity }}</span>
                <button
                    type="button"
                    x-on:click="$store.micrositeCart.increment({{ $id }}, @js($name), {{ $priceValue }}, @js($imageUrl))"
                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-outline-variant text-primary transition active:bg-primary/10"
                >
                    <span class="material-symbols-outlined text-sm">add</span>
                </button>
            </div>
        </div>
    </div>
</div>
