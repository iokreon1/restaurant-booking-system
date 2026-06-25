<?php

use App\Livewire\Admin\Booking\BookingEditPage;
use App\Models\Booking;
use App\Models\MenuItem;
use App\Models\Table;
use App\Models\User;
use Livewire\Livewire;

test('guests cannot access booking edit page', function () {
    $booking = Booking::factory()->create();

    $this->get(route('admin.bookings.edit', $booking))
        ->assertRedirect(route('login'));
});

test('authenticated user can open booking edit page', function () {
    $user = User::factory()->admin()->create();
    $menuItem = MenuItem::factory()->create(['status' => MenuItem::STATUS_AVAILABLE]);
    $booking = Booking::factory()->create([
        'items' => [
            [
                'menu_item_id' => $menuItem->id,
                'name' => $menuItem->name,
                'unit_price' => (float) $menuItem->price,
                'quantity' => 1,
                'subtotal' => (float) $menuItem->price,
            ],
        ],
        'total_amount' => $menuItem->price,
    ]);

    $this->actingAs($user)
        ->get(route('admin.bookings.edit', $booking))
        ->assertOk()
        ->assertSee('Ubah pesanan', false)
        ->assertSee($booking->booking_reference, false);
});

test('booking edit page saves items via BookingService', function () {
    $user = User::factory()->admin()->create();
    $table = Table::factory()->create();
    $menuItemA = MenuItem::factory()->create(['status' => MenuItem::STATUS_AVAILABLE, 'price' => 50000]);
    $menuItemB = MenuItem::factory()->create(['status' => MenuItem::STATUS_AVAILABLE, 'price' => 25000]);

    $booking = Booking::factory()->create([
        'table_id' => $table->id,
        'items' => [
            [
                'menu_item_id' => $menuItemA->id,
                'name' => $menuItemA->name,
                'unit_price' => 50000,
                'quantity' => 1,
                'subtotal' => 50000,
            ],
        ],
        'total_amount' => 50000,
    ]);

    Livewire::actingAs($user)
        ->test(BookingEditPage::class, ['booking' => $booking])
        ->set('cartItems', [
            ['id' => $menuItemB->id, 'quantity' => 2],
        ])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.bookings.show', $booking));

    $booking->refresh();

    expect((float) $booking->total_amount)->toBe(50000.0)
        ->and($booking->items[0]['menu_item_id'])->toBe($menuItemB->id)
        ->and($booking->items[0]['quantity'])->toBe(2);
});
