<?php

use App\Livewire\Admin\Booking\BookingCreatePage;
use App\Livewire\Admin\Booking\BookingsPage;
use App\Livewire\Admin\CustomersPage;
use App\Livewire\Admin\MenuCategoriesPage;
use App\Livewire\Admin\MenuItemsPage;
use App\Livewire\Admin\StaffPage;
use App\Livewire\Admin\TableManagementPage;
use App\Livewire\Admin\TransactionsPage;
use App\Models\Booking;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Table;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('admin can create update and delete a menu category when empty', function () {
    Storage::fake('public');
    $thumb = UploadedFile::fake()->image('cat.jpg', 100, 100);

    Livewire::actingAs($this->admin)
        ->test(MenuCategoriesPage::class)
        ->assertOk()
        ->call('openCreate')
        ->set('name', 'Appetizer')
        ->set('sort_order', 1)
        ->set('status', MenuCategory::STATUS_ACTIVE)
        ->set('thumbnail_path', '')
        ->set('thumbnailUpload', $thumb)
        ->call('save')
        ->assertHasNoErrors();

    $category = MenuCategory::query()->where('name', 'Appetizer')->first();
    expect($category)->not->toBeNull()
        ->and($category->thumbnail_path)->toStartWith('storage/menu-categories/thumbnails/');
    Storage::disk('public')->assertExists(Str::after($category->thumbnail_path, 'storage/'));

    Livewire::actingAs($this->admin)
        ->test(MenuCategoriesPage::class)
        ->call('openEdit', $category->id)
        ->set('name', 'Appetizer Updated')
        ->call('save')
        ->assertHasNoErrors();

    expect(MenuCategory::query()->find($category->id)?->name)->toBe('Appetizer Updated');

    Livewire::actingAs($this->admin)
        ->test(MenuCategoriesPage::class)
        ->call('delete', $category->id)
        ->assertHasNoErrors();

    expect(MenuCategory::query()->find($category->id))->toBeNull();
});

test('admin cannot delete menu category that still has items', function () {
    $category = MenuCategory::factory()->create();
    MenuItem::factory()->create(['category_id' => $category->id]);

    Livewire::actingAs($this->admin)
        ->test(MenuCategoriesPage::class)
        ->call('delete', $category->id)
        ->assertHasErrors(['delete']);

    expect(MenuCategory::query()->find($category->id))->not->toBeNull();
});

test('admin can create and delete a menu item', function () {
    Storage::fake('public');
    $category = MenuCategory::factory()->create();
    $thumb = UploadedFile::fake()->image('item.jpg', 100, 100);

    Livewire::actingAs($this->admin)
        ->test(MenuItemsPage::class)
        ->assertOk()
        ->call('openCreate')
        ->set('form_category_id', $category->id)
        ->set('name', 'Sate Maranggi')
        ->set('description', 'Daging sapi bumbu khas.')
        ->set('price', '45000')
        ->set('status', MenuItem::STATUS_AVAILABLE)
        ->set('sort_order', 0)
        ->set('thumbnail_path', '')
        ->set('thumbnailUpload', $thumb)
        ->call('save')
        ->assertHasNoErrors();

    $item = MenuItem::query()->where('name', 'Sate Maranggi')->first();
    expect($item)->not->toBeNull()
        ->and($item->thumbnail_path)->toStartWith('storage/menu-items/thumbnails/');
    Storage::disk('public')->assertExists(Str::after($item->thumbnail_path, 'storage/'));

    Livewire::actingAs($this->admin)
        ->test(MenuItemsPage::class)
        ->call('delete', $item->id)
        ->assertHasNoErrors();

    expect(MenuItem::query()->find($item->id))->toBeNull();
});

test('admin bookings list redirects new booking to create page', function () {
    Livewire::actingAs($this->admin)
        ->test(BookingsPage::class)
        ->call('openCreate')
        ->assertRedirect(route('admin.bookings.create'));
});

