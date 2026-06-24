<div>
    <div class="mt-16 flex flex-col gap-6 p-8">
        {{-- Header --}}
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <a
                    class="mb-3 inline-flex items-center gap-1 text-sm font-semibold text-[#025864] hover:underline"
                    href="{{ route('admin.bookings') }}"
                    wire:navigate
                >
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Kembali ke daftar booking
                </a>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-3xl font-bold text-[#0A1628]">Detail booking</h2>
                    <span class="{{ \App\Helper\BookingStatusHelper::badgeClassesFor($booking->booking_status) }}">
                        {{ $bookingStatuses[$booking->booking_status] ?? $booking->booking_status }}
                    </span>
                </div>
                <p class="mt-1 font-mono text-lg font-semibold text-[#025864]">{{ $booking->booking_reference }}</p>
            </div>
            <div class="flex flex-col gap-3 self-start sm:flex-row sm:flex-wrap md:self-end">
                @foreach ($bookingStatusActions as $action)
                    <button
                        @class([
                            'inline-flex items-center justify-center rounded-full border px-5 py-2.5 text-sm font-bold shadow-sm transition-colors disabled:cursor-not-allowed disabled:opacity-60',
                            $action['class'],
                        ])
                        type="button"
                        wire:click="updateBookingStatus('{{ $action['status'] }}')"
                        wire:loading.attr="disabled"
                        wire:target="updateBookingStatus"
                    >
                        {{ $action['label'] }}
                    </button>
                @endforeach
                <a
                    class="inline-flex items-center justify-center gap-2 rounded-full border border-[#D1D9D9] bg-white px-5 py-2.5 text-sm font-bold text-[#025864] shadow-sm hover:bg-[#eff5f5]"
                    href="{{ route('admin.bookings.edit', $booking) }}"
                    wire:navigate
                >
                    <span class="material-symbols-outlined text-[18px]">edit</span>
                    Ubah pesanan
                </a>
            </div>
        </div>

        {{-- Status & Info Overview --}}
        @php
            $t = $booking->booking_time;
            $timeStr = $t instanceof \DateTimeInterface ? $t->format('H:i') : substr((string) $t, 0, 5);

            $bookingStatusIcon = match ($booking->booking_status) {
                'pending' => 'hourglass_top',
                'confirmed' => 'check_circle',
                'seated' => 'event_seat',
                'preparing' => 'skillet',
                'completed' => 'verified',
                'cancelled' => 'cancel',
                'no_show' => 'person_off',
                default => 'info',
            };
            $bookingStatusColor = match ($booking->booking_status) {
                'pending' => 'text-amber-600 bg-amber-50 border-amber-200',
                'confirmed' => 'text-sky-600 bg-sky-50 border-sky-200',
                'seated' => 'text-cyan-600 bg-cyan-50 border-cyan-200',
                'preparing' => 'text-violet-600 bg-violet-50 border-violet-200',
                'completed' => 'text-emerald-600 bg-emerald-50 border-emerald-200',
                'cancelled' => 'text-red-600 bg-red-50 border-red-200',
                'no_show' => 'text-slate-600 bg-slate-50 border-slate-200',
                default => 'text-slate-600 bg-slate-50 border-slate-200',
            };

            $paymentIcon = match ($booking->payment_status) {
                'paid' => 'check_circle',
                'expired' => 'timer_off',
                'refunded' => 'currency_exchange',
                default => 'pending',
            };
            $paymentColor = match ($booking->payment_status) {
                'paid' => 'text-emerald-600 bg-emerald-50 border-emerald-200',
                'expired' => 'text-red-600 bg-red-50 border-red-200',
                'refunded' => 'text-violet-600 bg-violet-50 border-violet-200',
                default => 'text-amber-600 bg-amber-50 border-amber-200',
            };
        @endphp

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            {{-- Booking Status Card --}}
            <div class="rounded-[12px] border {{ $bookingStatusColor }} p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/70 shadow-sm">
                        <span class="material-symbols-outlined text-[22px]">{{ $bookingStatusIcon }}</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider opacity-70">Status Booking</p>
                        <p class="text-base font-bold">{{ $bookingStatuses[$booking->booking_status] ?? $booking->booking_status }}</p>
                    </div>
                </div>
            </div>

            {{-- Payment Status Card --}}
            <div class="rounded-[12px] border {{ $paymentColor }} p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/70 shadow-sm">
                        <span class="material-symbols-outlined text-[22px]">{{ $paymentIcon }}</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider opacity-70">Status Pembayaran</p>
                        <p class="text-base font-bold">{{ $paymentStatuses[$booking->payment_status] ?? $booking->payment_status }}</p>
                    </div>
                </div>
            </div>

            {{-- Transaction Status Card --}}
            @if ($booking->transaction)
                @php
                    $txIcon = match ($booking->transaction->status) {
                        'success' => 'paid',
                        'failed' => 'error',
                        'expired' => 'timer_off',
                        default => 'hourglass_top',
                    };
                    $txColor = match ($booking->transaction->status) {
                        'success' => 'text-emerald-600 bg-emerald-50 border-emerald-200',
                        'failed' => 'text-red-600 bg-red-50 border-red-200',
                        'expired' => 'text-slate-600 bg-slate-50 border-slate-200',
                        default => 'text-amber-600 bg-amber-50 border-amber-200',
                    };
                @endphp
                <div class="rounded-[12px] border {{ $txColor }} p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/70 shadow-sm">
                            <span class="material-symbols-outlined text-[22px]">{{ $txIcon }}</span>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider opacity-70">Status Transaksi</p>
                            <p class="text-base font-bold">{{ $transactionStatuses[$booking->transaction->status] ?? $booking->transaction->status }}</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="rounded-[12px] border border-dashed border-slate-200 bg-slate-50/50 p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white shadow-sm">
                            <span class="material-symbols-outlined text-[22px] text-slate-400">receipt_long</span>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Status Transaksi</p>
                            <p class="text-base font-bold text-slate-400">Belum ada transaksi</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Quick stats --}}
        <section class="grid grid-cols-2 gap-4 lg:grid-cols-5">
            <div class="rounded-[12px] border border-[#D1D9D9] bg-white p-4 shadow-sm card-shadow">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Sumber</p>
                <p class="mt-1.5 text-sm font-bold text-[#0A1628]">
                    @if (($booking->type ?? \App\Models\Booking::TYPE_MICROSITE) === \App\Models\Booking::TYPE_MANUAL)
                        <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-[16px] text-violet-600">edit_note</span> Manual</span>
                    @else
                        <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-[16px] text-sky-600">language</span> Microsite</span>
                    @endif
                </p>
            </div>
            <div class="rounded-[12px] border border-[#D1D9D9] bg-white p-4 shadow-sm card-shadow">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tanggal & Waktu</p>
                <p class="mt-1.5 text-sm font-bold text-[#0A1628]">{{ $booking->booking_date->format('d M Y') }}</p>
                <p class="text-xs text-slate-500">{{ $timeStr }} WIB</p>
            </div>
            <div class="rounded-[12px] border border-[#D1D9D9] bg-white p-4 shadow-sm card-shadow">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Jumlah tamu</p>
                <p class="mt-1.5 text-sm font-bold text-[#0A1628]">{{ $booking->guest_count }} orang</p>
            </div>
            <div class="rounded-[12px] border border-[#D1D9D9] bg-white p-4 shadow-sm card-shadow">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total</p>
                <p class="mt-1.5 text-lg font-bold text-[#0A1628]">Rp {{ number_format((float) $booking->total_amount, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-[12px] border border-[#D1D9D9] bg-white p-4 shadow-sm card-shadow">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Dibuat pada</p>
                <p class="mt-1.5 text-sm font-bold text-[#0A1628]">{{ $booking->created_at->format('d M Y') }}</p>
                <p class="text-xs text-slate-500">{{ $booking->created_at->format('H:i') }}</p>
            </div>
        </section>

        {{-- Pemesan & Meja --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-[12px] border border-[#D1D9D9] bg-white p-6 shadow-sm card-shadow">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-[#025864]">person</span>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Pemesan</h3>
                </div>
                <div class="mt-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#eff5f5] text-sm font-bold text-[#025864]">
                        {{ strtoupper(substr($booking->user?->name ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-[#0A1628]">{{ $booking->user?->name ?? '—' }}</p>
                        <p class="text-xs text-slate-500">{{ $booking->user?->email ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-[12px] border border-[#D1D9D9] bg-white p-6 shadow-sm card-shadow">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-[#025864]">table_restaurant</span>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Meja</h3>
                </div>
                @if ($booking->table)
                    <div class="mt-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#eff5f5] text-sm font-bold text-[#025864]">
                            #{{ $booking->table->table_number }}
                        </div>
                        <div>
                            <p class="font-semibold text-[#0A1628]">Meja {{ $booking->table->table_number }}</p>
                            <p class="text-xs text-slate-500">{{ $booking->table->capacity }} kursi &middot; {{ $booking->table->location_description ?? '-' }}</p>
                        </div>
                    </div>
                @else
                    <p class="mt-4 text-sm text-slate-400">Belum ada meja yang ditentukan</p>
                @endif
            </div>
        </div>

        {{-- Catatan & Pembatalan --}}
        @if ($booking->note || $booking->cancellation_reason)
            <div class="grid grid-cols-1 gap-6 {{ $booking->note && $booking->cancellation_reason ? 'lg:grid-cols-2' : '' }}">
                @if ($booking->note)
                    <div class="rounded-[12px] border border-[#D1D9D9] bg-white p-6 shadow-sm card-shadow">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px] text-amber-500">sticky_note_2</span>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Catatan</h3>
                        </div>
                        <p class="mt-3 text-sm leading-relaxed text-slate-700">{{ $booking->note }}</p>
                    </div>
                @endif
                @if ($booking->cancellation_reason)
                    <div class="rounded-[12px] border border-red-200 bg-red-50/50 p-6 shadow-sm card-shadow">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px] text-red-500">cancel</span>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-red-400">Alasan pembatalan</h3>
                        </div>
                        <p class="mt-3 text-sm leading-relaxed text-red-700">{{ $booking->cancellation_reason }}</p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Menu pesanan --}}
        <div class="rounded-[12px] border border-[#D1D9D9] bg-white p-6 shadow-sm card-shadow">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-[#025864]">restaurant_menu</span>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Menu pesanan</h3>
            </div>
            <x-dashboard.data-table wrapper-class="mt-4">
                <x-slot name="thead">
                    <tr class="border-b border-slate-100 bg-[#eff5f5] text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-6 py-4">Item</th>
                        <th class="px-6 py-4">Harga satuan</th>
                        <th class="px-6 py-4">Qty</th>
                        <th class="px-6 py-4 text-right">Subtotal</th>
                    </tr>
                </x-slot>
                @forelse ($booking->items ?? [] as $item)
                    <tr class="hover:bg-slate-50/80" wire:key="bk-item-{{ $loop->index }}">
                        <td class="px-6 py-4 text-sm font-semibold text-[#0A1628]">{{ data_get($item, 'name', '—') }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            Rp {{ number_format((float) data_get($item, 'unit_price', 0), 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ (int) data_get($item, 'quantity', 0) }}</td>
                        <td class="px-6 py-4 text-right text-sm font-bold text-[#0A1628]">
                            Rp {{ number_format((float) data_get($item, 'subtotal', 0), 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-6 py-12 text-center text-slate-500" colspan="4">Tidak ada item menu pada booking ini.</td>
                    </tr>
                @endforelse
            </x-dashboard.data-table>
        </div>

        {{-- Detail transaksi --}}
        <div class="rounded-[12px] border border-[#D1D9D9] bg-white p-6 shadow-sm card-shadow">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-[#025864]">payments</span>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Detail transaksi</h3>
            </div>

            @if ($booking->transaction)
                @php
                    $txStatusColor = match ($booking->transaction->status) {
                        'success' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                        'failed' => 'bg-red-100 text-red-800 border-red-200',
                        'expired' => 'bg-slate-100 text-slate-700 border-slate-200',
                        default => 'bg-amber-100 text-amber-800 border-amber-200',
                    };
                    $txStatusIcon = match ($booking->transaction->status) {
                        'success' => 'check_circle',
                        'failed' => 'error',
                        'expired' => 'schedule',
                        default => 'hourglass_top',
                    };
                @endphp

                <div class="mt-4 flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-bold {{ $txStatusColor }}">
                        <span class="material-symbols-outlined text-[14px]">{{ $txStatusIcon }}</span>
                        {{ $transactionStatuses[$booking->transaction->status] ?? $booking->transaction->status }}
                    </span>
                    <span class="text-lg font-bold text-[#0A1628]">Rp {{ number_format((float) $booking->transaction->amount, 0, ',', '.') }}</span>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @if ($booking->transaction->midtrans_transaction_id)
                        <div class="rounded-lg bg-slate-50 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">ID Midtrans</p>
                            <p class="mt-1 break-all font-mono text-xs font-medium text-slate-700">{{ $booking->transaction->midtrans_transaction_id }}</p>
                        </div>
                    @endif
                    @if ($booking->transaction->payment_method)
                        <div class="rounded-lg bg-slate-50 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Metode</p>
                            <p class="mt-1 text-sm font-medium text-slate-700">{{ $booking->transaction->payment_method }}</p>
                        </div>
                    @endif
                    @if ($booking->transaction->payment_channel)
                        <div class="rounded-lg bg-slate-50 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Channel</p>
                            <p class="mt-1 text-sm font-medium text-slate-700">{{ $booking->transaction->payment_channel }}</p>
                        </div>
                    @endif
                    @if ($booking->transaction->paid_at)
                        <div class="rounded-lg bg-emerald-50 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-500">Dibayar pada</p>
                            <p class="mt-1 text-sm font-medium text-emerald-800">{{ $booking->transaction->paid_at->format('d M Y, H:i') }}</p>
                        </div>
                    @endif
                    @if ($booking->transaction->expired_at)
                        <div class="rounded-lg bg-slate-50 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kedaluwarsa pada</p>
                            <p class="mt-1 text-sm font-medium text-slate-700">{{ $booking->transaction->expired_at->format('d M Y, H:i') }}</p>
                        </div>
                    @endif
                </div>
            @else
                <div class="mt-4 flex flex-col items-center justify-center rounded-lg border border-dashed border-slate-200 py-10">
                    <span class="material-symbols-outlined text-[40px] text-slate-300">receipt_long</span>
                    <p class="mt-2 text-sm font-medium text-slate-400">Belum ada data transaksi</p>
                </div>
            @endif
        </div>
    </div>
</div>
