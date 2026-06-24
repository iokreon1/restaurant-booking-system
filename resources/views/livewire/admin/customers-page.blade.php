<div>
    <div class="mt-16 flex flex-col gap-6 p-8">
        @if (session('status'))
            <div class="rounded-[12px] border border-[#00D47E]/40 bg-[#DCFCE7] px-4 py-3 text-sm font-semibold text-[#15803D]">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <h2 class="text-3xl font-bold text-[#0A1628]">Daftar Customer</h2>
                <p class="mt-1 font-medium text-slate-500">Pelanggan dengan peran tamu (customer) dan ringkasan booking.</p>
            </div>
            <div class="w-full max-w-md">
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-500" for="customer-search">Cari nama atau email</label>
                <input
                    class="mt-2 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                    id="customer-search"
                    type="search"
                    placeholder="Ketik untuk memfilter…"
                    wire:model.live.debounce.300ms="search"
                />
            </div>
        </div>

        <section class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-[12px] border border-[#D1D9D9] border-l-[4px] border-l-[#025864] bg-white p-5 shadow-sm card-shadow">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total customer</p>
                <h3 class="mt-2 text-2xl font-bold text-[#0A1628]">{{ $stats['total'] }}</h3>
            </div>
            <div class="rounded-[12px] border border-[#D1D9D9] border-l-[4px] border-l-[#00D47E] bg-white p-5 shadow-sm card-shadow">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Pernah booking</p>
                <h3 class="mt-2 text-2xl font-bold text-[#0A1628]">{{ $stats['with_bookings'] }}</h3>
            </div>
            <div class="rounded-[12px] border border-[#D1D9D9] border-l-[4px] border-l-[#7C3AED] bg-white p-5 shadow-sm card-shadow">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Baru bulan ini</p>
                <h3 class="mt-2 text-2xl font-bold text-[#0A1628]">{{ $stats['new_month'] }}</h3>
            </div>
            <div class="rounded-[12px] border border-[#D1D9D9] border-l-[4px] border-l-[#F59E0B] bg-white p-5 shadow-sm card-shadow">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Email terverifikasi</p>
                <h3 class="mt-2 text-2xl font-bold text-[#0A1628]">{{ $stats['verified'] }}</h3>
            </div>
        </section>

        <x-dashboard.data-table>
            <x-slot name="thead">
                <tr class="border-b border-slate-100 bg-[#eff5f5] text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <th class="px-6 py-4">Nama</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4">Jumlah booking</th>
                    <th class="px-6 py-4">Bergabung</th>
                    <th class="px-6 py-4">Verifikasi email</th>
                </tr>
            </x-slot>
            @forelse ($customers as $customer)
                <tr class="hover:bg-slate-50/80" wire:key="customer-{{ $customer->id }}">
                    <td class="px-6 py-4 font-semibold text-[#0A1628]">{{ $customer->name }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $customer->email }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $customer->bookings_count }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $customer->created_at?->translatedFormat('d M Y') }}</td>
                    <td class="px-6 py-4">
                        @if ($customer->email_verified_at)
                            <span class="rounded-full bg-[#DCFCE7] px-2.5 py-1 text-[10px] font-bold uppercase text-[#15803D]">Ya</span>
                        @else
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase text-slate-600">Belum</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="px-6 py-12 text-center text-slate-500" colspan="5">Belum ada customer yang cocok dengan pencarian.</td>
                </tr>
            @endforelse
            <x-slot name="footer">
                <x-dashboard.table-pagination :paginator="$customers" />
            </x-slot>
        </x-dashboard.data-table>
    </div>
</div>
