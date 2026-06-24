<?php

use App\Models\Booking;
use App\Models\Table;
use App\Models\User;
use App\Services\MessageService;

it('composes booking_created message with placeholders replaced', function () {
    $user = User::factory()->create(['name' => 'John Doe']);
    $table = Table::factory()->create(['table_number' => 5]);
    $booking = Booking::factory()->create([
        'user_id' => $user->id,
        'table_id' => $table->id,
        'booking_reference' => 'BK-TEST999',
        'items' => [
            ['name' => 'Nasi Goreng', 'quantity' => 2, 'subtotal' => 50000],
            ['name' => 'Es Teh', 'quantity' => 1, 'subtotal' => 10000],
        ],
        'guest_count' => 3,
        'total_amount' => 250000,
    ]);

    $service = new MessageService;
    $result = $service->compose('booking_created', $booking, [
        'payment_url' => 'https://pay.test/invoice-123',
    ]);

    expect($result)
        ->toContain('John Doe')
        ->toContain('BK-TEST999')
        ->toContain('3 Orang')
        ->toContain('Nasi Goreng x2')
        ->toContain('Es Teh x1')
        ->toContain('https://pay.test/invoice-123')
        ->toContain('250.000')
        ->not->toContain('{customer_name}')
        ->not->toContain('{booking_reference}')
        ->not->toContain('{ordered_menu}')
        ->not->toContain('{payment_url}');
});

it('composes booking_cancelled message with cancellation reason', function () {
    $booking = Booking::factory()->create([
        'cancellation_reason' => 'Perubahan jadwal',
    ]);

    $service = new MessageService;
    $result = $service->compose('booking_cancelled', $booking);

    expect($result)
        ->toContain('dibatalkan')
        ->toContain('Perubahan jadwal');
});

it('composes message with extra data overriding defaults', function () {
    $booking = Booking::factory()->create();

    $service = new MessageService;
    $result = $service->compose('booking_created', $booking, [
        'customer_name' => 'Override Name',
    ]);

    expect($result)->toContain('Override Name');
});

it('composes booking_confirmed message with ordered menu', function () {
    $booking = Booking::factory()->create([
        'items' => [
            ['name' => 'Ayam Bakar', 'quantity' => 1, 'subtotal' => 35000],
            ['name' => 'Jus Alpukat', 'quantity' => 2, 'subtotal' => 30000],
        ],
    ]);

    $service = new MessageService;
    $result = $service->compose('booking_confirmed', $booking);

    expect($result)
        ->toContain('Menu yang dipesan')
        ->toContain('Ayam Bakar x1')
        ->toContain('Jus Alpukat x2')
        ->not->toContain('{ordered_menu}');
});

it('throws exception for unknown event', function () {
    $booking = Booking::factory()->create();

    $service = new MessageService;
    $service->compose('nonexistent_event', $booking);
})->throws(InvalidArgumentException::class, 'Message template for event [nonexistent_event] not found.');

it('lists all available events', function () {
    $service = new MessageService;

    expect($service->availableEvents())->toBe([
        'booking_created',
        'booking_payment_success',
        'booking_payment_failed',
        'booking_confirmed',
        'booking_cancelled',
    ]);
});
