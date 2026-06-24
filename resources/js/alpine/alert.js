/**
 * Instance tunggal setelah komponen `x-data="dashboardAlert"` ter-init (satu per layout).
 * Dipakai oleh `Alpine.magic('dashboardAlert')` dan helper `window.showDashboardAlert`.
 *
 * @type {{ show: Function, hide: Function, ui: Function } | null}
 */
let dashboardAlertApi = null;

let windowHelpersAttached = false;

/**
 * Perluasan Alpine: `Alpine.data('dashboardAlert')` untuk dialog alert dashboard.
 * `Alpine.magic('dashboardAlert')` memunculkan `$dashboardAlert` di mana pun (mirip akses global ke store).
 *
 * @param {typeof window.Alpine} Alpine
 */
export function extendDashboardAlert(Alpine) {
    const variantUi = {
        success: {
            iconWrap: 'bg-emerald-50',
            icon: 'text-emerald-600',
            symbol: 'check_circle',
        },
        warning: {
            iconWrap: 'bg-orange-50',
            icon: 'text-orange-500',
            symbol: 'warning',
        },
        danger: {
            iconWrap: 'bg-red-50',
            icon: 'text-red-600',
            symbol: 'delete',
        },
        failed: {
            iconWrap: 'bg-rose-50',
            icon: 'text-rose-600',
            symbol: 'error',
        },
    };

    /** Kelas tombol utama (konfirmasi / tutup satu tombol) per varian — selaras layout dialog terpusat. */
    const variantPrimaryBtn = {
        success: 'bg-emerald-600 hover:bg-emerald-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600',
        warning: 'bg-orange-500 hover:bg-orange-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500',
        danger: 'bg-red-600 hover:bg-red-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600',
        failed: 'bg-rose-600 hover:bg-rose-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-600',
    };

    Alpine.data('dashboardAlert', () => ({
        open: false,
        variant: 'success',
        title: '',
        message: '',
        closeLabel: 'Tutup',
        confirmMode: false,
        onConfirm: null,
        confirmLabel: 'Ya',
        cancelLabel: 'Batal',

        init() {
            dashboardAlertApi = this;
        },

        ui() {
            return variantUi[this.variant] ?? variantUi.success;
        },

        primaryButtonClass() {
            return variantPrimaryBtn[this.variant] ?? variantPrimaryBtn.success;
        },

        /** Tombol X pojok kanan: tidak ditampilkan pada alert sukses satu tombol (sesuai desain). */
        showCornerClose() {
            if (this.variant === 'success' && !this.confirmMode) {
                return false;
            }

            return true;
        },

        show(options = {}) {
            this.confirmMode = false;
            this.onConfirm = null;
            const allowed = ['success', 'warning', 'danger', 'failed'];
            const v = options.variant ?? 'success';
            this.variant = allowed.includes(v) ? v : 'success';
            this.title = options.title ?? '';
            this.message = options.message ?? '';
            this.closeLabel = options.closeLabel ?? 'Tutup';
            this.open = true;
        },

        /**
         * Dialog konfirmasi (dua tombol). Memanggil options.onConfirm lalu menutup.
         *
         * @param {{ title?: string, message?: string, variant?: string, confirmLabel?: string, cancelLabel?: string, onConfirm?: () => void }} options
         */
        showConfirm(options = {}) {
            this.confirmMode = true;
            this.onConfirm = typeof options.onConfirm === 'function' ? options.onConfirm : () => {};
            this.confirmLabel = options.confirmLabel ?? 'Ya';
            this.cancelLabel = options.cancelLabel ?? 'Batal';
            const allowed = ['success', 'warning', 'danger', 'failed'];
            const v = options.variant ?? 'warning';
            this.variant = allowed.includes(v) ? v : 'warning';
            this.title = options.title ?? '';
            this.message = options.message ?? '';
            this.open = true;
        },

        runConfirm() {
            const fn = this.onConfirm;
            this.confirmMode = false;
            this.onConfirm = null;
            this.open = false;
            if (typeof fn === 'function') {
                fn();
            }
        },

        cancelConfirm() {
            this.hide();
        },

        hide() {
            this.open = false;
            this.confirmMode = false;
            this.onConfirm = null;
        },

        success(title = '', message = '', closeLabel = undefined) {
            this.show({ variant: 'success', title, message, closeLabel });
        },

        warning(title = '', message = '', closeLabel = undefined) {
            this.show({ variant: 'warning', title, message, closeLabel });
        },

        danger(title = '', message = '', closeLabel = undefined) {
            this.show({ variant: 'danger', title, message, closeLabel });
        },

        failed(title = '', message = '', closeLabel = undefined) {
            this.show({ variant: 'failed', title, message, closeLabel });
        },
    }));

    Alpine.magic('dashboardAlert', () => {
        return dashboardAlertApi;
    });

    if (!windowHelpersAttached) {
        windowHelpersAttached = true;
        window.showDashboardAlert = function (options) {
            dashboardAlertApi?.show(options ?? {});
        };
        window.hideDashboardAlert = function () {
            dashboardAlertApi?.hide();
        };
    }
}

/**
 * Dipanggil dari `app.js` (import statis); mendaftarkan data + magic + helper `window` sebelum Livewire.start().
 *
 * @param {typeof window.Alpine} Alpine
 */
export default function registerDashboardAlert(Alpine) {
    extendDashboardAlert(Alpine);
}
