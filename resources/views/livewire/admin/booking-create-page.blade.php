<div>
    <div class="mt-16 flex flex-col gap-6 p-8">
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
                <h2 class="text-3xl font-bold text-[#0A1628]">Booking baru</h2>
                <p class="mt-1 font-medium text-slate-500">Pilih tamu, meja, dan item menu. Total dihitung dari harga menu saat ini.</p>
            </div>
        </div>

        <form class="flex flex-col gap-8" wire:submit="save">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-[12px] border border-[#D1D9D9] bg-white p-6 shadow-sm card-shadow">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Detail reservasi</h3>
                    <div class="mt-4 flex flex-col gap-4">
                        <div>
                            <label class="text-xs font-bold uppercase text-slate-500" for="bc-user">Tamu (user)</label>
                            <select
                                class="mt-1 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                                id="bc-user"
                                wire:model="user_id"
                            >
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase text-slate-500" for="bc-table">Meja</label>
                            <select
                                class="mt-1 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                                id="bc-table"
                                wire:model="table_id"
                            >
                                @foreach ($tables as $tbl)
                                    <option value="{{ $tbl->id }}">Meja {{ $tbl->table_number }} — {{ $tbl->capacity }} kursi</option>
                                @endforeach
                            </select>
                            @error('table_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold uppercase text-slate-500" for="bc-date">Tanggal</label>
                                <input
                                    class="mt-1 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                                    id="bc-date"
                                    type="date"
                                    wire:model="booking_date"
                                />
                                @error('booking_date')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="text-xs font-bold uppercase text-slate-500" for="bc-time">Waktu</label>
                                <input
                                    class="mt-1 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                                    id="bc-time"
                                    type="time"
                                    wire:model="booking_time"
                                />
                                @error('booking_time')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase text-slate-500" for="bc-guests">Jumlah tamu</label>
                            <input
                                class="mt-1 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                                id="bc-guests"
                                type="number"
                                min="1"
                                wire:model.live="guest_count"
                            />
                            @error('guest_count')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase text-slate-500" for="bc-bst">Status booking</label>
                            <select
                                class="mt-1 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                                id="bc-bst"
                                wire:model="booking_status"
                            >
                                @foreach ($bookingStatuses as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('booking_status')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase text-slate-500" for="bc-pay">Status pembayaran</label>
                            <select
                                class="mt-1 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                                id="bc-pay"
                                wire:model="payment_status"
                            >
                                @foreach ($paymentStatuses as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('payment_status')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase text-slate-500" for="bc-note">Catatan</label>
                            <textarea
                                class="mt-1 min-h-[72px] w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                                id="bc-note"
                                wire:model="note"
                            ></textarea>
                            @error('note')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase text-slate-500" for="bc-cancel">Alasan pembatalan (opsional)</label>
                            <textarea
                                class="mt-1 min-h-[72px] w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                                id="bc-cancel"
                                wire:model="cancellation_reason"
                            ></textarea>
                            @error('cancellation_reason')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

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
                    href="{{ route('admin.bookings') }}"
                    wire:navigate
                >
                    Batal
                </a>
                <button
                    class="rounded-full bg-[#025864] px-5 py-2 text-sm font-bold text-white hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                    type="submit"
                    @disabled($users->isEmpty() || $tables->isEmpty())
                >
                    Simpan booking
                </button>
            </div>
        </form>
    </div>
</div>
