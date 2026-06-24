<div>
    <div class="mt-16 flex flex-col gap-6 p-8">
        @if (session('status'))
            <div class="rounded-[12px] border border-[#00D47E]/40 bg-[#DCFCE7] px-4 py-3 text-sm font-semibold text-[#15803D]">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <h2 class="text-3xl font-bold text-[#0A1628]">Menu Makanan</h2>
                <p class="mt-1 font-medium text-slate-500">Kelola nama, harga, kategori, dan status ketersediaan.</p>
            </div>
            <button
                class="flex items-center gap-2 rounded-full bg-[#025864] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                type="button"
                wire:click="openCreate"
                @disabled($categories->isEmpty())
            >
                <span class="material-symbols-outlined text-[18px]">add</span>
                Tambah menu
            </button>
        </div>

        <section class="grid grid-cols-1 gap-5 md:grid-cols-3">
            <div class="rounded-[12px] border border-[#D1D9D9] border-l-[4px] border-l-[#025864] bg-white p-5 shadow-sm card-shadow">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total menu</p>
                <h3 class="mt-2 text-2xl font-bold text-[#0A1628]">{{ $stats['total'] }}</h3>
            </div>
            <div class="rounded-[12px] border border-[#D1D9D9] border-l-[4px] border-l-[#00D47E] bg-white p-5 shadow-sm card-shadow">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Tersedia</p>
                <h3 class="mt-2 text-2xl font-bold text-[#0A1628]">{{ $stats['available'] }}</h3>
            </div>
            <div class="rounded-[12px] border border-[#D1D9D9] border-l-[4px] border-l-[#F59E0B] bg-white p-5 shadow-sm card-shadow">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Habis</p>
                <h3 class="mt-2 text-2xl font-bold text-[#0A1628]">{{ $stats['soldout'] }}</h3>
            </div>
        </section>

        <div class="flex flex-wrap items-center gap-4 rounded-[12px] border border-[#D1D9D9] bg-white p-4 card-shadow">
            <div class="relative min-w-[240px] flex-1">
                <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[20px] text-slate-400">search</span>
                <input
                    class="w-full rounded-lg border-0 bg-[#eff5f5] py-2.5 pl-10 pr-4 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                    type="search"
                    placeholder="Cari nama atau deskripsi..."
                    wire:model.live.debounce.300ms="search"
                />
            </div>
            <select
                class="rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                wire:model.live="categoryId"
            >
                <option value="">Semua kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <select
                class="rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                wire:model.live="statusFilter"
            >
                <option value="">Semua status</option>
                <option value="available">Tersedia</option>
                <option value="soldout">Habis</option>
                <option value="inactive">Nonaktif</option>
            </select>
        </div>

        <x-dashboard.data-table>
            <x-slot name="thead">
                <tr class="border-b border-slate-100 bg-[#eff5f5] text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <th class="px-6 py-4">Menu</th>
                    <th class="px-6 py-4">Kategori</th>
                    <th class="px-6 py-4">Harga</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </x-slot>
            @forelse ($items as $item)
                <tr class="hover:bg-slate-50/80" wire:key="item-{{ $item->id }}">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-[#eff5f5]">
                                @if (filled($item->thumbnail_path))
                                    <img
                                        class="h-full w-full object-cover"
                                        alt=""
                                        src="{{ \Illuminate\Support\Str::startsWith($item->thumbnail_path, ['http://', 'https://']) ? $item->thumbnail_path : asset($item->thumbnail_path) }}"
                                    />
                                @else
                                    <span class="material-symbols-outlined text-slate-400">restaurant</span>
                                @endif
                            </div>
                            <div>
                                <p class="font-semibold text-[#0A1628]">{{ $item->name }}</p>
                                <p class="line-clamp-1 text-xs text-slate-500">#{{ $item->id }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $item->category?->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm font-bold text-[#0A1628]">Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
                    <td class="px-6 py-4">
                        @if ($item->status === \App\Models\MenuItem::STATUS_AVAILABLE)
                            <span class="rounded-full bg-[#DCFCE7] px-2.5 py-1 text-[10px] font-bold uppercase text-[#15803D]">Tersedia</span>
                        @elseif ($item->status === \App\Models\MenuItem::STATUS_SOLDOUT)
                            <span class="rounded-full bg-red-50 px-2.5 py-1 text-[10px] font-bold uppercase text-red-700">Habis</span>
                        @else
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase text-slate-600">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button
                            class="mr-1 rounded-lg p-2 text-[#025864] hover:bg-[#eff5f5]"
                            type="button"
                            wire:click="openEdit({{ $item->id }})"
                        >
                            <span class="material-symbols-outlined text-[20px]">edit</span>
                        </button>
                        <button
                            class="rounded-lg p-2 text-red-600 hover:bg-red-50"
                            type="button"
                            wire:click="delete({{ $item->id }})"
                            wire:confirm="Hapus menu ini?"
                        >
                            <span class="material-symbols-outlined text-[20px]">delete</span>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="px-6 py-12 text-center text-slate-500" colspan="5">
                        @if ($categories->isEmpty())
                            Buat kategori menu terlebih dahulu.
                        @else
                            Tidak ada menu yang cocok dengan filter.
                        @endif
                    </td>
                </tr>
            @endforelse
            <x-slot name="footer">
                @if ($items->hasPages())
                    <div class="border-t border-slate-100 px-6 py-4">
                        {{ $items->links() }}
                    </div>
                @endif
            </x-slot>
        </x-dashboard.data-table>
    </div>

    <x-dashboard.modal :show="$showModal">
        <x-slot name="heading">
            <h3 class="text-lg font-bold text-[#0A1628]">{{ $editingId ? 'Ubah menu' : 'Menu baru' }}</h3>
        </x-slot>
        <form class="mt-6 flex flex-col gap-4" wire:submit="save">
                    <div>
                        <label class="text-xs font-bold uppercase text-slate-500" for="item-cat">Kategori</label>
                        <select
                            class="mt-1 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                            id="item-cat"
                            wire:model="form_category_id"
                        >
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('form_category_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase text-slate-500" for="item-name">Nama</label>
                        <input
                            class="mt-1 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                            id="item-name"
                            type="text"
                            wire:model="name"
                        />
                        @error('name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase text-slate-500" for="item-desc">Deskripsi</label>
                        <textarea
                            class="mt-1 min-h-[88px] w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                            id="item-desc"
                            wire:model="description"
                        ></textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase text-slate-500" for="item-price">Harga (Rp)</label>
                        <input
                            class="mt-1 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                            id="item-price"
                            type="text"
                            inputmode="decimal"
                            wire:model="price"
                        />
                        @error('price')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold uppercase text-slate-500" for="item-status">Status</label>
                            <select
                                class="mt-1 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                                id="item-status"
                                wire:model="status"
                            >
                                <option value="available">Tersedia</option>
                                <option value="soldout">Habis</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase text-slate-500" for="item-sort">Urutan</label>
                            <input
                                class="mt-1 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                                id="item-sort"
                                type="number"
                                min="0"
                                wire:model.live="sort_order"
                            />
                            @error('sort_order')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
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
                                    id="item-thumb-file"
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
                        <button class="rounded-full px-5 py-2 text-sm font-bold text-slate-600 hover:bg-slate-100" type="button" wire:click="closeModal">
                            Batal
                        </button>
                        <button class="rounded-full bg-[#025864] px-5 py-2 text-sm font-bold text-white hover:opacity-90" type="submit">Simpan</button>
                    </div>
                </form>
    </x-dashboard.modal>
</div>
