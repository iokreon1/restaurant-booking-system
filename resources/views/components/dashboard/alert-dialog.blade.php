{{--
    Dialog alert dashboard — state di Alpine.data `dashboardAlert` + magic `$dashboardAlert` untuk akses dari komponen lain.

    Sertakan sekali per layout, contoh di `layouts/dashboard.blade.php`:
        <x-dashboard.alert-dialog />

    Buka / tutup dari markup (di luar subtree ini gunakan `$dashboardAlert`):
        <button type="button" @click="$dashboardAlert.show({ variant: 'success', title: 'Berhasil', message: 'Data tersimpan.' })">
            Tes sukses
        </button>
        <button type="button" @click="$dashboardAlert.warning('Perhatian', 'Stok menipis.')">Peringatan</button>
        <button type="button" @click="$dashboardAlert.danger('Bahaya', 'Tindakan ini berisiko.')">Bahaya</button>
        <button type="button" @click="$dashboardAlert.failed('Gagal', 'Permintaan tidak dapat diproses.')">Gagal</button>
        <button
            type="button"
            @click="$dashboardAlert.showConfirm({ variant: 'warning', title: 'Konfirmasi', message: 'Lanjutkan tindakan ini?', confirmLabel: 'Ya', cancelLabel: 'Batal', onConfirm: () => {} })"
        >
            Konfirmasi (contoh)
        </button>

    Dari JavaScript (setelah halaman siap):
        showDashboardAlert({ variant: 'failed', title: 'Error', message: '...' });
        hideDashboardAlert();

    Slot opsional:
      actions — menggantikan tombol tutup default (tombol kustom).

    Props:
      zIndex — kelas z-index overlay, default z-[60].
--}}

@props([
    'zIndex' => 'z-[60]',
])

<div x-data="dashboardAlert" class="contents">
    <template x-teleport="body">
        <div
            x-cloak
            x-show="open"
            x-transition.opacity.duration.200ms
            @keydown.escape.window="hide()"
            @class([
                'fixed inset-0 flex items-center justify-center bg-black/45 p-4',
                $zIndex,
            ])
            @click.self="hide()"
        >
            <div
                class="relative w-full max-w-[380px] rounded-2xl border border-slate-200/80 bg-white px-8 pb-8 pt-10 shadow-xl"
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="dashboard-alert-title"
                aria-describedby="dashboard-alert-message"
                @click.stop
            >
                <button
                    class="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-full text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
                    type="button"
                    x-show="showCornerClose()"
                    x-cloak
                    @click="hide()"
                    aria-label="Tutup dialog"
                >
                    <span class="material-symbols-outlined text-[22px] leading-none" aria-hidden="true">close</span>
                </button>

                <div class="flex flex-col items-center text-center">
                    <div
                        class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full"
                        :class="ui().iconWrap"
                    >
                        <span
                            class="material-symbols-outlined text-[32px] leading-none"
                            :class="ui().icon"
                            aria-hidden="true"
                            x-text="ui().symbol"
                        ></span>
                    </div>
                    <h3
                        id="dashboard-alert-title"
                        class="mt-5 text-xl font-bold tracking-tight text-[#0A1628]"
                        x-show="title"
                        x-text="title"
                    ></h3>
                    <p
                        id="dashboard-alert-message"
                        class="mt-2 max-w-sm text-sm leading-relaxed text-slate-500"
                        x-show="message"
                        x-text="message"
                    ></p>
                </div>

                @isset($actions)
                    <div class="mt-8 flex flex-col gap-3">
                        {{ $actions }}
                    </div>
                @else
                    <div class="mt-8 flex w-full gap-3" x-show="confirmMode" x-cloak>
                        <button
                            class="flex-1 rounded-xl bg-slate-100 py-3.5 text-sm font-bold text-slate-700 shadow-sm transition-colors hover:bg-slate-200"
                            type="button"
                            @click="cancelConfirm()"
                            x-text="cancelLabel"
                        ></button>
                        <button
                            class="flex-1 rounded-xl py-3.5 text-sm font-bold text-white shadow-sm transition-opacity hover:opacity-95"
                            type="button"
                            :class="primaryButtonClass()"
                            @click="runConfirm()"
                            x-text="confirmLabel"
                        ></button>
                    </div>
                    <div class="mt-8 w-full" x-show="!confirmMode">
                        <button
                            class="w-full rounded-xl py-3.5 text-sm font-bold text-white shadow-sm transition-opacity hover:opacity-95"
                            type="button"
                            :class="primaryButtonClass()"
                            @click="hide()"
                            x-text="closeLabel"
                        ></button>
                    </div>
                @endisset
            </div>
        </div>
    </template>
</div>