test('admin bookings list orders by booking created_at newest first', function () {
    $guest = User::factory()->create();
    $table = Table::factory()->create();

    $older = Booking::factory()->create([
        'user_id' => $guest->id,
        'table_id' => $table->id,
        'payment_status' => Booking::PAYMENT_STATUS_PAID,
    ]);
    $older->forceFill(['created_at' => now()->subDays(3)])->saveQuietly();

    $newer = Booking::factory()->create([
        'user_id' => $guest->id,
        'table_id' => $table->id,
        'payment_status' => Booking::PAYMENT_STATUS_PAID,
    ]);
    $newer->forceFill(['created_at' => now()->subDay()])->saveQuietly();

    Livewire::actingAs($this->admin)
        ->test(BookingsPage::class)
        ->assertViewHas('bookings', function ($bookings) use ($newer, $older): bool {
            $pageItems = collect($bookings->items());

            return $pageItems->first()->is($newer) && $pageItems->get(1)->is($older);
        });
});

test('admin bookings page always shows pagination summary in footer', function () {
    Livewire::actingAs($this->admin)
        ->test(BookingsPage::class)
        ->assertOk()
        ->assertSee('Tidak ada data.');

    $guest = User::factory()->create();
    $table = Table::factory()->create();
    Booking::factory()->create([
        'user_id' => $guest->id,
        'table_id' => $table->id,
        'payment_status' => Booking::PAYMENT_STATUS_PAID,
    ]);

    Livewire::actingAs($this->admin)
        ->test(BookingsPage::class)
        ->assertOk()
        ->assertSee('Menampilkan')
        ->assertSee('dari')
        ->assertSee('entri');
});

test('admin can create and delete a booking', function () {
    $guest = User::factory()->create();
    $table = Table::factory()->create(['capacity' => 4]);
    $menuItem = MenuItem::factory()->create(['status' => MenuItem::STATUS_AVAILABLE]);

    Livewire::actingAs($this->admin)
        ->test(BookingCreatePage::class)
        ->assertOk()
        ->set('user_id', $guest->id)
        ->set('table_id', $table->id)
        ->set('booking_date', '2026-06-20')
        ->set('booking_time', '19:30')
        ->set('guest_count', 3)
        ->set('booking_status', Booking::BOOKING_STATUS_CONFIRMED)
        ->set('payment_status', Booking::PAYMENT_STATUS_PENDING)
        ->set('note', 'Minta meja dekat jendela.')
        ->set('cancellation_reason', '')
        ->set('cartItems', [['id' => $menuItem->id, 'quantity' => 2]])
        ->call('save')
        ->assertHasNoErrors();

    $booking = Booking::query()->where('user_id', $guest->id)->first();
    expect($booking)->not->toBeNull()
        ->and($booking->guest_count)->toBe(3)
        ->and($booking->type)->toBe(Booking::TYPE_MANUAL)
        ->and($booking->items)->not->toBeEmpty();

    Livewire::actingAs($this->admin)
        ->test(BookingsPage::class)
        ->call('delete', $booking->id)
        ->assertHasNoErrors();

    expect(Booking::query()->find($booking->id))->toBeNull();
});

test('admin can create update and delete a table when no bookings', function () {
    Livewire::actingAs($this->admin)
        ->test(TableManagementPage::class)
        ->assertOk()
        ->call('openCreate')
        ->set('table_number', 'A1')
        ->set('capacity', 4)
        ->set('location_description', 'Area utama dekat jendela.')
        ->set('status', Table::STATUS_AVAILABLE)
        ->call('save')
        ->assertHasNoErrors();

    $table = Table::query()->where('table_number', 'A1')->first();
    expect($table)->not->toBeNull();

    Livewire::actingAs($this->admin)
        ->test(TableManagementPage::class)
        ->call('openEdit', $table->id)
        ->set('table_number', 'A1')
        ->set('capacity', 6)
        ->call('save')
        ->assertHasNoErrors();

    expect(Table::query()->find($table->id)?->capacity)->toBe(6);

    Livewire::actingAs($this->admin)
        ->test(TableManagementPage::class)
        ->call('delete', $table->id)
        ->assertHasNoErrors();

    expect(Table::query()->find($table->id))->toBeNull();
});

test('admin can set maintenance table to available', function () {
    $table = Table::factory()->create(['status' => Table::STATUS_MAINTENANCE]);

    Livewire::actingAs($this->admin)
        ->test(TableManagementPage::class)
        ->call('setAvailable', $table->id)
        ->assertHasNoErrors();

    expect(Table::query()->find($table->id)?->status)->toBe(Table::STATUS_AVAILABLE);
});

