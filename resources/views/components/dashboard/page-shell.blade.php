@props([
    'title',
    'description',
    'icon' => 'dashboard',
    'badge' => null,
    'stats' => [],
    'focusPoints' => [],
])

@php
    $resolvedFocusPoints = $focusPoints !== [] ? $focusPoints : [
        'Tambahkan daftar data utama untuk modul ini.',
        'Sambungkan aksi create, update, dan delete sesuai kebutuhan bisnis.',
        'Lengkapi filter, validasi, dan hak akses admin sebelum dipakai produksi.',
    ];
@endphp

<div class="mt-16 flex flex-col gap-6 p-8">
    <section class="rounded-[24px] border border-[#D1D9D9] bg-[linear-gradient(135deg,_#025864_0%,_#0A1628_100%)] p-8 text-white shadow-sm">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-3xl">
                <div class="mb-4 inline-flex items-center gap-3 rounded-full bg-white/10 px-4 py-2 text-sm font-semibold">
                    <span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>
                    <span>{{ $badge ?? 'Admin Module' }}</span>
                </div>

                <h2 class="text-3xl font-bold tracking-tight">{{ $title }}</h2>
                <p class="mt-3 max-w-2xl text-sm font-medium leading-6 text-white/75">
                    {{ $description }}
                </p>
            </div>

            <div class="rounded-2xl border border-white/15 bg-white/10 px-5 py-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-white/60">Status Halaman</p>
                <p class="mt-2 text-base font-semibold">Livewire component aktif</p>
                <p class="mt-1 text-sm text-white/70">Route dan sidebar sudah tersambung ke modul ini.</p>
            </div>
        </div>
    </section>

    @if ($stats !== [])
        <section class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as $stat)
                <div class="rounded-[16px] border border-[#D1D9D9] border-l-[4px] bg-white p-5 shadow-sm {{ $stat['border'] ?? 'border-l-[#025864]' }}">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                        {{ $stat['label'] }}
                    </p>

                    <div class="mt-3 flex items-center justify-between gap-3">
                        <h3 class="text-2xl font-bold text-[#0A1628]">{{ $stat['value'] }}</h3>

                        @if (filled($stat['meta'] ?? null))
                            <span class="text-xs font-bold {{ $stat['meta_class'] ?? 'text-slate-500' }}">
                                {{ $stat['meta'] }}
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </section>
    @endif

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,_1.45fr)_minmax(320px,_0.9fr)]">
        <div class="rounded-[20px] border border-[#D1D9D9] bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold text-[#0A1628]">Ringkasan Halaman</h3>
            <p class="mt-2 text-sm leading-6 text-slate-500">
                Halaman ini sudah disiapkan sebagai entry point Livewire untuk modul <span class="font-semibold text-[#025864]">{{ $title }}</span>.
                Struktur ini aman dipakai untuk list data, filter, formulir, aksi modal, dan integrasi query berikutnya.
            </p>

            <div class="mt-6 rounded-[20px] border border-[#D1D9D9] bg-[#eff5f5] p-5">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#025864] text-white">
                        <span class="material-symbols-outlined text-[24px]">{{ $icon }}</span>
                    </div>

                    <div>
                        <h4 class="text-base font-bold text-[#0A1628]">{{ $title }}</h4>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Gunakan komponen ini untuk menambahkan tabel data, status operasional, aksi cepat, dan form manajemen sesuai domain halaman.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-[20px] border border-[#D1D9D9] bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold text-[#0A1628]">Fokus Berikutnya</h3>

            <div class="mt-6 space-y-3">
                @foreach ($resolvedFocusPoints as $focusPoint)
                    <div class="flex items-start gap-3 rounded-[18px] border border-slate-100 bg-slate-50 p-4">
                        <span class="material-symbols-outlined text-[20px] text-[#00A76F]">check_circle</span>
                        <p class="text-sm leading-6 text-slate-600">{{ $focusPoint }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
