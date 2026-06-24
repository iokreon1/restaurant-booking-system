<div x-data="micrositeCartPage">

    <x-microsite.header title="Home" :back-href="route('home')" :show-cart="true" :center-title="true" />

    <div class="px-4 mt-4">
        <label for="catalog-search" class="sr-only">Cari menu</label>
        <input
            id="catalog-search"
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="Cari menu..."
            class="w-full rounded-xl border border-outline-variant/30 bg-surface px-4 py-2.5 text-sm text-on-surface placeholder:text-on-surface-variant focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
        />
    </div>

    <div
        class="flex gap-2 overflow-x-auto hide-scrollbar px-4 sticky top-14 bg-surface/95 backdrop-blur-md py-3 z-40 border-b border-outline-variant/10">
        @foreach ($categories as $category)
            <x-microsite.category-chip :slug="$category['slug']" :label="$category['label']" :active="$selectedCategory === $category['slug']" />
        @endforeach
    </div>

    <div wire:loading.remove class="grid grid-cols-2 gap-3 px-3 mt-4">
        @foreach ($this->filteredItems as $item)
            <x-microsite.menu-card wire:key="menu-item-{{ $item['id'] }}" :id="$item['id']" :name="$item['name']"
                :price-label="$item['price_label']" :price-value="$item['price_value']" :image-url="$item['image']" :image-alt="$item['image_alt']" :rating="$item['rating']" :available="$item['available']"
            />
        @endforeach
    </div>

    <div wire:loading class="grid grid-cols-2 gap-3 px-3 mt-4">
        @for ($i = 0; $i < 6; $i++)
            <div class="flex flex-col bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm border border-outline-variant/10 animate-pulse">
                <div class="h-32 bg-surface-container-high"></div>
                <div class="p-2.5">
                    <div class="h-3 rounded bg-surface-container-high"></div>
                    <div class="h-3 w-2/3 rounded bg-surface-container-high mt-2"></div>
                    <div class="mt-4 h-8 rounded-lg bg-surface-container-high"></div>
                </div>
            </div>
        @endfor
    </div>

    @if (count($this->filteredItems) === 0)
        <div class="px-4 py-8 text-center text-sm text-on-surface-variant">
            Menu tidak ditemukan.
        </div>
    @endif

    @if ($this->hasMoreItems)
        <div class="px-3 py-4">
            <button
                type="button"
                wire:click="loadMore"
                wire:loading.attr="disabled"
                wire:target="loadMore"
                class="w-full rounded-xl border border-outline-variant/30 bg-surface px-4 py-2.5 text-sm font-semibold text-on-surface disabled:opacity-60 disabled:cursor-not-allowed"
            >
                <span wire:loading.remove wire:target="loadMore">Load More</span>
                <span wire:loading wire:target="loadMore">Loading...</span>
            </button>
        </div>
    @endif

    <div
        x-show="$store.micrositeCart.totalItems() > 0"
        x-cloak
        class="fixed bottom-28 left-1/2 z-50 w-full max-w-[480px] -translate-x-1/2 px-4 transition-all duration-200"
    >
        <a
            href="{{ route('microsite.reservation') }}"
            class="flex items-center justify-between rounded-2xl bg-[#2D6A4F] px-5 py-4 text-base font-bold text-white shadow-xl"
        >
            <span x-text="$store.micrositeCart.selectedLabel()">0 item selected</span>
            <span class="material-symbols-outlined text-2xl leading-none">shopping_cart</span>
        </a>
    </div>

    <div x-show="$store.micrositeCart.totalItems() > 0" x-cloak class="h-24"></div>
</div>