test('admin cannot delete table that has bookings', function () {
    $table = Table::factory()->create();
    Booking::factory()->create(['table_id' => $table->id]);

    Livewire::actingAs($this->admin)
        ->test(TableManagementPage::class)
        ->call('delete', $table->id)
        ->assertHasErrors(['delete']);

    expect(Table::query()->find($table->id))->not->toBeNull();
});

test('admin customers page shows pagination summary in footer', function () {
    Livewire::actingAs($this->admin)
        ->test(CustomersPage::class)
        ->assertOk()
        ->assertSee('Tidak ada data.');

    User::factory()->create([
        'name' => 'Satu Customer',
        'role' => User::ROLE_CUSTOMER,
    ]);

    Livewire::actingAs($this->admin)
        ->test(CustomersPage::class)
        ->assertOk()
        ->assertSee('Menampilkan')
        ->assertSee('dari')
        ->assertSee('entri');
});

test('admin staff page shows pagination summary in footer', function () {
    Livewire::actingAs($this->admin)
        ->test(StaffPage::class)
        ->assertOk()
        ->assertSee('Menampilkan')
        ->assertSee('dari')
        ->assertSee('entri');
});

test('customers page lists customers and booking counts', function () {
    $alice = User::factory()->create([
        'name' => 'Alice Pelanggan',
        'role' => User::ROLE_CUSTOMER,
    ]);
    User::factory()->create([
        'name' => 'Bob Pelanggan',
        'role' => User::ROLE_CUSTOMER,
    ]);
    Booking::factory()->create(['user_id' => $alice->id]);
    Booking::factory()->create(['user_id' => $alice->id]);

    Livewire::actingAs($this->admin)
        ->test(CustomersPage::class)
        ->assertOk()
        ->assertSee('Alice Pelanggan')
        ->assertSee('Bob Pelanggan');

    expect(User::query()->where('name', 'Alice Pelanggan')->first()?->bookings()->count())->toBe(2);
});

test('admin can create update and delete staff', function () {
    Livewire::actingAs($this->admin)
        ->test(StaffPage::class)
        ->assertOk()
        ->call('openCreate')
        ->set('name', 'Staff Baru')
        ->set('email', 'staffbaru@example.test')
        ->set('password', 'Password123!')
        ->set('password_confirmation', 'Password123!')
        ->call('save')
        ->assertHasNoErrors();

    $created = User::query()->where('email', 'staffbaru@example.test')->first();
    expect($created)->not->toBeNull()
        ->and($created->role)->toBe(User::ROLE_ADMIN);

    Livewire::actingAs($this->admin)
        ->test(StaffPage::class)
        ->call('openEdit', $created->id)
        ->set('name', 'Staff Diperbarui')
        ->set('email', 'staffbaru@example.test')
        ->set('password', '')
        ->set('password_confirmation', '')
        ->call('save')
        ->assertHasNoErrors();

    expect(User::query()->find($created->id)?->name)->toBe('Staff Diperbarui');

    Livewire::actingAs($this->admin)
        ->test(StaffPage::class)
        ->call('delete', $created->id)
        ->assertHasNoErrors();

    expect(User::query()->find($created->id))->toBeNull();
});

test('admin cannot delete the only staff account', function () {
    $sole = User::factory()->admin()->create();

    Livewire::actingAs($sole)
        ->test(StaffPage::class)
        ->call('delete', $sole->id)
        ->assertHasErrors(['delete']);

    expect(User::query()->find($sole->id))->not->toBeNull();
});

test('transactions page lists transactions', function () {
    $guest = User::factory()->create();
    $table = Table::factory()->create();
    $booking = Booking::factory()->create([
        'user_id' => $guest->id,
        'table_id' => $table->id,
        'payment_status' => Booking::PAYMENT_STATUS_PAID,
    ]);
    Transaction::query()->create([
        'booking_id' => $booking->id,
        'midtrans_transaction_id' => 'TEST-ORDER-'.uniqid(),
        'amount' => 150000,
        'status' => Transaction::STATUS_SUCCESS,
        'payment_method' => 'qris',
        'paid_at' => now(),
    ]);

    Livewire::actingAs($this->admin)
        ->test(TransactionsPage::class)
        ->assertOk()
        ->assertSee('Riwayat transaksi')
        ->assertSee('qris')
        ->assertSee('Menampilkan')
        ->assertSee('dari')
        ->assertSee('entri');
});
