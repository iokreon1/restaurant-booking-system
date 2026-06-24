<div>
    <div class="mt-16 flex flex-col gap-6 p-8">
        @if (session('status'))
            <div class="rounded-[12px] border border-[#00D47E]/40 bg-[#DCFCE7] px-4 py-3 text-sm font-semibold text-[#15803D]">
                {{ session('status') }}
            </div>
        @endif

        @error('delete')
            <div class="rounded-[12px] border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                {{ $message }}
            </div>
        @enderror

        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <h2 class="text-3xl font-bold text-[#0A1628]">Daftar Staff</h2>
                <p class="mt-1 font-medium text-slate-500">Akun dengan peran admin untuk mengelola panel ini.</p>
            </div>
            <button
                class="flex items-center gap-2 rounded-full bg-[#025864] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:opacity-90"
                type="button"
                wire:click="openCreate"
            >
                <span class="material-symbols-outlined text-[18px]">person_add</span>
                Tambah staff
            </button>
        </div>

        <section class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div class="rounded-[12px] border border-[#D1D9D9] border-l-[4px] border-l-[#025864] bg-white p-5 shadow-sm card-shadow">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total staff (admin)</p>
                <h3 class="mt-2 text-2xl font-bold text-[#0A1628]">{{ $stats['total'] }}</h3>
            </div>
            <div class="rounded-[12px] border border-[#D1D9D9] border-l-[4px] border-l-[#00D47E] bg-white p-5 shadow-sm card-shadow">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Email terverifikasi</p>
                <h3 class="mt-2 text-2xl font-bold text-[#0A1628]">{{ $stats['verified'] }}</h3>
            </div>
        </section>

        <x-dashboard.data-table>
            <x-slot name="thead">
                <tr class="border-b border-slate-100 bg-[#eff5f5] text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <th class="px-6 py-4">Nama</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4">Bergabung</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </x-slot>
            @forelse ($staffMembers as $member)
                <tr class="hover:bg-slate-50/80" wire:key="staff-{{ $member->id }}">
                    <td class="px-6 py-4 font-semibold text-[#0A1628]">
                        {{ $member->name }}
                        @if ($member->id === auth()->id())
                            <span class="ml-2 rounded-full bg-[#eff5f5] px-2 py-0.5 text-[10px] font-bold uppercase text-[#025864]">Anda</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $member->email }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $member->created_at?->translatedFormat('d M Y') }}</td>
                    <td class="px-6 py-4 text-right">
                        <button
                            class="mr-1 rounded-lg p-2 text-[#025864] hover:bg-[#eff5f5]"
                            type="button"
                            wire:click="openEdit({{ $member->id }})"
                        >
                            <span class="material-symbols-outlined text-[20px]">edit</span>
                        </button>
                        <button
                            class="rounded-lg p-2 text-red-600 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40"
                            type="button"
                            wire:click="delete({{ $member->id }})"
                            wire:confirm="Hapus akun staff ini?"
                            @disabled($member->id === auth()->id())
                        >
                            <span class="material-symbols-outlined text-[20px]">delete</span>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="px-6 py-12 text-center text-slate-500" colspan="4">Belum ada staff admin.</td>
                </tr>
            @endforelse
            <x-slot name="footer">
                <x-dashboard.table-pagination :paginator="$staffMembers" />
            </x-slot>
        </x-dashboard.data-table>
    </div>

    <x-dashboard.modal :show="$showModal">
        <x-slot name="heading">
            <h3 class="text-lg font-bold text-[#0A1628]">{{ $editingId ? 'Ubah staff' : 'Staff baru' }}</h3>
        </x-slot>
        <form class="mt-6 flex flex-col gap-4" wire:submit="save">
            <div>
                <label class="text-xs font-bold uppercase text-slate-500" for="staff-name">Nama</label>
                <input
                    class="mt-1 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                    id="staff-name"
                    type="text"
                    autocomplete="name"
                    wire:model="name"
                />
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500" for="staff-email">Email</label>
                <input
                    class="mt-1 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                    id="staff-email"
                    type="email"
                    autocomplete="email"
                    wire:model="email"
                />
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500" for="staff-password">
                    Password
                    @if ($editingId)
                        <span class="font-normal normal-case text-slate-500">(kosongkan jika tidak diubah)</span>
                    @endif
                </label>
                <input
                    class="mt-1 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                    id="staff-password"
                    type="password"
                    autocomplete="new-password"
                    wire:model="password"
                />
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500" for="staff-password-confirmation">Konfirmasi password</label>
                <input
                    class="mt-1 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                    id="staff-password-confirmation"
                    type="password"
                    autocomplete="new-password"
                    wire:model="password_confirmation"
                />
                @error('password_confirmation')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="mt-2 flex justify-end gap-2">
                <button
                    class="rounded-full px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100"
                    type="button"
                    wire:click="closeModal"
                >
                    Batal
                </button>
                <button
                    class="rounded-full bg-[#025864] px-5 py-2 text-sm font-bold text-white shadow-sm hover:opacity-90"
                    type="submit"
                >
                    Simpan
                </button>
            </div>
        </form>
    </x-dashboard.modal>
</div>
