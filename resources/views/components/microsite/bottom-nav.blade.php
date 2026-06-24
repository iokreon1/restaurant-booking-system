@props([
    'active' => 'menu',
])

<nav class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] z-50 flex justify-around items-center px-4 pb-6 pt-3 bg-white/90 backdrop-blur-md border-t border-outline-variant/10 shadow-lg">
    <a @class([
        'flex flex-col items-center justify-center rounded-xl px-5 py-1.5 active:scale-95 transition-all',
        'bg-secondary-container/50 text-secondary' => $active === 'menu',
        'text-outline hover:text-primary' => $active !== 'menu',
    ]) href="{{ route('microsite.menu') }}">
        <span class="material-symbols-outlined" data-icon="restaurant_menu">restaurant_menu</span>
        <span class="text-[10px] font-bold uppercase tracking-wide mt-0.5">Menu</span>
    </a>
    <a @class([
        'flex flex-col items-center justify-center rounded-xl px-5 py-1.5 active:scale-95 transition-all',
        'bg-secondary-container/50 text-secondary' => $active === 'bookings',
        'text-outline hover:text-primary' => $active !== 'bookings',
    ]) href="{{ route('microsite.tracking') }}">
        <span class="material-symbols-outlined" data-icon="event_available">event_available</span>
        <span class="text-[10px] font-bold uppercase tracking-wide mt-0.5">Bookings</span>
    </a>
    <a class="flex flex-col items-center justify-center text-outline px-5 py-1.5 hover:text-primary transition-all active:scale-95"
    href="#">
        <span class="material-symbols-outlined" data-icon="person">person</span>
        <span class="text-[10px] font-bold uppercase tracking-wide mt-0.5">Profile</span>
    </a>
</nav>
