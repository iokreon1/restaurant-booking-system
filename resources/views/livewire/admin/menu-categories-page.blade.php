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
                <h2 class="text-3xl font-bold text-[#0A1628]">Kategori Menu</h2>
                <p class="mt-1 font-medium text-slate-500">Kelompokkan menu agar katalog dan dapur lebih rapi.</p>
            </div>
            <button
                class="flex items-center gap-2 rounded-full bg-[#025864] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:opacity-90"
                type="button"
                wire:click="openCreate"
            >
                <span class="material-symbols-outlined text-[18px]">add</span>
                Tambah kategori
            </button>
        </div>

        <section class="grid grid-cols-1 gap-5 md:grid-cols-3">
            <div class="rounded-[12px] border border-[#D1D9D9] border-l-[4px] border-l-[#025864] bg-white p-5 shadow-sm card-shadow">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total kategori</p>
                <h3 class="mt-2 text-2xl font-bold text-[#0A1628]">{{ $stats['total'] }}</h3>
            </div>
            <div class="rounded-[12px] border border-[#D1D9D9] border-l-[4px] border-l-[#00D47E] bg-white p-5 shadow-sm card-shadow">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Aktif</p>
                <h3 class="mt-2 text-2xl font-bold text-[#0A1628]">{{ $stats['active'] }}</h3>
            </div>
            <div class="rounded-[12px] border border-[#D1D9D9] border-l-[4px] border-l-[#7C3AED] bg-white p-5 shadow-sm card-shadow">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total item menu</p>
                <h3 class="mt-2 text-2xl font-bold text-[#0A1628]">{{ $stats['menu_total'] }}</h3>
            </div>
        </section>

        <x-dashboard.data-table>
            <x-slot name="thead">
                <tr class="border-b border-slate-100 bg-[#eff5f5] text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <th class="px-6 py-4">Nama</th>
                    <th class="px-6 py-4">Urutan</th>
                    <th class="px-6 py-4">Jumlah menu</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </x-slot>
            @forelse ($categories as $category)
                <tr class="hover:bg-slate-50/80" wire:key="cat-{{ $category->id }}">
                    <td class="px-6 py-4 font-semibold text-[#0A1628]">{{ $category->name }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $category->sort_order }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $category->menu_items_count }}</td>
                    <td class="px-6 py-4">
                        @if ($category->status === \App\Models\MenuCategory::STATUS_ACTIVE)
                            <span class="rounded-full bg-[#DCFCE7] px-2.5 py-1 text-[10px] font-bold uppercase text-[#15803D]">Aktif</span>
                        @else
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase text-slate-600">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button
                            class="mr-1 rounded-lg p-2 text-[#025864] hover:bg-[#eff5f5]"
                            type="button"
                            wire:click="openEdit({{ $category->id }})"
                        >
                            <span class="material-symbols-outlined text-[20px]">edit</span>
                        </button>
                        <button
                            class="rounded-lg p-2 text-red-600 hover:bg-red-50"
                            type="button"
                            wire:click="delete({{ $category->id }})"
                            wire:confirm="Hapus kategori ini?"
                        >
                            <span class="material-symbols-outlined text-[20px]">delete</span>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="px-6 py-12 text-center text-slate-500" colspan="5">Belum ada kategori. Tambahkan yang pertama.</td>
                </tr>
            @endforelse
        </x-dashboard.data-table>
    </div>

    <x-dashboard.modal :show="$showModal">
        <x-slot name="heading">
            <h3 class="text-lg font-bold text-[#0A1628]">{{ $editingId ? 'Ubah kategori' : 'Kategori baru' }}</h3>
        </x-slot>
        <form class="mt-6 flex flex-col gap-4" wire:submit="save">
                    <div>
                        <label class="text-xs font-bold uppercase text-slate-500" for="cat-name">Nama</label>
                        <input
                            class="mt-1 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                            id="cat-name"
                            type="text"
                            wire:model="name"
                        />
                        @error('name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase text-slate-500" for="cat-sort">Urutan</label>
                        <input
                            class="mt-1 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                            id="cat-sort"
                            type="number"
                            min="0"
                            wire:model.live="sort_order"
                        />
                        @error('sort_order')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase text-slate-500" for="cat-status">Status</label>
                        <select
                            class="mt-1 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                            id="cat-status"
                            wire:model="status"
                        >
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase text-slate-500">
                            Thumbnail
                            @if (! $editingId)
                                <span class="text-red-600">*</span>
                            @endif
                        </p>
                        <p class="mt-0.5 text-[11px] text-slate-500">
                            @if ($editingId)
                                Unggah file baru untuk mengganti gambar. Kosongkan dengan tombol hapus lalu unggah lagi jika perlu.
                            @else
                                Wajib unggah file gambar (mis. JPG, PNG, WebP).
                            @endif
                        </p>
                        <div class="mt-2 flex flex-wrap items-start gap-4">
                            <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-[#eff5f5] ring-1 ring-slate-200/80">
                                @if ($thumbnailUpload)
                                    <img
                                        src="{{ $thumbnailUpload->temporaryUrl() }}"
                                        alt=""
                                        class="h-full w-full object-cover"
                                    />
                                @elseif (filled($thumbnail_path))
                                    <img
                                        src="{{ \Illuminate\Support\Str::startsWith($thumbnail_path, ['http://', 'https://']) ? $thumbnail_path : asset($thumbnail_path) }}"
                                        alt=""
                                        class="h-full w-full object-cover"
                                    />
                                @else
                                    <span class="material-symbols-outlined text-slate-400">image</span>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1 space-y-2">
                                <input
                                    id="cat-thumb-file"
                                    type="file"
                                    accept="image/*"
                                    class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-[#025864] file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90"
                                    wire:model="thumbnailUpload"
                                />
                                @error('thumbnailUpload')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                @if (filled($thumbnail_path) || $thumbnailUpload)
                                    <button
                                        class="text-xs font-semibold text-red-600 hover:underline"
                                        type="button"
                                        wire:click="removeThumbnail"
                                    >
                                        Hapus gambar
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
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
                </form>
    </x-dashboard.modal>
</div>
