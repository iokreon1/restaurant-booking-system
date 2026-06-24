<?php

use App\External\MidtransService;
use App\Models\Booking;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Table;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BookingService;

it('creates booking and transaction from reservation and cart items', function () {
    $table = Table::query()->create([
        'table_number' => 'A-01',
        'capacity' => 4,
        'location_description' => 'Indoor - Window seat',
        'status' => Table::STATUS_AVAILABLE,
    ]);

    $category = MenuCategory::query()->create([
        'name' => 'Main Course',
        'thumbnail_path' => 'images/default.png',
        'sort_order' => 1,
        'status' => MenuCategory::STATUS_ACTIVE,
    ]);

    $firstItem = MenuItem::query()->create([
        'category_id' => $category->id,
        'name' => 'Mushroom Risotto',
        'description' => 'Creamy risotto',
        'price' => 145000,
        'thumbnail_path' => 'images/default.png',
        'status' => MenuItem::STATUS_AVAILABLE,
        'sort_order' => 1,
    ]);

    $secondItem = MenuItem::query()->create([
        'category_id' => $category->id,
        'name' => 'Garden Salad',
        'description' => 'Fresh salad',
        'price' => 85000,
        'thumbnail_path' => 'images/default.png',
        'status' => MenuItem::STATUS_AVAILABLE,
        'sort_order' => 2,
    ]);

    $midtransService = Mockery::mock(MidtransService::class);
    $midtransService
        ->shouldReceive('createSnapTransactionForBooking')
        ->once()
        ->andReturn([
            'token' => 'snap-token-123',
            'redirect_url' => 'https://pay.example.com/booking/BK-123',
        ]);

    $this->app->instance(MidtransService::class, $midtransService);

    $checkout = app(BookingService::class)->createBooking(
        [
            'customer_name' => 'Budi Santoso',
            'customer_email' => 'budi@example.com',
            'customer_phone' => '081234567890',
            'reservation_date' => '2026-04-05',
            'reservation_time' => '18:00',
            'party_size' => 4,
            'selected_table_id' => $table->id,
        ],
        [
            [
                'id' => $firstItem->id,
                'quantity' => 2,
                'unitPrice' => 1000,
            ],
            [
                'id' => $secondItem->id,
                'quantity' => 1,
                'unitPrice' => 500,
            ],
        ],
    );

    $booking = $checkout['booking'];
    $transaction = $booking->transaction;

    expect($checkout['snap_token'])->toBe('snap-token-123')
        ->and($checkout['redirect_url'])->toBe('https://pay.example.com/booking/BK-123')
        ->and($booking)
        ->toBeInstanceOf(Booking::class)
        ->and($transaction)->toBeInstanceOf(Transaction::class);

    $this->assertModelExists($booking);
    $this->assertModelExists($transaction);

    $user = User::query()->where('email', 'budi@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($booking->booking_reference)->toStartWith('BK-')
        ->and($booking->booking_reference)->toMatch('/^BK-[A-Z0-9]{8}$/')
        ->and($booking->user_id)->toBe($user->id)
        ->and($booking->type)->toBe(Booking::TYPE_MICROSITE)
        ->and($booking->booking_status)->toBe(Booking::BOOKING_STATUS_PENDING)
        ->and($booking->payment_status)->toBe(Booking::PAYMENT_STATUS_PENDING)
        ->and((float) $booking->total_amount)->toBe(375000.0)
        ->and($transaction->midtrans_transaction_id)->toBe($booking->booking_reference)
        ->and($transaction->snap_token)->toBe('snap-token-123')
        ->and($transaction->status)->toBe(Transaction::STATUS_PENDING)
        ->and((float) $transaction->amount)->toBe(375000.0)
        ->and($booking->items)->toBe([
            [
                'menu_item_id' => $firstItem->id,
                'name' => 'Mushroom Risotto',
                'unit_price' => 145000,
                'quantity' => 2,
                'subtotal' => 290000,
            ],
            [
                'menu_item_id' => $secondItem->id,
                'name' => 'Garden Salad',
                'unit_price' => 85000,
                'quantity' => 1,
                'subtotal' => 85000,
            ],
        ]);
});

it('reuses existing user with the same email when creating booking', function () {
    $user = User::factory()->create([
        'name' => 'Existing User',
        'email' => 'existing@example.com',
    ]);

    $table = Table::query()->create([
        'table_number' => 'A-02',
        'capacity' => 2,
        'location_description' => 'Indoor - Corner',
        'status' => Table::STATUS_AVAILABLE,
    ]);

    $category = MenuCategory::query()->create([
        'name' => 'Drink',
        'thumbnail_path' => 'images/default.png',
        'sort_order' => 1,
        'status' => MenuCategory::STATUS_ACTIVE,
    ]);

    $item = MenuItem::query()->create([
        'category_id' => $category->id,
        'name' => 'Espresso',
        'description' => 'Hot coffee',
        'price' => 30000,
        'thumbnail_path' => 'images/default.png',
        'status' => MenuItem::STATUS_AVAILABLE,
        'sort_order' => 1,
    ]);

    $midtransService = Mockery::mock(MidtransService::class);
    $midtransService
        ->shouldReceive('createSnapTransactionForBooking')
        ->once()
        ->andReturn([
            'token' => 'snap-token-existing',
            'redirect_url' => 'https://pay.example.com/booking/existing',
        ]);

    $this->app->instance(MidtransService::class, $midtransService);

    $checkout = app(BookingService::class)->createBooking(
        [
            'customer_name' => 'Updated Name',
            'customer_email' => 'existing@example.com',
            'customer_phone' => '081111111111',
            'reservation_date' => '2026-04-06',
            'reservation_time' => '19:00',
            'party_size' => 2,
            'selected_table_id' => $table->id,
        ],
        [
            [
                'id' => $item->id,
                'quantity' => 1,
            ],
        ],
    );

    $booking = $checkout['booking'];

    expect(User::query()->count())->toBe(1)
        ->and($booking->user_id)->toBe($user->id)
        ->and($booking->type)->toBe(Booking::TYPE_MICROSITE);
});

it('creates manual booking with a transaction record', function () {
    $guest = User::factory()->create();

    $table = Table::query()->create([
        'table_number' => 'A-03',
        'capacity' => 6,
        'location_description' => 'Outdoor',
        'status' => Table::STATUS_AVAILABLE,
    ]);

    $category = MenuCategory::query()->create([
        'name' => 'Dessert',
        'thumbnail_path' => 'images/default.png',
        'sort_order' => 1,
        'status' => MenuCategory::STATUS_ACTIVE,
    ]);

    $item = MenuItem::query()->create([
        'category_id' => $category->id,
        'name' => 'Ice Cream',
        'description' => 'Vanilla',
        'price' => 45000,
        'thumbnail_path' => 'images/default.png',
        'status' => MenuItem::STATUS_AVAILABLE,
        'sort_order' => 1,
    ]);

    $booking = app(BookingService::class)->createManualBooking(
        [
            'user_id' => $guest->id,
            'table_id' => $table->id,
            'booking_date' => '2026-05-01',
            'booking_time' => '20:00',
            'guest_count' => 2,
            'booking_status' => Booking::BOOKING_STATUS_CONFIRMED,
            'payment_status' => Booking::PAYMENT_STATUS_PAID,
            'note' => 'Tanpa cabai.',
            'cancellation_reason' => null,
        ],
        [
            ['id' => $item->id, 'quantity' => 2],
        ],
    );

    $line = $booking->items[0];
    $transaction = $booking->transaction;

    expect($booking->type)->toBe(Booking::TYPE_MANUAL)
        ->and($transaction)->toBeInstanceOf(Transaction::class)
        ->and((float) $transaction->amount)->toBe(90000.0)
        ->and($transaction->status)->toBe(Transaction::STATUS_SUCCESS)
        ->and($transaction->payment_method)->toBe('manual')
        ->and((float) $booking->total_amount)->toBe(90000.0)
        ->and($line['menu_item_id'])->toBe($item->id)
        ->and($line['name'])->toBe('Ice Cream')
        ->and($line['quantity'])->toBe(2)
        ->and((float) $line['unit_price'])->toBe(45000.0)
        ->and((float) $line['subtotal'])->toBe(90000.0);
});

it('updates only booking items and total via updateBookingItems', function () {
    $guest = User::factory()->create();
    $table = Table::factory()->create();

    $category = MenuCategory::query()->create([
        'name' => 'Snack',
        'thumbnail_path' => 'images/default.png',
        'sort_order' => 1,
        'status' => MenuCategory::STATUS_ACTIVE,
    ]);

    $itemA = MenuItem::query()->create([
        'category_id' => $category->id,
        'name' => 'Keripik',
        'description' => 'Gurih',
        'price' => 10000,
        'thumbnail_path' => 'images/default.png',
        'status' => MenuItem::STATUS_AVAILABLE,
        'sort_order' => 1,
    ]);

    $itemB = MenuItem::query()->create([
        'category_id' => $category->id,
        'name' => 'Kacang',
        'description' => 'Renyah',
        'price' => 15000,
        'thumbnail_path' => 'images/default.png',
        'status' => MenuItem::STATUS_AVAILABLE,
        'sort_order' => 2,
    ]);

    $booking = Booking::query()->create([
        'booking_reference' => 'BK-EDIT-TEST',
        'type' => Booking::TYPE_MANUAL,
        'user_id' => $guest->id,
        'table_id' => $table->id,
        'items' => [
            [
                'menu_item_id' => $itemA->id,
                'name' => $itemA->name,
                'unit_price' => 10000,
                'quantity' => 1,
                'subtotal' => 10000,
            ],
        ],
        'total_amount' => 10000,
        'booking_date' => '2026-07-01',
        'booking_time' => '12:00',
        'guest_count' => 2,
        'booking_status' => Booking::BOOKING_STATUS_PENDING,
        'payment_status' => Booking::PAYMENT_STATUS_PENDING,
        'note' => 'Jangan pedas',
        'cancellation_reason' => null,
    ]);

    $txn = $booking->transaction()->create([
        'amount' => 10000,
        'status' => Transaction::STATUS_PENDING,
        'payment_method' => 'manual',
    ]);

    $updated = app(BookingService::class)->updateBookingItems($booking, [
        ['id' => $itemB->id, 'quantity' => 2],
    ]);

    $updated->refresh();
    $txn->refresh();

    expect((float) $updated->total_amount)->toBe(30000.0)
        ->and($updated->guest_count)->toBe(2)
        ->and($updated->note)->toBe('Jangan pedas')
        ->and(count($updated->items))->toBe(1)
        ->and($updated->items[0]['menu_item_id'])->toBe($itemB->id)
        ->and($updated->items[0]['quantity'])->toBe(2)
        ->and((float) $txn->amount)->toBe(30000.0);
});
