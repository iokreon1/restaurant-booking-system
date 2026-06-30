@php
    $occupancyPct = $stats['total'] > 0 ? (int) round(($stats['booked'] / $stats['total']) * 100) : 0;
    $circ = 2 * pi() * 16;
@endphp

<div>
<div class="mt-16">
    @if (session('status'))
        <div class="px-10 pb-4">
            <div class="rounded-[12px] border border-[#00D47E]/40 bg-[#DCFCE7] px-4 py-3 text-sm font-semibold text-[#15803D]">
                {{ session('status') }}
            </div>
        </div>
    @endif

    @error('delete')
        <div class="px-10 pb-4">
            <div class="rounded-[12px] border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                {{ $message }}
            </div>
        </div>
    @enderror

    <div class="p-10 flex gap-10">
        <!-- Left Side: Floor Plan Grid -->
        <section class="flex-grow">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h3 class="text-2xl font-bold text-[#0A1628]">Main Dining Area</h3>
                    <p class="text-sm text-slate-500">Manage real-time table occupancy</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex gap-4 mr-4">
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                            <span class="h-2.5 w-2.5 rounded-full bg-[#00A76F]"></span> Available
                        </div>
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                            <span class="h-2.5 w-2.5 rounded-full bg-[#025864]"></span> Occupied
                        </div>
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                            <span class="h-2.5 w-2.5 rounded-full bg-[#F59E0B]"></span> Reserved
                        </div>
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                            <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span> Cleaning
                        </div>
                    </div>
                    <button
                        class="flex items-center gap-2 rounded-full bg-[#025864] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:opacity-90"
                        type="button"
                        wire:click="openCreate"
                    >
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        Tambah Meja
                    </button>
                </div>
            </div>
            <!-- Grid Layout Floor Plan -->
            <div class="grid grid-cols-2 gap-6">
                @foreach ($tables as $table)
                    @php
                        $latestBooking = $table->latestBooking;
                        $hasActiveBooking = $latestBooking && !in_array($latestBooking->booking_status, [
                            \App\Models\Booking::BOOKING_STATUS_COMPLETED,
                            \App\Models\Booking::BOOKING_STATUS_CANCELLED,
                            \App\Models\Booking::BOOKING_STATUS_NO_SHOW,
                        ]);
                        $guestLabel = ($hasActiveBooking && $latestBooking->user) ? $latestBooking->user->name : 'Walk-in Customer';
                    @endphp
                    @if ($table->status === \App\Models\Table::STATUS_BOOKED)
                        <!-- Table Card: Occupied -->
                        <div class="relative overflow-hidden rounded-xl border border-[#D1D9D9] bg-white p-6 shadow-sm transition-all hover:border-[#025864] group" wire:key="tbl-card-{{ $table->id }}">
                            <div class="mb-4 flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="text-3xl font-black leading-none text-[#025864]">{{ $table->table_number }}</div>
                                    <div class="mt-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">TABLE NUMBER</div>
                                </div>
                                <div class="flex shrink-0 items-center gap-1.5">
                                    <span class="rounded bg-[#025864] px-2 py-0.5 text-[10px] font-bold text-white">OCCUPIED</span>
                                    <button
                                        class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-[#eff5f5] hover:text-[#025864]"
                                        type="button"
                                        wire:click="openEdit({{ $table->id }})"
                                        title="Ubah meja"
                                    >
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </button>
                                    <button
                                        class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600"
                                        type="button"
                                        wire:click.stop="delete({{ $table->id }})"
                                        wire:confirm="Hapus meja ini?"
                                        title="Hapus meja"
                                    >
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-6 space-y-0.5">
                                <div class="text-sm font-bold text-[#0A1628]">{{ $guestLabel }}</div>
                                <p class="flex items-center gap-1 text-[11px] text-slate-500">
                                    <span class="material-symbols-outlined text-sm" data-icon="groups">groups</span>
                                    {{ $table->capacity }} Guests
                                </p>
                            </div>
                            @if ($hasActiveBooking)
                                <a
                                    class="block w-full rounded-full border border-[#025864]/35 bg-[#eff5f5] py-2.5 text-center text-xs font-bold text-[#025864] shadow-sm transition-all hover:bg-[#025864] hover:text-white"
                                    href="{{ route('admin.bookings.edit', $latestBooking) }}"
                                    wire:navigate
                                >
                                    Manage Order
                                </a>
                            @else
                                <button
                                    class="w-full rounded-full border border-[#025864]/35 bg-[#eff5f5] py-2.5 text-xs font-bold text-[#025864] shadow-sm transition-all hover:bg-[#025864] hover:text-white disabled:cursor-not-allowed disabled:opacity-50"
                                    type="button"
                                    disabled
                                >
                                    Manage Order
                                </button>
                            @endif
                        </div>
                    @elseif ($table->status === \App\Models\Table::STATUS_AVAILABLE)
                        <!-- Table Card: Available -->
                        <div class="relative overflow-hidden rounded-xl border border-[#D1D9D9] bg-white p-6 shadow-sm transition-all hover:border-[#00A76F] group" wire:key="tbl-card-{{ $table->id }}">
                            <div class="mb-4 flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="text-3xl font-black leading-none text-[#00A76F]">{{ $table->table_number }}</div>
                                    <div class="mt-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">TABLE NUMBER</div>
                                </div>
                                <div class="flex shrink-0 items-center gap-1.5">
                                    <span class="rounded bg-[#00A76F] px-2 py-0.5 text-[10px] font-bold text-white">AVAILABLE</span>
                                    <button
                                        class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-[#eff5f5] hover:text-[#025864]"
                                        type="button"
                                        wire:click="openEdit({{ $table->id }})"
                                        title="Ubah meja"
                                    >
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </button>
                                    <button
                                        class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600"
                                        type="button"
                                        wire:click.stop="delete({{ $table->id }})"
                                        wire:confirm="Hapus meja ini?"
                                        title="Hapus meja"
                                    >
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-6 space-y-0.5">
                                <div class="text-sm font-bold text-[#0A1628]">Ready to seat</div>
                                <p class="flex items-center gap-1 text-[11px] text-slate-500">
                                    <span class="material-symbols-outlined text-sm" data-icon="event_seat">event_seat</span>
                                    {{ $table->capacity }} Seats
                                </p>
                            </div>
                            <button
                                class="w-full rounded-full bg-[#025864] py-2.5 text-xs font-bold text-white shadow-sm transition-all hover:opacity-90"
                                type="button"
                                wire:click="openEdit({{ $table->id }})"
                            >
                                Assign Table
                            </button>
                        </div>
                    @elseif ($table->status === \App\Models\Table::STATUS_MAINTENANCE)
                        <!-- Table Card: Cleaning -->
                        <div class="relative overflow-hidden rounded-xl border border-[#D1D9D9] bg-white p-6 shadow-sm transition-all hover:border-red-400 group" wire:key="tbl-card-{{ $table->id }}">
                            <div class="mb-4 flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="text-3xl font-black leading-none text-red-600">{{ $table->table_number }}</div>
                                    <div class="mt-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">TABLE NUMBER</div>
                                </div>
                                <div class="flex shrink-0 items-center gap-1.5">
                                    <span class="rounded bg-red-500 px-2 py-0.5 text-[10px] font-bold text-white">CLEANING</span>
                                    <button
                                        class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-[#eff5f5] hover:text-[#025864]"
                                        type="button"
                                        wire:click="openEdit({{ $table->id }})"
                                        title="Ubah meja"
                                    >
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </button>
                                    <button
                                        class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600"
                                        type="button"
                                        wire:click.stop="delete({{ $table->id }})"
                                        wire:confirm="Hapus meja ini?"
                                        title="Hapus meja"
                                    >
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-6 space-y-0.5">
                                <div class="text-sm font-bold text-red-600">{{ \Illuminate\Support\Str::limit($table->location_description, 40) }}</div>
                                <p class="flex items-center gap-1 text-[11px] text-slate-500">
                                    <span class="material-symbols-outlined text-sm" data-icon="event_seat">event_seat</span>
                                    {{ $table->capacity }} Seats
                                </p>
                            </div>
                            <button
                                class="w-full rounded-full border border-[#D1D9D9] bg-[#eff5f5] py-2.5 text-xs font-bold text-[#0A1628] transition-all hover:border-[#00A76F] hover:bg-[#00A76F] hover:text-white"
                                type="button"
                                wire:click="setAvailable({{ $table->id }})"
                            >
                                Set Available
                            </button>
                        </div>
                    @else
                        <!-- Table Card: Inactive (mapped to reserved-style) -->
                        <div class="relative overflow-hidden rounded-xl border border-[#D1D9D9] bg-white p-6 shadow-sm transition-all hover:border-[#F59E0B] group" wire:key="tbl-card-{{ $table->id }}">
                            <div class="mb-4 flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="text-3xl font-black leading-none text-[#F59E0B]">{{ $table->table_number }}</div>
                                    <div class="mt-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">TABLE NUMBER</div>
                                </div>
                                <div class="flex shrink-0 items-center gap-1.5">
                                    <span class="rounded bg-[#F59E0B] px-2 py-0.5 text-[10px] font-bold text-white">RESERVED</span>
                                    <button
                                        class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-[#eff5f5] hover:text-[#025864]"
                                        type="button"
                                        wire:click="openEdit({{ $table->id }})"
                                        title="Ubah meja"
                                    >
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </button>
                                    <button
                                        class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600"
                                        type="button"
                                        wire:click.stop="delete({{ $table->id }})"
                                        wire:confirm="Hapus meja ini?"
                                        title="Hapus meja"
                                    >
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-6 space-y-0.5">
                                <div class="text-sm font-bold text-[#0A1628]">{{ \Illuminate\Support\Str::limit($table->location_description, 36) }}</div>
                                <p class="flex items-center gap-1 text-[11px] text-slate-500">
                                    <span class="material-symbols-outlined text-sm" data-icon="groups">groups</span>
                                    {{ $table->capacity }} Seats
                                </p>
                            </div>
                            @if ($hasActiveBooking)
                                <a
                                    class="block w-full rounded-full border border-[#F59E0B]/50 bg-amber-50 py-2.5 text-center text-xs font-bold text-amber-900 transition-all hover:bg-[#F59E0B] hover:text-white"
                                    href="{{ route('admin.bookings.show', $latestBooking) }}"
                                    wire:navigate
                                >
                                    Check In
                                </a>
                            @else
                                <button
                                    class="w-full rounded-full border border-[#F59E0B]/50 bg-amber-50 py-2.5 text-xs font-bold text-amber-900 transition-all hover:bg-[#F59E0B] hover:text-white disabled:cursor-not-allowed disabled:opacity-50"
                                    type="button"
                                    disabled
                                >
                                    Check In
                                </button>
                            @endif
                        </div>
                    @endif
                @endforeach

                <!-- Add Table Placeholder Card -->
                <div
                    class="flex cursor-pointer flex-col items-center justify-center space-y-3 rounded-xl border-2 border-dashed border-[#D1D9D9] bg-[#eff5f5]/50 p-6 transition-all hover:border-[#025864] hover:bg-[#eff5f5]"
                    role="button"
                    tabindex="0"
                    wire:click="openCreate"
                    wire:keydown.enter="openCreate"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#025864] text-white shadow-sm">
                        <span class="material-symbols-outlined text-2xl">add</span>
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-bold text-[#025864]">Tambah Meja</p>
                        <p class="text-[10px] text-slate-500">Add a new table to grid</p>
                    </div>
                </div>
            </div>
        </section>
        <!-- Right Sidebar -->
        <aside class="w-80 flex flex-shrink-0 flex-col gap-6">
            <!-- Simplified Occupancy Summary -->
            <div class="rounded-xl border border-[#D1D9D9] bg-white p-6">
                <h4 class="mb-6 text-xs font-bold uppercase tracking-wider text-[#025864]">Occupancy Summary</h4>
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <div class="text-4xl font-black text-[#025864]">{{ $stats['booked'] }}/{{ $stats['total'] }}</div>
                        <div class="text-[11px] font-medium uppercase text-slate-500">Tables Used</div>
                    </div>
                    <div class="relative flex h-14 w-14 items-center justify-center">
                        <svg class="h-full w-full -rotate-90" viewbox="0 0 36 36">
                            <circle class="text-[#eff5f5]" cx="18" cy="18" fill="none" r="16" stroke="currentColor" stroke-width="3"></circle>
                            <circle
                                class="text-[#025864]"
                                cx="18"
                                cy="18"
                                fill="none"
                                r="16"
                                stroke="currentColor"
                                stroke-dasharray="{{ $circ * $occupancyPct / 100 }}, {{ $circ }}"
                                stroke-linecap="round"
                                stroke-width="3"
                            ></circle>
                        </svg>
                        <span class="absolute text-[10px] font-bold text-[#025864]">{{ $occupancyPct }}%</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-lg bg-[#eff5f5] p-3">
                        <p class="text-[10px] font-bold uppercase text-slate-500">Available</p>
                        <p class="text-lg font-bold text-[#00A76F]">{{ $stats['available'] }}</p>
                    </div>
                    <div class="rounded-lg bg-[#eff5f5] p-3">
                        <p class="text-[10px] font-bold uppercase text-slate-500">Reserved</p>
                        <p class="text-lg font-bold text-[#F59E0B]">{{ $stats['inactive'] }}</p>
                    </div>
                </div>
            </div>
            <!-- Simplified Waitlist Section -->
            <div class="flex-grow rounded-xl border border-[#D1D9D9] bg-white p-6">
                <div class="mb-6 flex items-center justify-between">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-[#025864]">Waitlist</h4>
                    <span class="rounded-full bg-red-500 px-2 py-0.5 text-[10px] font-bold text-white">3</span>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between rounded-lg bg-[#eff5f5] p-3">
                        <div>
                            <p class="text-sm font-bold text-[#0A1628]">Siska (4)</p>
                            <p class="text-[10px] text-slate-500">12m wait</p>
                        </div>
                        <button class="rounded-full px-3 py-1 text-[10px] font-bold uppercase text-[#025864] hover:bg-[#025864]/10" type="button">Seat</button>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-[#eff5f5] p-3">
                        <div>
                            <p class="text-sm font-bold text-[#0A1628]">Budi (2)</p>
                            <p class="text-[10px] text-slate-500">8m wait</p>
                        </div>
                        <button class="rounded-full px-3 py-1 text-[10px] font-bold uppercase text-[#025864] hover:bg-[#025864]/10" type="button">Seat</button>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-[#eff5f5] p-3">
                        <div>
                            <p class="text-sm font-bold text-[#0A1628]">Rian (5)</p>
                            <p class="text-[10px] text-slate-500">2m wait</p>
                        </div>
                        <button class="rounded-full px-3 py-1 text-[10px] font-bold uppercase text-[#025864] hover:bg-[#025864]/10" type="button">Seat</button>
                    </div>
                </div>
                <button
                    class="mt-6 w-full rounded-full border border-dashed border-[#D1D9D9] py-2.5 text-xs font-bold text-[#025864] transition-all hover:border-[#025864] hover:bg-[#eff5f5]"
                    type="button"
                >
                    + Add to Waitlist
                </button>
            </div>
            <!-- Simple Kitchen Sync -->
            <div class="rounded-xl bg-[#025864] p-6 text-white">
                <div class="mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-xl" data-icon="restaurant">restaurant</span>
                    <span class="text-xs font-bold uppercase tracking-wider">Kitchen Load</span>
                </div>
                <p class="mb-3 text-sm">4 orders in progress</p>
                <div class="mb-2 h-1 w-full rounded-full bg-white/20">
                    <div class="h-full w-[70%] rounded-full bg-[#00D47E]"></div>
                </div>
                <p class="text-[10px] text-white/70">Avg. preparation time: 15m</p>
            </div>
        </aside>
    </div>
</div>

<x-dashboard.modal :show="$showModal">
        <x-slot name="heading">
            <h3 class="text-lg font-bold text-[#0A1628]">{{ $editingId ? 'Ubah meja' : 'Meja baru' }}</h3>
        </x-slot>
        <form class="mt-6 flex flex-col gap-4" wire:submit="save">
            <div>
                <label class="text-xs font-bold uppercase text-slate-500" for="tbl-number">Nomor meja</label>
                <input
                    class="mt-1 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                    id="tbl-number"
                    type="text"
                    wire:model="table_number"
                />
                @error('table_number')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500" for="tbl-cap">Kapasitas</label>
                <input
                    class="mt-1 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                    id="tbl-cap"
                    type="number"
                    min="1"
                    max="99"
                    wire:model.live="capacity"
                />
                @error('capacity')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500" for="tbl-loc">Deskripsi lokasi</label>
                <textarea
                    class="mt-1 min-h-[88px] w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                    id="tbl-loc"
                    wire:model="location_description"
                ></textarea>
                @error('location_description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500" for="tbl-status">Status</label>
                <select
                    class="mt-1 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                    id="tbl-status"
                    wire:model="status"
                >
                    @foreach ($tableStatusLabels as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    @if ($editingId)
                        <button
                            class="rounded-full border border-red-200 px-5 py-2 text-sm font-bold text-red-600 transition-colors hover:bg-red-50"
                            type="button"
                            wire:click="delete({{ $editingId }})"
                            wire:confirm="Hapus meja ini? Tindakan tidak dapat dibatalkan."
                        >
                            Hapus meja
                        </button>
                    @endif
                </div>
                <div class="flex gap-2">
                    <button
                        class="rounded-full px-5 py-2 text-sm font-bold text-slate-600 hover:bg-slate-100"
                        type="button"
                        wire:click="closeModal"
                    >
                        Batal
                    </button>
                    <button
                        class="rounded-full bg-[#025864] px-5 py-2 text-sm font-bold text-white hover:opacity-90"
                        type="submit"
                    >
                        Simpan
                    </button>
                </div>
            </div>
        </form>
</x-dashboard.modal>
</div>
