<?php

use App\Livewire\Admin\Booking\BookingShowPage;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingCancelledNotification;
use App\Notifications\BookingConfirmedNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('bookings list includes link to booking show', function () {
    $user = User::factory()->create();
    $booking = Booking::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.bookings'))
        ->assertOk()
        ->assertSee(route('admin.bookings.show', $booking), false);
});

test('guests are redirected to login when visiting booking show', function () {
    $booking = Booking::factory()->create();

    $this->get(route('admin.bookings.show', $booking))
        ->assertRedirect(route('login'));
});

test('authenticated users can view booking detail', function () {
    $user = User::factory()->create();
    $booking = Booking::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.bookings.show', $booking))
        ->assertOk()
        ->assertSee($booking->booking_reference, false)
        ->assertSee($booking->user->email, false);
});

test('authenticated users can see contextual booking status actions on detail page', function () {
    $user = User::factory()->create();
    $booking = Booking::factory()->create([
        'booking_status' => Booking::BOOKING_STATUS_PENDING,
        'payment_status' => Booking::PAYMENT_STATUS_PENDING,
    ]);

    $this->actingAs($user)
        ->get(route('admin.bookings.show', $booking))
        ->assertOk()
        ->assertSee('Konfirmasi')
        ->assertSee('Tolak')
        ->assertDontSee('Selesaikan');
});

test('authenticated users can update booking status using allowed sequential action', function () {
    Notification::fake();

    $user = User::factory()->create();
    $booking = Booking::factory()->create([
        'booking_status' => Booking::BOOKING_STATUS_PENDING,
        'payment_status' => Booking::PAYMENT_STATUS_PENDING,
    ]);

    $successJs = 'window.showDashboardAlert('.json_encode([
        'variant' => 'success',
        'title' => 'Berhasil',
        'message' => 'Status pesanan telah diperbarui.',
    ], JSON_UNESCAPED_UNICODE).')';

    Livewire::actingAs($user)
        ->test(BookingShowPage::class, ['booking' => $booking])
        ->call('updateBookingStatus', Booking::BOOKING_STATUS_CONFIRMED)
        ->assertJs($successJs);

    $booking->refresh();

    expect($booking->booking_status)->toBe(Booking::BOOKING_STATUS_CONFIRMED);
    Notification::assertSentTo($booking->user, BookingConfirmedNotification::class);
});

test('authenticated users can cancel booking and send cancellation notification', function () {
    Notification::fake();

    $user = User::factory()->create();
    $booking = Booking::factory()->create([
        'booking_status' => Booking::BOOKING_STATUS_PENDING,
        'payment_status' => Booking::PAYMENT_STATUS_PENDING,
    ]);

    Livewire::actingAs($user)
        ->test(BookingShowPage::class, ['booking' => $booking])
        ->call('updateBookingStatus', Booking::BOOKING_STATUS_CANCELLED);

    $booking->refresh();

    expect($booking->booking_status)->toBe(Booking::BOOKING_STATUS_CANCELLED);
    Notification::assertSentTo($booking->user, BookingCancelledNotification::class);
});

test('authenticated users cannot skip booking status flow', function () {
    $user = User::factory()->create();
    $booking = Booking::factory()->create([
        'booking_status' => Booking::BOOKING_STATUS_PENDING,
        'payment_status' => Booking::PAYMENT_STATUS_PENDING,
    ]);

    $warningJs = 'window.showDashboardAlert('.json_encode([
        'variant' => 'warning',
        'title' => 'Tidak valid',
        'message' => 'Transisi status pesanan ini tidak tersedia.',
    ], JSON_UNESCAPED_UNICODE).')';

    Livewire::actingAs($user)
        ->test(BookingShowPage::class, ['booking' => $booking])
        ->call('updateBookingStatus', Booking::BOOKING_STATUS_COMPLETED)
        ->assertJs($warningJs);

    $booking->refresh();

    expect($booking->booking_status)->toBe(Booking::BOOKING_STATUS_PENDING);
});
