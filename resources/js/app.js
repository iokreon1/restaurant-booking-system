import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm.js';
import registerDashboardAlert from './alpine/alert.js';
import registerDashboardSidebar from './alpine/dashboard-sidebar.js';

/**
 * Satu instance Alpine dari bundle Livewire (bukan `import Alpine from "alpinejs"`),
 * supaya tidak bentrok dengan Livewire — lihat dokumentasi Livewire "Manually bundling".
 */
window.Alpine = Alpine;

registerDashboardAlert(Alpine);
registerDashboardSidebar(Alpine);
Livewire.start();
