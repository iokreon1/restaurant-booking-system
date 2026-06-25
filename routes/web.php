<?php

use App\Http\Controllers\DashboardController;
use App\Livewire\Admin\Booking\BookingCreatePage;
use App\Livewire\Admin\Booking\BookingEditPage;
use App\Livewire\Admin\Booking\BookingShowPage;
use App\Livewire\Admin\Booking\BookingsPage;
use App\Livewire\Admin\CustomersPage;
use App\Livewire\Admin\MenuCategoriesPage;
use App\Livewire\Admin\MenuItemsPage;
use App\Livewire\Admin\StaffPage;
use App\Livewire\Admin\TableManagementPage;
use App\Livewire\Admin\TransactionsPage;
use App\Livewire\Microsite\CatalogMicrosite;
use App\Livewire\Microsite\LandingPageMicrosite;
use App\Livewire\Microsite\OrderReservationMicrosite;
use App\Livewire\Microsite\OrderSummaryMicrosite;
use App\Livewire\Microsite\OrderTrackingMicrosite;
use Illuminate\Support\Facades\Route;

Route::get('', LandingPageMicrosite::class)->name('home');
Route::get('menu', CatalogMicrosite::class)->name('microsite.menu');
Route::get('menu/order-summary', OrderSummaryMicrosite::class)->name('microsite.summary');
Route::get('menu/order-reservation', OrderReservationMicrosite::class)->name('microsite.reservation');
Route::get('menu/order-tracking', OrderTrackingMicrosite::class)->name('microsite.tracking');

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'verified', 'admin']], function () {
    Route::get('', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('bookings/create', BookingCreatePage::class)->name('admin.bookings.create');
    Route::get('bookings/{booking}/edit', BookingEditPage::class)->name('admin.bookings.edit');
    Route::get('bookings/{booking}', BookingShowPage::class)->name('admin.bookings.show');
    Route::get('bookings', BookingsPage::class)->name('admin.bookings');
    Route::get('transactions', TransactionsPage::class)->name('admin.transactions');
    Route::get('menu-items', MenuItemsPage::class)->name('admin.menu-items');
    Route::get('menu-categories', MenuCategoriesPage::class)->name('admin.menu-categories');
    Route::get('table-management', TableManagementPage::class)->name('admin.table-management');
    Route::get('customers', CustomersPage::class)->name('admin.customers');
    Route::get('staff', StaffPage::class)->name('admin.staff');
});

require __DIR__.'/settings.php';
