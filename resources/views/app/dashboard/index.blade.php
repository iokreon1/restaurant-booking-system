@extends('layouts.dashboard')

@section('content')
    @php
        /** @var \App\Models\User $user */
        $s = $summary;
        $bookingsDelta = $s['bookings_today_change_percent'];
        $cancelDelta = $s['cancellations_delta_vs_yesterday'];
        $mr = $monthly_reservations;
        $f = $footer;
    @endphp
    <div class="mt-16 p-8 flex flex-col gap-[24px]">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-[#0A1628]">Workflow Management Hub</h2>
                <p class="text-slate-500 font-medium">Selamat datang kembali, {{ $user->name }} 👋 • Pantau antrian dan selesaikan tugas operasional harian.</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.bookings.create') }}" class="bg-[#025864] text-white px-5 py-2.5 rounded-full text-sm font-bold flex items-center gap-2 shadow-sm hover:opacity-90 transition-all">
                    <span class="material-symbols-outlined text-[18px]">add</span> Booking Baru
                </a>
            </div>
        </div>

        <section class="grid grid-cols-1 md:grid-cols-4 gap-[20px]">
            <div class="bg-white rounded-[12px] p-4 border border-[#D1D9D9] border-l-[4px] border-l-[#00D47E] card-shadow">
                <p class="text-slate-500 text-[11px] font-bold uppercase mb-1">Booking Hari Ini</p>
                <div class="flex items-center justify-between">
                    <h4 class="text-xl font-bold">{{ $s['bookings_today'] }}</h4>
                    @if($bookingsDelta !== null)
                        <span class="{{ $bookingsDelta >= 0 ? 'text-[#00D47E]' : 'text-red-500' }} text-xs font-bold">
                            {{ $bookingsDelta >= 0 ? '+' : '' }}{{ $bookingsDelta }}%
                        </span>
                    @else
                        <span class="text-slate-400 text-xs font-bold">—</span>
                    @endif
                </div>
            </div>
            <div class="bg-white rounded-[12px] p-4 border border-[#D1D9D9] border-l-[4px] border-l-[#F59E0B] card-shadow">
                <p class="text-slate-500 text-[11px] font-bold uppercase mb-1">Menunggu Approval</p>
                <div class="flex items-center justify-between">
                    <h4 class="text-xl font-bold text-[#F59E0B]">{{ $s['pending_approval'] }}</h4>
                    @if($s['pending_approval'] > 0)
                        <span class="bg-amber-100 text-amber-700 text-[9px] font-bold px-1.5 py-0.5 rounded">URGENT</span>
                    @endif
                </div>
            </div>
            <div class="bg-white rounded-[12px] p-4 border border-[#D1D9D9] border-l-[4px] border-l-[#025864] card-shadow">
                <p class="text-slate-500 text-[11px] font-bold uppercase mb-1">Okupansi Meja</p>
                <div class="flex items-center justify-between">
                    <h4 class="text-xl font-bold">{{ $s['occupied_tables'] }} / {{ $s['total_tables'] }}</h4>
                    <span class="text-[#025864] text-[11px] font-bold">{{ $s['occupancy_percent'] }}%</span>
                </div>
            </div>
            <div class="bg-white rounded-[12px] p-4 border border-[#D1D9D9] border-l-[4px] border-l-[#EF4444] card-shadow">
                <p class="text-slate-500 text-[11px] font-bold uppercase mb-1">Pembatalan</p>
                <div class="flex items-center justify-between">
                    <h4 class="text-xl font-bold text-[#EF4444]">{{ $s['cancellations_today'] }}</h4>
                    <span class="text-slate-400 text-[11px]">
                        @if($cancelDelta !== 0)
                            {{ $cancelDelta > 0 ? '+' : '' }}{{ $cancelDelta }} vs kemarin
                        @else
                            sama vs kemarin
                        @endif
                    </span>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-12 gap-[24px]">
            <div class="col-span-12 lg:col-span-8 bg-white rounded-[12px] border border-[#D1D9D9] card-shadow flex flex-col">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-amber-50 rounded-lg">
                            <span class="material-symbols-outlined text-amber-600">notification_important</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-[#0A1628]">Pending Approval &amp; Payment</h3>
                            <p class="text-xs text-slate-500">Selesaikan permintaan tertunda untuk mengonfirmasi meja</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.bookings') }}" class="text-xs font-bold text-[#025864] hover:underline">Lihat Semua Antrian</a>
                </div>
                <div class="workflow-scroll no-scrollbar p-5 flex flex-col gap-3">
                    @forelse($pending_bookings as $booking)
                        @php
                            $initials = collect(preg_split('/\s+/', trim($booking->user?->name ?? '?')))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr((string) $p, 0, 1)))->implode('');
                            if ($booking->booking_status === \App\Models\Booking::BOOKING_STATUS_PENDING) {
                                $badge = 'PERLU KONFIRMASI';
                                $badgeClass = 'bg-blue-100 text-blue-700';
                            } else {
                                $badge = 'MENUNGGU PEMBAYARAN';
                                $badgeClass = 'bg-amber-100 text-amber-700';
                            }
                        @endphp
                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100 group hover:border-[#025864]/30 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-[#025864] text-white flex items-center justify-center font-bold text-sm">{{ $initials }}</div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-sm">{{ $booking->user?->name ?? 'Tamu' }}</span>
                                        <span class="{{ $badgeClass }} text-[10px] font-bold px-1.5 py-0.5 rounded">{{ $badge }}</span>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        @if($booking->table)
                                            Meja #{{ $booking->table->table_number }}
                                        @else
                                            Meja —
                                        @endif
                                        • {{ $booking->guest_count }} Orang • Rp {{ number_format((float) $booking->total_amount, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.bookings.show', $booking) }}" class="px-4 py-2 bg-white border border-[#025864] text-[#025864] text-xs font-bold rounded-full hover:bg-[#eff5f5]">Detail</a>
                                @if($booking->payment_status === \App\Models\Booking::PAYMENT_STATUS_PENDING)
                                    <span class="px-4 py-2 bg-[#025864] text-white text-xs font-bold rounded-full flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-[16px]">payments</span>
                                        Process Payment
                                    </span>
                                @else
                                    <span class="px-4 py-2 bg-[#025864] text-white text-xs font-bold rounded-full flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-[16px]">check_circle</span>
                                        Approve Booking
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 text-center py-6">Tidak ada antrian tertunda.</p>
                    @endforelse
                </div>
            </div>

            <div class="col-span-12 lg:col-span-4 bg-white rounded-[12px] border border-[#D1D9D9] card-shadow flex flex-col">
                <div class="p-5 border-b border-slate-100">
                    <h3 class="font-bold text-[#0A1628]">Aktivitas Terkini</h3>
                    <p class="text-xs text-slate-500">Log transaksi terbaru sistem</p>
                </div>
                <div class="p-5 flex-1 workflow-scroll no-scrollbar">
                    <div class="space-y-6 relative before:absolute before:left-[15px] before:top-2 before:bottom-2 before:w-[1px] before:bg-slate-100">
                        @foreach($recent_activity as $activity)
                            @php
                                $iconWrap = match ($activity['icon']) {
                                    'check' => 'bg-[#DCFCE7]',
                                    'close' => 'bg-red-50',
                                    default => 'bg-blue-50',
                                };
                                $iconColor = match ($activity['icon']) {
                                    'check' => 'text-[#15803D]',
                                    'close' => 'text-red-600',
                                    default => 'text-blue-600',
                                };
                            @endphp
                            <div class="relative flex gap-4 pl-8">
                                <div class="absolute left-0 top-1 w-8 h-8 rounded-full {{ $iconWrap }} border-4 border-white flex items-center justify-center z-10 shadow-sm">
                                    <span class="material-symbols-outlined {{ $iconColor }} text-[14px]">{{ $activity['icon'] }}</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-bold text-slate-800">{{ $activity['title'] }}</p>
                                    <p class="text-xs text-slate-500">{{ $activity['description'] }}</p>
                                    <span class="text-[10px] text-slate-400 mt-1 block">{{ $activity['time_ago'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-12 gap-[24px]">
            <div class="col-span-12 lg:col-span-7 bg-white rounded-[12px] p-6 border border-[#D1D9D9] card-shadow">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-bold text-[#0A1628]">Tren Booking 14 Hari</h3>
                    <div class="flex bg-slate-100 p-1 rounded-lg">
                        <span class="px-4 py-1 text-xs font-bold rounded-md bg-white shadow-sm text-[#025864]">Daily</span>
                    </div>
                </div>
                <div class="h-[180px] flex items-end justify-between gap-1 px-2">
                    @foreach($trend_14_days as $day)
                        @php
                            $hi = $trend_max > 0 ? (int) round(($day['incoming'] / $trend_max) * 100) : 0;
                            $hc = $trend_max > 0 ? (int) round(($day['completed'] / $trend_max) * 100) : 0;
                        @endphp
                        <div class="flex-1 flex flex-col justify-end items-center gap-1 min-w-0">
                            <div class="flex gap-0.5 w-full items-end justify-center h-[140px]">
                                <div class="w-2.5 bg-[#025864] rounded-t-sm transition-all" style="height: {{ max(4, $hi) }}%;"></div>
                                <div class="w-2.5 bg-[#00D47E] rounded-t-sm transition-all" style="height: {{ max(4, $hc) }}%;"></div>
                            </div>
                            <span class="text-[9px] text-slate-400 mt-2 truncate w-full text-center">{{ $day['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-center gap-6 mt-6">
                    <div class="flex items-center gap-2"><div class="w-2.5 h-2.5 bg-[#025864] rounded-full"></div><span class="text-[10px] font-semibold text-slate-500 uppercase">Booking Masuk</span></div>
                    <div class="flex items-center gap-2"><div class="w-2.5 h-2.5 bg-[#00D47E] rounded-full"></div><span class="text-[10px] font-semibold text-slate-500 uppercase">Selesai</span></div>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-5 bg-white rounded-[12px] p-6 border border-[#D1D9D9] card-shadow flex flex-col items-center">
                <h3 class="font-bold text-[#0A1628] w-full mb-4">Status Reservasi (Bulan Ini)</h3>
                <div class="relative w-32 h-32 my-2">
                    @if($mr['total'] > 0 && count($mr['stroke_segments']) > 0)
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                            @foreach($mr['stroke_segments'] as $seg)
                                <circle cx="18" cy="18" fill="none" r="15.915" stroke="{{ $seg['color'] }}" stroke-dasharray="{{ $seg['dasharray'] }}" stroke-dashoffset="{{ $seg['dashoffset'] }}" stroke-width="4"></circle>
                            @endforeach
                        </svg>
                    @else
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" fill="none" r="15.915" stroke="#E2E8F0" stroke-dasharray="100 0" stroke-width="4"></circle>
                        </svg>
                    @endif
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-xl font-bold">{{ $mr['total'] }}</span>
                        <span class="text-[8px] font-bold text-slate-400 uppercase">Total</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-x-6 gap-y-2 mt-4 w-full text-[10px] font-semibold">
                    @foreach($mr['segments'] as $seg)
                        <div class="flex items-center gap-1.5">
                            <div class="w-2 h-2 rounded-full" style="background-color: {{ $seg['color'] }};"></div>
                            {{ $seg['label'] }} ({{ $seg['percent'] }}%)
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-3 gap-[20px] mb-8">
            <div class="bg-white rounded-[12px] p-5 border border-[#D1D9D9] card-shadow flex justify-between items-center">
                <div>
                    <p class="text-slate-500 text-[11px] font-bold uppercase">Revenue Harian</p>
                    <h5 class="text-xl font-bold mt-1">Rp {{ number_format($f['revenue_today'], 0, ',', '.') }}</h5>
                </div>
                @if($f['revenue_change_percent'] !== null)
                    <div class="{{ $f['revenue_change_percent'] >= 0 ? 'text-[#00D47E]' : 'text-red-500' }} flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">{{ $f['revenue_change_percent'] >= 0 ? 'trending_up' : 'trending_down' }}</span>
                        <span class="text-xs font-bold">{{ $f['revenue_change_percent'] >= 0 ? '+' : '' }}{{ $f['revenue_change_percent'] }}%</span>
                    </div>
                @else
                    <span class="text-slate-400 text-xs">—</span>
                @endif
            </div>
            <div class="bg-white rounded-[12px] p-5 border border-[#D1D9D9] card-shadow flex justify-between items-center">
                <div>
                    <p class="text-slate-500 text-[11px] font-bold uppercase">Menu Aktif</p>
                    <h5 class="text-xl font-bold mt-1">{{ $f['menu_active_count'] }} Items</h5>
                </div>
                @if($f['menu_soldout_count'] > 0)
                    <span class="bg-red-50 text-red-600 text-[10px] font-bold px-2 py-1 rounded-full">{{ $f['menu_soldout_count'] }} SOLD OUT</span>
                @endif
            </div>
            <div class="bg-white rounded-[12px] p-5 border border-[#D1D9D9] card-shadow flex justify-between items-center">
                <div>
                    <p class="text-slate-500 text-[11px] font-bold uppercase">Total Customer</p>
                    <h5 class="text-xl font-bold mt-1">{{ $f['customer_total'] }}</h5>
                </div>
                @if($f['customers_new_this_month'] > 0)
                    <span class="bg-[#eff5f5] text-[#025864] text-[10px] font-bold px-2 py-1 rounded-full">+{{ $f['customers_new_this_month'] }} NEW</span>
                @endif
            </div>
        </section>
    </div>
@endsection
