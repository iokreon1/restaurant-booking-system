<?php

use App\Models\User;

dataset('admin pages', [
    ['admin.bookings', 'Booking'],
    ['admin.transactions', 'Transaksi'],
    ['admin.menu-items', 'Menu Makanan'],
    ['admin.menu-categories', 'Kategori Menu'],
    ['admin.table-management', 'Manajemen Meja'],
    ['admin.customers', 'Daftar Customer'],
    ['admin.staff', 'Daftar Staff'],
]);

test('guests are redirected to login when visiting admin livewire pages', function (string $routeName) {
    $this->get(route($routeName))
        ->assertRedirect(route('login'));
})->with('admin pages');

test('authenticated users can visit each admin livewire page', function (string $routeName, string $heading) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route($routeName))
        ->assertOk()
        ->assertSee($heading);
})->with('admin pages');

test('dashboard sidebar contains links to all admin modules', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();

    foreach (['dashboard', 'admin.bookings', 'admin.transactions', 'admin.menu-items', 'admin.menu-categories', 'admin.table-management', 'admin.customers', 'admin.staff'] as $routeName) {
        $response->assertSee(route($routeName), false);
    }

    $response->assertSee('id="dashboard-sidebar-toggle"', false)
        ->assertSee('id="logo-sidebar"', false)
        ->assertSee('data-drawer-target="logo-sidebar"', false);
});
