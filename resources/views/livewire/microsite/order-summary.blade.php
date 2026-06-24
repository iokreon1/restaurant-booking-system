<div x-data="micrositeCartPage">
    <x-microsite.header :title="'Ringkasan Order'" :back-href="route('microsite.reservation')"
                        :show-cart="false"
                        :center-title="true"/>

    <div class="pt-4 pb-40 px-4">
        <div class="mb-6">
            <h2 class="font-headline text-2xl font-bold tracking-tight text-on-surface">Ringkasan Order</h2>
            <p class="text-on-surface-variant text-sm mt-1">Periksa kembali menu yang dipilih dan detail reservasi Anda.</p>
        </div>

        <div x-show="$store.micrositeCart.totalItems() === 0" x-cloak class="rounded-xl bg-surface-container p-6 text-center">
            <p class="text-sm text-on-surface-variant">Keranjang Anda masih kosong.</p>
            <a href="{{ route('microsite.menu') }}" class="inline-flex mt-4 rounded-lg bg-primary px-4 py-2 text-xs font-semibold text-on-primary">
                Kembali ke menu
            </a>
        </div>

        @error('order')
            <div class="mt-4 rounded-2xl border border-error/20 bg-error/5 px-4 py-3 text-sm font-medium text-error">
                {{ $message }}
            </div>
        @enderror

        @error('cartItems')
            <div class="mt-4 rounded-2xl border border-error/20 bg-error/5 px-4 py-3 text-sm font-medium text-error">
                {{ $message }}
            </div>
        @enderror

        <div x-show="$store.micrositeCart.totalItems() > 0" x-cloak class="space-y-4">
            <template x-for="item in $store.micrositeCart.cartItems()" :key="item.id">
                <div class="bg-surface-container-lowest rounded-xl p-4 flex gap-4">
                    <div class="w-20 h-20 rounded-lg overflow-hidden flex-shrink-0 bg-surface-container">
                        <img :alt="item.name" class="w-full h-full object-cover" :src="item.image || '/images/default.png'" />
                    </div>
                    <div class="flex flex-col justify-between flex-grow">
                        <div>
                            <h3 class="font-headline font-bold text-on-surface leading-tight" x-text="item.name"></h3>
                            <p class="text-secondary font-bold mt-1 text-sm" x-text="$store.micrositeCart.formatRupiah(item.unitPrice)"></p>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <div class="flex items-center bg-surface-container rounded-full px-2 py-1">
                                <button type="button" class="w-6 h-6 flex items-center justify-center text-primary active:scale-90 transition-all" x-on:click="$store.micrositeCart.decrementCartItem(item.id)">
                                    <span class="material-symbols-outlined text-sm">remove</span>
                                </button>
                                <span class="px-3 font-semibold text-sm" x-text="item.quantity"></span>
                                <button type="button" class="w-6 h-6 flex items-center justify-center text-primary active:scale-90 transition-all" x-on:click="$store.micrositeCart.incrementCartItem(item.id)">
                                    <span class="material-symbols-outlined text-sm">add</span>
                                </button>
                            </div>
                            <button type="button" class="text-outline hover:text-error active:scale-90 transition-all" x-on:click="$store.micrositeCart.remove(item.id)">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <div class="mt-8 bg-surface-container-low rounded-2xl p-6">
                <h3 class="font-headline text-lg font-bold text-on-surface mb-4">Ringkasan Pembayaran</h3>
                <div class="space-y-3 text-sm">
                    <div class="pt-4 border-t border-outline-variant/15 flex justify-between items-center">
                        <span class="font-headline font-bold text-on-surface">Total</span>
                        <span class="font-headline font-extrabold text-lg text-secondary" x-text="$store.micrositeCart.formatRupiah($store.micrositeCart.subtotal())"></span>
                    </div>
                </div>
            </div>

        </div>

        <div class="mt-6 rounded-2xl bg-surface-container-low p-6">
            <h3 class="mb-4 font-headline text-lg font-bold text-on-surface">Detail Reservasi</h3>

            @if ($this->reservationSummary)
                <div class="space-y-3 text-sm text-on-surface">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-on-surface-variant">Nama</span>
                        <span class="text-right font-semibold">{{ $this->reservationSummary['customer_name'] }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-on-surface-variant">Email</span>
                        <span class="text-right font-semibold">{{ $this->reservationSummary['customer_email'] }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-on-surface-variant">Telepon</span>
                        <span class="text-right font-semibold">{{ $this->reservationSummary['customer_phone'] }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <span class="text-on-surface-variant">Notes</span>
                        <span class="text-right font-semibold">{{ $this->reservationSummary['notes'] }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-on-surface-variant">Tanggal</span>
                        <span class="text-right font-semibold">{{ $this->reservationSummary['date_label'] }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-on-surface-variant">Waktu</span>
                        <span class="text-right font-semibold">{{ $this->reservationSummary['time_label'] }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-on-surface-variant">Jumlah Orang</span>
                        <span class="text-right font-semibold">{{ $this->reservationSummary['party_size_label'] }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-on-surface-variant">Meja</span>
                        <span class="text-right font-semibold">{{ $this->reservationSummary['table_label'] }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-on-surface-variant">Lokasi</span>
                        <span class="text-right font-semibold">{{ $this->reservationSummary['location_label'] }}</span>
                    </div>
                </div>
            @else
                <div class="rounded-xl bg-surface-container p-4 text-sm text-on-surface-variant">
                    Detail reservasi belum tersedia. Silakan pilih tempat terlebih dahulu.
                </div>
            @endif
        </div>
    </div>

    <div class="fixed bottom-20 left-1/2 z-50 w-full max-w-[480px] -translate-x-1/2 border-t border-outline-variant/15 bg-white/80 p-6 shadow-[0px_-10px_30px_rgba(0,0,0,0.05)] backdrop-blur-xl">
        <button
            type="button"
            x-bind:disabled="$store.micrositeCart.totalItems() === 0"
            x-on:click="let items = $store.micrositeCart.cartItems(); $wire.confirmOrder(items).then(() => $store.micrositeCart.clear())"
            wire:loading.attr="disabled"
            wire:target="confirmOrder"
            class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#2D6A4F] py-4 font-headline font-bold text-white shadow-lg transition-all duration-200 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
        >
            <span wire:loading.remove wire:target="confirmOrder">Konfirmasi Order</span>
            <span wire:loading wire:target="confirmOrder">Membuat Booking...</span>
            <span class="material-symbols-outlined" data-icon="arrow_forward">arrow_forward</span>
        </button>
    </div>
</div>
