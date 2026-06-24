<div>
    <div class="mt-16 flex flex-col gap-6 p-8">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <a
                    class="mb-3 inline-flex items-center gap-1 text-sm font-semibold text-[#025864] hover:underline"
                    href="{{ route('admin.bookings.show', $booking) }}"
                    wire:navigate
                >
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Kembali ke detail booking
                </a>
                <h2 class="text-3xl font-bold text-[#0A1628]">Ubah pesanan</h2>
                <p class="mt-1 font-mono text-lg font-semibold text-[#025864]">{{ $booking->booking_reference }}</p>
                <p class="mt-1 text-sm text-slate-500">Hanya isi pesanan (menu &amp; jumlah) yang dapat diubah. Total mengikuti harga menu saat ini.</p>
            </div>
        </div>

        <div class="rounded-[12px] border border-[#D1D9D9] bg-white p-6 shadow-sm card-shadow">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Detail reservasi (tidak diubah)</h3>
            <dl class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <dt class="text-xs font-bold uppercase text-slate-500">Tamu</dt>
                    <dd class="mt-0.5 font-medium text-[#0A1628]">{{ $booking->user?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase text-slate-500">Meja</dt>
                    <dd class="mt-0.5 text-slate-700">#{{ $booking->table?->table_number ?? '—' }} ({{ $booking->table?->capacity ?? '—' }} kursi)</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase text-slate-500">Tanggal &amp; waktu</dt>
                    <dd class="mt-0.5 text-slate-700">
                        {{ $booking->booking_date->format('d M Y') }}
                        @php
                            $t = $booking->booking_time;
                            $timeStr = $t instanceof \DateTimeInterface ? $t->format('H:i') : substr((string) $t, 0, 5);
                        @endphp
                        <span class="text-slate-500"> · {{ $timeStr }}</span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase text-slate-500">Jumlah tamu</dt>
                    <dd class="mt-0.5 text-slate-700">{{ $booking->guest_count }}</dd>
                </div>
            </dl>
        </div>

        <form class="flex flex-col gap-8" wire:submit="save">
            <div class="rounded-[12px] border border-[#D1D9D9] bg-white p-6 shadow-sm card-shadow">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Ringkasan pesanan</h3>
                @if ($orderLines === [])
                    <p class="mt-4 text-sm text-slate-500">Belum ada item. Tambahkan dari daftar menu di bawah.</p>
                @else
                    <ul class="mt-4 flex flex-col gap-3">
                        @foreach ($orderLines as $line)
                            <li
                                class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3 text-sm last:border-0 last:pb-0"
                                wire:key="line-{{ $line['id'] }}"
                            >
                                <div>
                                    <p class="font-semibold text-[#0A1628]">{{ $line['name'] }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ number_format($line['unit_price'], 0, ',', '.') }} × {{ $line['quantity'] }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center gap-1 rounded-lg bg-[#eff5f5] p-1">
                                        <button
                                            class="rounded-md px-2 py-1 text-slate-600 hover:bg-white"
                                            type="button"
                                            wire:click="decrementItem({{ $line['id'] }})"
                                        >
                                            <span class="material-symbols-outlined text-[18px]">remove</span>
                                        </button>
                                        <span class="min-w-[1.5rem] text-center text-xs font-bold">{{ $line['quantity'] }}</span>
                                        <button
                                            class="rounded-md px-2 py-1 text-slate-600 hover:bg-white"
                                            type="button"
                                            wire:click="incrementItem({{ $line['id'] }})"
                                        >
                                            <span class="material-symbols-outlined text-[18px]">add</span>
                                        </button>
                                    </div>
                                    <span class="min-w-[5rem] text-right font-bold text-[#025864]">
                                        Rp {{ number_format($line['subtotal'], 0, ',', '.') }}
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-4 flex justify-between border-t border-slate-200 pt-4 text-base font-bold text-[#0A1628]">
                        <span>Total</span>
                        <span>Rp {{ number_format($cartTotal, 0, ',', '.') }}</span>
                    </div>
                @endif
                @error('cartItems')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Menu</h3>
                <p class="mt-1 text-sm text-slate-500">Hanya menu berstatus tersedia yang dapat ditambahkan ke pesanan.</p>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($menuItems as $item)
                        <div
                            class="flex flex-col rounded-[12px] border border-[#D1D9D9] bg-white p-4 shadow-sm card-shadow"
                            wire:key="menu-{{ $item->id }}"
                        >
                            <div class="flex gap-3">
                                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg bg-[#eff5f5]">
                                    <img
                                        class="h-full w-full object-cover"
                                        src="{{ asset($item->thumbnail_path) }}"
                                        alt="{{ $item->name }}"
                                    />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-[#0A1628]">{{ $item->name }}</p>
                                    <p class="text-[11px] text-slate-500">{{ $item->category?->name ?? '—' }}</p>
                                    <p class="mt-1 text-sm font-bold text-[#025864]">
                                        Rp {{ number_format((float) $item->price, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                            @if ($item->status === \App\Models\MenuItem::STATUS_AVAILABLE)
                                <button
                                    class="mt-3 w-full rounded-full bg-[#025864] py-2 text-xs font-bold text-white hover:opacity-90"
                                    type="button"
                                    wire:click="addToCart({{ $item->id }})"
                                >
                                    Tambah ke pesanan
                                </button>
                            @else
                                <p class="mt-3 text-center text-xs font-semibold text-amber-700">
                                    {{ $item->status === \App\Models\MenuItem::STATUS_SOLDOUT ? 'Habis' : 'Nonaktif' }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 pt-6">
                <a
                    class="rounded-full px-5 py-2 text-sm font-bold text-slate-600 hover:bg-slate-100"
                    href="{{ route('admin.bookings.show', $booking) }}"
                    wire:navigate
                >
                    Batal
                </a>
                <button
                    class="rounded-full bg-[#025864] px-5 py-2 text-sm font-bold text-white hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                    type="submit"
                >
                    Simpan pesanan
                </button>
            </div>
        </form>
    </div>
</div>
