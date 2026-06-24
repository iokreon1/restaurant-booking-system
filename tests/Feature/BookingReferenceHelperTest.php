<?php

use App\Helper\BookingReferenceHelper;
use App\Models\Booking;
use App\Models\Table;
use App\Models\User;
use Illuminate\Support\Str;

it('generates a booking reference with the expected format', function () {
    $reference = app(BookingReferenceHelper::class)->generate();

    expect($reference)->toStartWith('BK-')
        ->and($reference)->toMatch('/^BK-[A-Z0-9]{8}$/');
});

it('retries when the generated booking reference already exists', function () {
    $user = User::factory()->create();

    $table = Table::query()->create([
        'table_number' => 'A-01',
        'capacity' => 4,
        'location_description' => 'Indoor - Window seat',
        'status' => Table::STATUS_AVAILABLE,
    ]);

    Booking::query()->create([
        'booking_reference' => 'BK-ABCDEFGH',
        'user_id' => $user->id,
        'table_id' => $table->id,
        'items' => [],
        'total_amount' => 100000,
        'booking_date' => '2026-04-05',
        'booking_time' => '18:00:00',
        'guest_count' => 2,
        'booking_status' => Booking::BOOKING_STATUS_PENDING,
        'payment_status' => Booking::PAYMENT_STATUS_PENDING,
    ]);

    Str::createRandomStringsUsingSequence(['ABCDEFGH', 'ZXCVBNML']);

    try {
        $reference = app(BookingReferenceHelper::class)->generate();
    } finally {
        Str::createRandomStringsUsing();
    }

    expect($reference)->toBe('BK-ZXCVBNML');
});
