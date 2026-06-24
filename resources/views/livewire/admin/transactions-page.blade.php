<div>
    <div class="mt-16 flex flex-col gap-6 p-8">
        @if (session('status'))
            <div class="rounded-[12px] border border-[#00D47E]/40 bg-[#DCFCE7] px-4 py-3 text-sm font-semibold text-[#15803D]">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <h2 class="text-3xl font-bold text-[#0A1628]">Riwayat transaksi</h2>
                <p class="mt-1 font-medium text-slate-500">Pantau pembayaran Midtrans, meja, dan status pembayaran tamu.</p>
            </div>
            <button
                class="flex items-center gap-2 rounded-full bg-[#025864] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:opacity-90"
                type="button"
                wire:click="exportCsv"
            >
                <span class="material-symbols-outlined text-[18px]">ios_share</span>
                Ekspor CSV/PDF
            </button>
        </div>

        <section class="grid grid-cols-1 gap-5 md:grid-cols-3">
            <div class="rounded-[12px] border border-[#D1D9D9] border-l-[4px] border-l-[#025864] bg-white p-5 shadow-sm card-shadow">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total transaksi</p>
                <h3 class="mt-2 text-2xl font-bold text-[#0A1628]">{{ $stats['total'] }}</h3>
            </div>
            <div class="rounded-[12px] border border-[#D1D9D9] border-l-[4px] border-l-[#00D47E] bg-white p-5 shadow-sm card-shadow">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Berhasil hari ini</p>
                <h3 class="mt-2 text-2xl font-bold text-[#0A1628]">{{ $stats['today_success'] }}</h3>
            </div>
            <div class="rounded-[12px] border border-[#D1D9D9] border-l-[4px] border-l-[#7C3AED] bg-white p-5 shadow-sm card-shadow">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Nominal berhasil hari ini</p>
                <h3 class="mt-2 text-2xl font-bold text-[#0A1628]">Rp {{ number_format($stats['today_amount'], 0, ',', '.') }}</h3>
            </div>
        </section>

        <section class="rounded-[12px] border border-[#D1D9D9] bg-white p-5 shadow-sm card-shadow">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Status</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach (['all' => 'Semua', 'success' => 'Berhasil', 'failed' => 'Gagal', 'pending' => 'Menunggu', 'refunded' => 'Refund'] as $value => $label)
                            <button
                                class="rounded-full px-4 py-2 text-xs font-bold transition-colors {{ $statusFilter === $value ? 'bg-[#025864] text-white' : 'bg-[#eff5f5] text-slate-600 hover:bg-slate-200' }}"
                                type="button"
                                wire:click="$set('statusFilter', '{{ $value }}')"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-500" for="tx-payment">Metode / saluran</label>
                    <select
                        class="mt-2 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                        id="tx-payment"
                        wire:model.live="paymentMethodFilter"
                    >
                        <option value="all">Semua</option>
                        @foreach ($paymentMethodChoices as $method)
                            <option value="{{ $method }}">{{ $method }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        <x-dashboard.data-table>
            <x-slot name="thead">
                <tr class="border-b border-slate-100 bg-[#eff5f5] text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <th class="px-6 py-4">Waktu</th>
                    <th class="px-6 py-4">Referensi</th>
                    <th class="px-6 py-4">Meja</th>
                    <th class="px-6 py-4">Tamu</th>
                    <th class="px-6 py-4">Total</th>
                    <th class="px-6 py-4">Metode</th>
                    <th class="px-6 py-4">Status</th>
                </tr>
            </x-slot>
            @forelse ($transactions as $transaction)
                @php
                    $booking = $transaction->booking;
                    $paidAt = $transaction->paid_at ?? $transaction->created_at;
                    $isRefund = $booking && $booking->payment_status === \App\Models\Booking::PAYMENT_STATUS_REFUNDED;
                @endphp
                <tr class="hover:bg-slate-50/80" wire:key="tx-{{ $transaction->id }}">
                    <td class="px-6 py-4">
                        <p class="text-sm font-bold text-[#0A1628]">{{ $paidAt->format('H:i') }}</p>
                        <p class="text-xs text-slate-500">{{ $paidAt->format('d M Y') }}</p>
                    </td>
                    <td class="px-6 py-4 font-mono text-sm font-semibold text-[#025864]">
                        #{{ $transaction->midtrans_transaction_id ?? 'TRX-'.$transaction->id }}
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">
                        @if ($booking?->table)
                            <span class="rounded-md bg-[#eff5f5] px-2 py-1 text-xs font-bold">#{{ $booking->table->table_number }}</span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-700">{{ $booking?->user?->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm font-bold text-[#0A1628]">Rp {{ number_format((float) $transaction->amount, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">
                        {{ $transaction->payment_method ?? $transaction->payment_channel ?? '—' }}
                    </td>
                    <td class="px-6 py-4">
                        @if ($isRefund)
                            <span class="rounded-full bg-violet-100 px-2.5 py-1 text-[10px] font-bold uppercase text-violet-800">Refund</span>
                        @elseif ($transaction->status === \App\Models\Transaction::STATUS_SUCCESS)
                            <span class="rounded-full bg-[#DCFCE7] px-2.5 py-1 text-[10px] font-bold uppercase text-[#15803D]">Berhasil</span>
                        @elseif ($transaction->status === \App\Models\Transaction::STATUS_FAILED)
                            <span class="rounded-full bg-red-100 px-2.5 py-1 text-[10px] font-bold uppercase text-red-800">Gagal</span>
                        @else
                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-bold uppercase text-amber-900">Menunggu</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="px-6 py-12 text-center text-slate-500" colspan="7">Belum ada transaksi.</td>
                </tr>
            @endforelse
            <x-slot name="footer">
                <x-dashboard.table-pagination :paginator="$transactions" />
            </x-slot>
        </x-dashboard.data-table>
    </div>
</div>
