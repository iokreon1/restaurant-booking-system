<div
    x-data="{ partySize: {{ $partySize }} }"
    x-init="$watch('partySize', value => $wire.set('partySize', value))"
    class="relative flex h-auto min-h-screen w-full max-w-[480px] mx-auto flex-col bg-surface overflow-x-hidden"
>
    <x-microsite.header :title="'Reservasi Meja'" :back-href="route('microsite.menu')" :show-cart="false"
                        :center-title="true"/>

    <main class="flex-1 px-6 pt-4 pb-56">

        <div class="mb-8">
            <h3 class="text-on-surface font-headline tracking-tight text-2xl font-bold leading-tight mb-2">Detail
                Reservasi</h3>
            <p class="text-on-surface-variant font-body text-base leading-relaxed">Silakan lengkapi data kunjungan Anda
                untuk memastikan ketersediaan tempat.</p>
        </div>

        <div class="space-y-8">
            <div class="space-y-4">
                <div class="flex flex-col gap-2">
                    <label for="customer-name" class="text-on-surface font-headline text-xs font-bold uppercase tracking-widest opacity-70">
                        Nama Pemesan
                    </label>
                    <input
                        id="customer-name"
                        type="text"
                        wire:model="customerName"
                        class="h-12 rounded-lg border-none bg-surface-container-high px-4 font-body text-sm text-on-surface transition-all focus:ring-2 focus:ring-primary/20"
                        placeholder="Masukkan nama lengkap"
                    />
                    @error('customerName')
                        <p class="text-sm font-medium text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-2">
                        <label for="customer-email" class="text-on-surface font-headline text-xs font-bold uppercase tracking-widest opacity-70">
                            Email
                        </label>
                        <input
                            id="customer-email"
                            type="email"
                            wire:model="customerEmail"
                            class="h-12 rounded-lg border-none bg-surface-container-high px-4 font-body text-sm text-on-surface transition-all focus:ring-2 focus:ring-primary/20"
                            placeholder="nama@email.com"
                        />
                        @error('customerEmail')
                            <p class="text-sm font-medium text-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="customer-phone" class="text-on-surface font-headline text-xs font-bold uppercase tracking-widest opacity-70">
                            Nomor Telepon
                        </label>
                        <input
                            id="customer-phone"
                            type="tel"
                            wire:model="customerPhone"
                            class="h-12 rounded-lg border-none bg-surface-container-high px-4 font-body text-sm text-on-surface transition-all focus:ring-2 focus:ring-primary/20"
                            placeholder="08xxxxxxxxxx"
                        />
                        @error('customerPhone')
                            <p class="text-sm font-medium text-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="reservation-notes" class="text-on-surface font-headline text-xs font-bold uppercase tracking-widest opacity-70">
                        Notes
                    </label>
                    <textarea
                        id="reservation-notes"
                        wire:model="notes"
                        rows="4"
                        class="rounded-lg border-none bg-surface-container-high px-4 py-3 font-body text-sm text-on-surface transition-all focus:ring-2 focus:ring-primary/20"
                        placeholder="Tambahkan catatan untuk reservasi Anda"
                    ></textarea>
                    @error('notes')
                        <p class="text-sm font-medium text-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-2">
                    <label class="text-on-surface font-headline text-xs font-bold uppercase tracking-widest opacity-70">Tanggal</label>
                    <input wire:model="reservationDate"
                           class="w-full h-12 bg-surface-container-high border-none rounded-lg px-4 font-body text-sm text-on-surface focus:ring-2 focus:ring-primary/20 transition-all"
                           type="date"/>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-on-surface font-headline text-xs font-bold uppercase tracking-widest opacity-70">Waktu</label>
                    <select wire:model="reservationTime"
                            class="h-12 bg-surface-container-high border-none rounded-lg px-4 font-body text-sm text-on-surface focus:ring-2 focus:ring-primary/20 transition-all">
                        @foreach (range(8, 22) as $hour)
                            @php($time = sprintf('%02d:00', $hour))
                            <option value="{{ $time }}">{{ $time }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <label class="text-on-surface font-headline text-xs font-bold uppercase tracking-widest opacity-70">Jumlah
                    Orang</label>
                <div class="flex items-center bg-surface-container-high rounded-lg p-1 h-12">
                    <button x-on:click="partySize = Math.max(1, partySize - 1)"
                            class="flex-1 h-full flex items-center justify-center text-primary active:scale-95 transition-transform"
                            type="button">
                        <span class="material-symbols-outlined text-xl">remove</span>
                    </button>
                    <span class="flex-[2] text-center font-headline font-bold text-base" x-text="`${partySize} Orang`">{{ $partySize }} Orang</span>
                    <button x-on:click="partySize = Math.min(12, partySize + 1)"
                            class="flex-1 h-full flex items-center justify-center text-primary active:scale-95 transition-transform"
                            type="button">
                        <span class="material-symbols-outlined text-xl">add</span>
                    </button>
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <label class="text-on-surface font-headline text-xs font-bold uppercase tracking-widest opacity-70">Pilih
                    Area &amp; Meja</label>
                @error('selectedTableId')
                    <p class="text-sm font-medium text-error">{{ $message }}</p>
                @enderror
                <div class="flex flex-col gap-3 max-h-[500px] overflow-y-auto hide-scrollbar -mx-1 px-1">
                    @forelse ($this->tables as $table)
                        @php($tableInputId = 'table-selection-'.$table['id'])
                        <label wire:key="table-option-{{ $table['id'] }}" for="{{ $tableInputId }}" class="relative block cursor-pointer group">
                            <input
                                id="{{ $tableInputId }}"
                                wire:model.live.number="selectedTableId"
                                value="{{ $table['id'] }}"
                                @checked($selectedTableId === $table['id'])
                                class="peer sr-only"
                                name="table_selection"
                                type="radio"
                            />
                            <div
                                class="flex items-center justify-between p-4 rounded-xl border-2 border-transparent bg-surface-container-low peer-checked:border-secondary peer-checked:bg-secondary-container/20 transition-all hover:bg-surface-container-high">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-lg bg-surface-container-highest text-on-surface-variant peer-checked:bg-secondary/20 peer-checked:text-secondary">
                                        <span class="material-symbols-outlined text-2xl">table_restaurant</span>
                                    </div>
                                    <div>
                                        <h4 class="font-headline font-bold text-sm">
                                            Meja {{ $table['table_number'] }}</h4>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span
                                                class="text-[10px] px-1.5 py-0.5 rounded bg-surface-container-highest font-bold text-on-surface-variant uppercase tracking-wider">
                                                {{ $table['location_description'] }}
                                            </span>
                                            <span
                                                class="text-[10px] px-1.5 py-0.5 rounded bg-secondary/10 font-bold text-secondary uppercase tracking-wider">
                                                Max {{ $table['capacity'] }} Orang
                                            </span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </label>
                    @empty
                        <div class="p-4 rounded-xl bg-surface-container text-center text-sm text-on-surface-variant">
                            Tidak ada meja tersedia untuk area dan jumlah orang ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </main>

    <div class="fixed bottom-20 left-1/2 z-50 w-full max-w-[480px] -translate-x-1/2 border-t border-outline-variant/15 bg-white/80 p-6 shadow-[0px_-10px_30px_rgba(0,0,0,0.05)] backdrop-blur-xl">
        <button
            type="button"
            wire:click="proceedToSummary"
            wire:loading.attr="disabled"
            wire:target="proceedToSummary"
            class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#2D6A4F] py-4 font-headline font-bold text-white shadow-lg transition-all duration-200 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
        >
            <span wire:loading.remove wire:target="proceedToSummary">Lanjutkan ke Ringkasan</span>
            <span wire:loading wire:target="proceedToSummary">Menyimpan Reservasi...</span>
            <span class="material-symbols-outlined" data-icon="arrow_forward">arrow_forward</span>
        </button>
    </div>
</div>
