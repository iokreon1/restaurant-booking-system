<header
    class="fixed top-0 right-0 z-40 flex h-16 items-center gap-4 border-b border-[#D1D9D9] bg-white px-4 transition-[left] duration-200 ease-out sm:px-8"
    x-bind:class="collapsed ? 'left-0 sm:left-[72px]' : 'left-0 sm:left-64'"
>
    <button
        class="inline-flex shrink-0 rounded-lg border border-transparent p-2 text-[#0A1628] transition-colors hover:bg-[#eff5f5] focus:outline-none focus:ring-4 focus:ring-[#025864]/15 sm:hidden"
        data-drawer-target="logo-sidebar"
        data-drawer-toggle="logo-sidebar"
        type="button"
        aria-controls="logo-sidebar"
        x-on:click="toggleMobileDrawer()"
    >
        <span class="sr-only">Open sidebar</span>
        <svg class="h-6 w-6 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M5 7h14M5 12h14M5 17h10"/>
        </svg>
    </button>

    <div class="flex min-w-0 flex-1 items-center justify-end gap-6">
        <button class="relative p-2 text-slate-500 transition-colors hover:text-[#025864]" type="button">
            <span class="material-symbols-outlined text-[24px]">notifications</span>
            <span class="absolute top-1.5 right-1.5 flex h-4 w-4 items-center justify-center rounded-full border-2 border-white bg-red-500 text-[9px] font-bold text-white">3</span>
        </button>
        <div class="h-8 w-8 overflow-hidden rounded-full border border-slate-200">
            <img
                alt="SR"
                class="h-full w-full object-cover"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuDSMIeoeoliliGMHQ-dn7uo74vxImDVyrdOhEaNQsgQxWPITdqeeh053O3lnpY2E45Bl4qIn0VKkaw6e4Veadn-4eo4ARWI4A21XVUhiaO1nvib67IftqpesRy4FB_O3n_Ec-6ppM3EZ8UoN9f-OuxTp3RyW0JTngsTInaFg_5x4T7Drv8fSgrUXyBRWy1Kdzi7jzoPaK6TxiVs7boxkd15vuGlSC4SSrVqcUxPjJY595prCYBpmIidKMZt-VqXMjYHqST17PgT6qk"
            />
        </div>
    </div>
</header>
