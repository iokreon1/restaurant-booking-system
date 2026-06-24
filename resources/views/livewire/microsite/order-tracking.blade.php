<div class="bg-surface text-on-surface min-h-screen pb-32">
    <x-microsite.header :title="'Status Booking'" :back-href="route('microsite.menu')" :show-cart="false" :center-title="true" />

    <div class="px-4 pt-4 space-y-6">
        <div class="mb-2">
            <h2 class="font-headline text-2xl font-bold tracking-tight text-on-surface">Masukkan order ID Anda terlebih dahulu</h2>
            <p class="mt-1 text-sm text-on-surface-variant">
                Gunakan kode booking seperti <span class="font-bold">BK-ABCDEFGH</span> untuk melihat status reservasi,
                detail meja, dan ringkasan pembayaran.
            </p>
        </div>

        <section class="rounded-2xl bg-surface-container-low p-6">
            <form wire:submit="searchOrder" class="space-y-3">
                <label for="order-id" class="block text-xs font-bold uppercase tracking-widest text-on-surface opacity-70">
                    Order ID
                </label>
                <div class="flex items-stretch gap-3">
                    <input
                        id="order-id"
                        type="text"
                        wire:model="orderId"
                        placeholder="Contoh: BK-ABCDEFGH"
                        class="min-w-0 flex-1 rounded-xl border-none bg-surface-container-high px-4 py-3 text-sm font-semibold tracking-[0.08em] text-on-surface outline-none transition-all focus:ring-2 focus:ring-primary/20 placeholder:text-on-surface-variant"
                    />
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="searchOrder"
                        class="rounded-xl bg-primary px-5 py-3 text-sm font-bold text-on-primary transition-all active:scale-95 disabled:cursor-not-allowed disabled:opacity-70"
                    >
                        <span wire:loading.remove wire:target="searchOrder">Cari</span>
                        <span wire:loading wire:target="searchOrder">Mencari...</span>
                    </button>
                </div>

                @error('orderId')
                    <div class="rounded-xl border border-error/20 bg-error/5 px-4 py-3 text-sm font-medium text-error">
                        {{ $message }}
                    </div>
                @enderror
            </form>
        </section>


        @if ($bookingDetails)
            <section class="rounded-2xl bg-surface-container-low p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-on-surface-variant">Booking ditemukan</p>
                        <h3 class="mt-2 font-headline text-2xl font-bold text-on-surface">{{ $bookingDetails['booking_reference'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-on-surface-variant">{{ $bookingDetails['booking_status_description'] }}</p>
                    </div>
                    <div class="rounded-xl bg-secondary-container px-3 py-2 text-right text-on-secondary-container">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em]">Status</p>
                        <p class="mt-1 text-sm font-bold">{{ $bookingDetails['booking_status_label'] }}</p>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-2 gap-4">
                <div class="rounded-2xl bg-surface-container-low p-5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-on-surface-variant">Pembayaran</p>
                    <p class="mt-3 font-headline text-lg font-bold text-on-surface">{{ $bookingDetails['payment_status_label'] }}</p>
                </div>
                <div class="rounded-2xl bg-surface-container-low p-5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-on-surface-variant">Transaksi</p>
                    <p class="mt-3 font-headline text-lg font-bold text-on-surface">{{ $bookingDetails['transaction_status_label'] }}</p>
                </div>
            </section>

            <section class="rounded-2xl bg-surface-container-low p-6">
                <h4 class="font-headline text-lg font-bold text-on-surface">Detail Reservasi</h4>
                <div class="mt-4 space-y-4 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-on-surface-variant">Tanggal</span>
                        <span class="text-right font-semibold text-on-surface">{{ $bookingDetails['date_label'] }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-on-surface-variant">Waktu</span>
                        <span class="text-right font-semibold text-on-surface">{{ $bookingDetails['time_label'] }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-on-surface-variant">Meja</span>
                        <span class="text-right font-semibold text-on-surface">{{ $bookingDetails['table_label'] }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-on-surface-variant">Jumlah Tamu</span>
                        <span class="text-right font-semibold text-on-surface">{{ $bookingDetails['guest_count_label'] }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-on-surface-variant">Total</span>
                        <span class="text-right font-semibold text-secondary">{{ $bookingDetails['total_amount_label'] }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-4 border-t border-outline-variant/15 pt-4">
                        <span class="text-on-surface-variant">Catatan</span>
                        <span class="text-right font-semibold text-on-surface">{{ $bookingDetails['note'] }}</span>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl bg-surface-container-low p-6">
                <div class="flex items-center justify-between gap-4">
                    <h4 class="font-headline text-lg font-bold text-on-surface">Item Pesanan</h4>
                    <span class="rounded-full bg-secondary/10 px-3 py-1 text-xs font-semibold text-secondary">
                        {{ count($bookingDetails['items']) }} item
                    </span>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse ($bookingDetails['items'] as $item)
                        <div class="flex items-center justify-between gap-4 rounded-xl bg-surface-container-high px-4 py-3">
                            <div class="min-w-0">
                                <p class="font-semibold text-on-surface">{{ $item['name'] }}</p>
                                <p class="text-sm text-on-surface-variant">{{ $item['quantity_label'] }}</p>
                            </div>
                            <p class="text-right font-bold text-on-surface">{{ $item['subtotal_label'] }}</p>
                        </div>
                    @empty
                        <div class="rounded-xl bg-surface-container-high px-4 py-3 text-sm text-on-surface-variant">
                            Belum ada item pesanan yang tersimpan untuk booking ini.
                        </div>
                    @endforelse
                </div>
            </section>
        @endif
    </div>
</div>
