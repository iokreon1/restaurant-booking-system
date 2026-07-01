@props([
    'brand' => 'The Organic Atelier',
    'title' => null,
    'cartCount' => 0,
    'backHref' => null,
    'cartHref' => null,
    'showCart' => true,
    'centerTitle' => false,
])

<header class="fixed top-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] z-50 flex items-center justify-between px-4 h-14 bg-[#f9f9f7] dark:bg-stone-900 border-b border-outline-variant/10">
    @if ($centerTitle)
        @if ($backHref)
            <a href="{{ $backHref }}" class="w-10 h-10 flex items-center justify-center rounded-full active:scale-95 duration-150">
                <span class="material-symbols-outlined text-[#634018] dark:text-[#95d4b3]" data-icon="arrow_back">arrow_back</span>
            </a>
        @else
            <div class="w-10 h-10"></div>
        @endif
        <h1 class="flex-1 text-center font-headline font-bold text-lg text-[#2d6a4f] dark:text-[#a7e7c4]">
            {{ $title ?? $brand }}
        </h1>
        <div class="w-10 h-10 flex items-center justify-center relative">
            @if ($showCart)
                <a href="{{ $cartHref ?? route('microsite.summary') }}" class="p-2 rounded-full active:scale-95 duration-150">
                    <span class="material-symbols-outlined text-[#634018] dark:text-[#95d4b3]" data-icon="shopping_cart">shopping_cart</span>
                </a>
                @if ($cartCount > 0)
                    <span class="absolute top-1 right-1 flex h-4 min-w-4 px-0.5 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-on-primary">{{ $cartCount > 9 ? '9+' : $cartCount }}</span>
                @endif
            @endif
        </div>
    @else
        <div class="flex items-center gap-2">
            @if ($backHref)
                <a href="{{ $backHref }}" class="p-2 rounded-full active:scale-95 duration-150">
                    <span class="material-symbols-outlined text-[#634018] dark:text-[#95d4b3]" data-icon="arrow_back">arrow_back</span>
                </a>
            @endif
            <h1 class="font-headline font-bold text-lg text-[#2d6a4f] dark:text-[#a7e7c4]">{{ $title ?? $brand }}</h1>
        </div>
        <div class="relative">
            @if ($showCart)
                <a href="{{ $cartHref ?? route('microsite.summary') }}" class="p-2 rounded-full active:scale-95 duration-150">
                    <span class="material-symbols-outlined text-[#634018] dark:text-[#95d4b3]" data-icon="shopping_cart">shopping_cart</span>
                </a>
                @if ($cartCount > 0)
                    <span class="absolute top-1 right-1 flex h-4 min-w-4 px-0.5 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-on-primary">{{ $cartCount > 9 ? '9+' : $cartCount }}</span>
                @endif
            @endif
        </div>
    @endif
</header>
