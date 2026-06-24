<?php

use App\Helper\BookingStatusHelper;
use App\Models\Booking;

test('booking status helper returns expected label', function () {
    expect(BookingStatusHelper::label(Booking::BOOKING_STATUS_PENDING))->toBe('Menunggu')
        ->and(BookingStatusHelper::label(Booking::BOOKING_STATUS_CONFIRMED))->toBe('Dikonfirmasi');
});

test('booking status helper returns raw status for unknown key', function () {
    expect(BookingStatusHelper::label('custom_status'))->toBe('custom_status');
});

test('booking status helper returns badge classes for known status', function () {
    expect(BookingStatusHelper::badgeClassesFor(Booking::BOOKING_STATUS_CANCELLED))
        ->toContain('red-100')
        ->and(BookingStatusHelper::badgeClassesFor('unknown'))
        ->toBe(BookingStatusHelper::BADGE_FALLBACK_CLASSES);
});

test('booking status labels map contains all known keys', function () {
    expect(BookingStatusHelper::labels())->toHaveKeys([
        Booking::BOOKING_STATUS_PENDING,
        Booking::BOOKING_STATUS_NO_SHOW,
    ]);
});
