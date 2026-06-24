@use('App\Helper\BookingStatusHelper')

<div>
    <div class="mt-16 flex flex-col gap-6 p-8">
        @if (session('status'))
            <div class="rounded-[12px] border border-[#00D47E]/40 bg-[#DCFCE7] px-4 py-3 text-sm font-semibold text-[#15803D]">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <h2 class="text-3xl font-bold text-[#0A1628]">Booking</h2>
                <p class="mt-1 font-medium text-slate-500">Kelola reservasi, tamu, meja, dan status pembayaran.</p>
            </div>
            <button
                class="flex items-center gap-2 rounded-full bg-[#025864] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                type="button"
                wire:click="openCreate"
                @disabled(! $canCreateBooking)
            >
                <span class="material-symbols-outlined text-[18px]">add</span>
                Booking baru
            </button>
        </div>

        <section class="grid grid-cols-1 gap-5 md:grid-cols-3">
            <div class="rounded-[12px] border border-[#D1D9D9] border-l-[4px] border-l-[#025864] bg-white p-5 shadow-sm card-shadow">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total booking</p>
                <h3 class="mt-2 text-2xl font-bold text-[#0A1628]">{{ $stats['total'] }}</h3>
            </div>
            <div class="rounded-[12px] border border-[#D1D9D9] border-l-[4px] border-l-[#F59E0B] bg-white p-5 shadow-sm card-shadow">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Menunggu</p>
                <h3 class="mt-2 text-2xl font-bold text-[#0A1628]">{{ $stats['pending'] }}</h3>
            </div>
            <div class="rounded-[12px] border border-[#D1D9D9] border-l-[4px] border-l-[#00D47E] bg-white p-5 shadow-sm card-shadow">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Hari ini</p>
                <h3 class="mt-2 text-2xl font-bold text-[#0A1628]">{{ $stats['today'] }}</h3>
            </div>
        </section>

        <x-dashboard.data-table>
            <x-slot name="thead">
                <tr class="border-b border-slate-100 bg-[#eff5f5] text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <th class="px-6 py-4">Referensi</th>
                    <th class="px-6 py-4">Tamu</th>
                    <th class="px-6 py-4">Tanggal &amp; waktu</th>
                    <th class="px-6 py-4">Total</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </x-slot>
            @forelse ($bookings as $booking)
                <tr class="hover:bg-slate-50/80" wire:key="bk-{{ $booking->id }}">
                    <td class="px-6 py-4 font-mono text-sm font-semibold text-[#0A1628]">{{ $booking->booking_reference }}</td>
                    <td class="px-6 py-4 text-sm text-slate-700">{{ $booking->user?->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">
                        {{ $booking->booking_date->format('d M Y') }}
                        <span class="block text-xs text-slate-500">
                            @php
                                $t = $booking->booking_time;
                                $timeStr = $t instanceof \DateTimeInterface ? $t->format('H:i') : substr((string) $t, 0, 5);
                            @endphp
                            {{ $timeStr }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm font-bold text-[#0A1628]">Rp {{ number_format((float) $booking->total_amount, 0, ',', '.') }}</td>
                    <td class="px-6 py-4">
                        <span class="{{ BookingStatusHelper::badgeClassesFor($booking->booking_status) }}">
                            {{ BookingStatusHelper::label($booking->booking_status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div
                            class="relative inline-flex text-left"
                            x-data="{ open: false }"
                            @keydown.escape.window="open = false"
                        >
                            <button
                                class="inline-flex rounded-lg p-2 text-slate-600 outline-none ring-[#025864] transition-colors hover:bg-[#eff5f5] hover:text-[#025864] focus-visible:ring-2"
                                type="button"
                                :aria-expanded="open"
                                aria-haspopup="menu"
                                aria-label="Menu aksi booking"
                                @click="open = ! open"
                            >
                                <span class="material-symbols-outlined text-[22px]">menu</span>
                            </button>
                            <div
                                class="absolute right-0 z-50 mt-1 min-w-[11rem] origin-top-right rounded-xl border border-[#D1D9D9] bg-white py-1 shadow-lg"
                                x-cloak
                                x-show="open"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-end="opacity-0 scale-95"
                                x-transition:leave-start="opacity-100 scale-100"
                                @click.outside="open = false"
                            >
                                <a
                                    class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-[#025864] hover:bg-[#eff5f5]"
                                    href="{{ route('admin.bookings.show', $booking) }}"
                                    wire:navigate
                                    @click="open = false"
                                >
                                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                                    Lihat detail
                                </a>
                                <a
                                    class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-[#025864] hover:bg-[#eff5f5]"
                                    href="{{ route('admin.bookings.edit', $booking) }}"
                                    wire:navigate
                                    @click="open = false"
                                >
                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                    Ubah pesanan
                                </a>
                                <button
                                    class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm font-semibold text-red-600 hover:bg-red-50"
                                    type="button"
                                    wire:click="delete({{ $booking->id }})"
                                    wire:confirm="Hapus booking ini?"
                                    @click="open = false"
                                >
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="px-6 py-12 text-center text-slate-500" colspan="6">
                        @if (! $canCreateBooking)
                            Tambahkan pengguna dan data meja terlebih dahulu untuk membuat booking.
                        @else
                            Belum ada booking.
                        @endif
                    </td>
                </tr>
            @endforelse
            <x-slot name="footer">
                <x-dashboard.table-pagination :paginator="$bookings" />
            </x-slot>
        </x-dashboard.data-table>
    </div>
</div>
