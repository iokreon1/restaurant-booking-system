/**
 * State sidebar dashboard: drawer mobile + collapse desktop + persist collapse.
 *
 * @param {typeof window.Alpine} Alpine
 */
export default function registerDashboardSidebar(Alpine) {
    Alpine.data('dashboardSidebar', () => ({
        collapsed: false,

        mobileOpen: false,

        storageKey: 'dashboard-sidebar-collapsed',

        init() {
            const stored = localStorage.getItem(this.storageKey);

            if (stored !== null) {
                this.collapsed = stored === 'true';
            }
        },

        toggle() {
            this.collapsed = !this.collapsed;
            localStorage.setItem(this.storageKey, String(this.collapsed));
        },

        toggleMobileDrawer() {
            this.mobileOpen = !this.mobileOpen;
        },

        closeMobileDrawer() {
            this.mobileOpen = false;
        },
    }));
}
