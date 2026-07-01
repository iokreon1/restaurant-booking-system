@php
    $navigationItems = [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'dashboard',
            'active_patterns' => ['dashboard'],
        ],
        [
            'label' => 'Booking',
            'route' => 'admin.bookings',
            'icon' => 'event_available',
            'badge' => '5',
            'active_patterns' => ['admin.bookings'],
        ],
        [
            'label' => 'Transaksi',
            'route' => 'admin.transactions',
            'icon' => 'receipt_long',
            'active_patterns' => ['admin.transactions'],
        ],
        [
            'label' => 'Menu Makanan',
            'route' => 'admin.menu-items',
            'icon' => 'restaurant_menu',
            'active_patterns' => ['admin.menu-items'],
        ],
        [
            'label' => 'Kategori Menu',
            'route' => 'admin.menu-categories',
            'icon' => 'category',
            'active_patterns' => ['admin.menu-categories'],
        ],
        [
            'label' => 'Manajemen Meja',
            'route' => 'admin.table-management',
            'icon' => 'table_restaurant',
            'active_patterns' => ['admin.table-management'],
        ],
        [
            'label' => 'Daftar Customer',
            'route' => 'admin.customers',
            'icon' => 'group',
            'active_patterns' => ['admin.customers'],
        ],
        [
            'label' => 'Daftar Staff',
            'route' => 'admin.staff',
            'icon' => 'admin_panel_settings',
            'active_patterns' => ['admin.staff'],
        ],
    ];
@endphp

{{-- Overlay mobile (Flowbite-style drawer) --}}
<div
    class="fixed inset-0 z-30 bg-black/40 sm:hidden"
    x-cloak
    x-on:click="closeMobileDrawer()"
    x-show="mobileOpen"
    x-transition.opacity
></div>

<aside
    id="logo-sidebar"
    aria-label="Sidebar"
    class="fixed left-0 top-0 z-40 flex h-full w-64 flex-col border-r border-[#D1D9D9] bg-white transition-[width,transform] duration-200 ease-out -translate-x-full sm:translate-x-0"
    x-bind:class="[mobileOpen ? 'translate-x-0' : '', collapsed ? 'sm:!w-[72px]' : 'sm:!w-64']"
>
    <div class="flex h-full min-h-0 flex-col overflow-y-auto px-3 py-4 sm:py-6">
        <div class="mb-6 px-1 sm:mb-8" x-bind:class="collapsed ? 'px-0' : 'sm:px-1'">
            <div
                class="flex gap-2"
                x-bind:class="collapsed ? 'flex-col items-center gap-3' : 'items-center justify-between'"
            >
                <a
                    class="flex min-w-0 items-center gap-3 ps-0.5"
                    href="{{ route('dashboard') }}"
                    wire:navigate
                    x-on:click="closeMobileDrawer()"
                >
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#025864] text-white">
                        <span class="material-symbols-outlined">restaurant</span>
                    </div>
                    <div class="min-w-0" x-show="! collapsed" x-cloak>
                        <span class="block text-lg font-semibold leading-tight tracking-tight text-[#003f48]">Empon Pawon</span>
                        <span class="block text-[10px] uppercase tracking-widest text-slate-500 opacity-70">Admin Panel</span>
                    </div>
                </a>
                <button
                    class="hidden shrink-0 rounded-lg p-2 text-slate-500 transition-colors hover:bg-[#eff5f5] hover:text-[#025864] sm:inline-flex"
                    id="dashboard-sidebar-toggle"
                    type="button"
                    x-bind:aria-expanded="! collapsed"
                    x-bind:aria-label="collapsed ? 'Buka sidebar penuh' : 'Ciutkan sidebar'"
                    x-on:click="toggle()"
                >
                    <span class="material-symbols-outlined text-[22px]" x-show="! collapsed" x-cloak>chevron_left</span>
                    <span class="material-symbols-outlined text-[22px]" x-show="collapsed" x-cloak>chevron_right</span>
                </button>
            </div>
        </div>

        <nav
            class="no-scrollbar flex-1 space-y-2 font-medium"
            aria-label="Menu utama"
            x-on:click.capture="$event.target.closest('a[href]') && closeMobileDrawer()"
        >
            <ul class="space-y-1">
                @foreach ($navigationItems as $navigationItem)
                    @php
                        $isActive = request()->routeIs(...$navigationItem['active_patterns']);
                    @endphp
                    <li>
                        <a
                            @class([
                                'flex items-center rounded-lg px-2 py-1.5 text-sm transition-colors duration-75',
                                'bg-[#dee4e4] font-medium text-[#003f48]' => $isActive,
                                'text-slate-700 hover:bg-[#eff5f5] hover:text-[#025864] group' => ! $isActive,
                            ])
                            x-bind:class="collapsed ? 'justify-center !px-1.5 sm:justify-center' : (! $isActive ? 'px-2' : '')"
                            href="{{ Route::has($navigationItem['route']) ? route($navigationItem['route']) : '#' }}"
                            title="{{ $navigationItem['label'] }}"
                            wire:navigate
                        >
                            <span
                                @class([
                                    'material-symbols-outlined shrink-0 text-[22px] leading-none',
                                    'opacity-80 group-hover:opacity-100' => ! $isActive,
                                ])
                                x-bind:class="collapsed ? '' : 'mr-3'"
                            >{{ $navigationItem['icon'] }}</span>
                            <span class="flex-1 whitespace-nowrap" x-show="! collapsed" x-cloak>{{ $navigationItem['label'] }}</span>

                            @if (filled($navigationItem['badge'] ?? null))
                                <span
                                    class="ms-auto inline-flex shrink-0 items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $isActive ? 'bg-[#003f48] text-white' : 'border border-slate-200 bg-red-500 text-white' }}"
                                    x-show="! collapsed"
                                    x-cloak
                                >
                                    {{ $navigationItem['badge'] }}
                                </span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="mt-auto border-t border-[#D1D9D9] pt-4">
            <div
                class="mb-3 flex items-center gap-3 rounded-lg bg-[#eff5f5] p-2"
                x-bind:class="collapsed ? 'flex-col justify-center' : ''"
            >
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#025864] text-sm font-bold text-white"
                    title="{{ auth()->user()->name }}"
                >
                    {{ auth()->user()->initials() }}
                </div>
                <div class="min-w-0 flex-1 overflow-hidden" x-show="! collapsed" x-cloak>
                    <p class="truncate text-sm font-semibold text-[#003f48]">{{ auth()->user()->name }}</p>
                    <p class="truncate text-[10px] text-slate-500">{{ auth()->user()->email }}</p>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button
                    class="flex w-full items-center rounded-lg px-2 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50/80"
                    title="Logout"
                    type="submit"
                    x-bind:class="collapsed ? 'justify-center' : ''"
                >
                    <span class="material-symbols-outlined shrink-0 text-[22px] sm:mr-3" x-bind:class="collapsed ? '!mr-0' : ''">logout</span>
                    <span x-show="! collapsed" x-cloak>Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>
