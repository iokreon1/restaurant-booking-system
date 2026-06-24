<?php

use App\External\KirimiService;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingCreatedNotification;
use App\Notifications\Channels\KirimiChannel;
use App\Notifications\Messages\KirimiMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

it('routes via kirimi channel', function () {
    $booking = Booking::factory()->create();

    $notification = new BookingCreatedNotification($booking);

    expect($notification->via($booking->user))->toBe([KirimiChannel::class]);
});

it('builds kirimi message with booking details and payment url', function () {
    $booking = Booking::factory()->create([
        'booking_reference' => 'BK-TEST123',
        'guest_count' => 4,
        'total_amount' => 500000,
    ]);

    $paymentUrl = 'https://app.midtrans.com/snap/v2/vtweb/test-token';
    $notification = new BookingCreatedNotification($booking, $paymentUrl);
    $message = $notification->toKirimi($booking->user);

    expect($message)
        ->toBeInstanceOf(KirimiMessage::class)
        ->and($message->content)
        ->toContain('BK-TEST123')
        ->toContain('4')
        ->toContain('500.000')
        ->toContain('Dapur Nabilah')
        ->toContain($paymentUrl);
});

it('sends whatsapp message via kirimi channel', function () {
    Http::fake([
        'api.kirimi.id/v1/send-message' => Http::response([
            'success' => true,
            'message' => 'Pesan berhasil dikirim',
        ]),
    ]);

    config([
        'services.kirimi.user_code' => 'test-user-code',
        'services.kirimi.device_id' => 'test-device-id',
        'services.kirimi.secret' => 'test-secret',
    ]);

    $user = User::factory()->create();
    $booking = Booking::factory()->create(['user_id' => $user->id]);

    $channel = new KirimiChannel(new KirimiService);
    $notification = new BookingCreatedNotification($booking);

    $message = $notification->toKirimi($user);

    // Simulate sending via on-demand notification with a phone number
    $result = app(KirimiService::class)->sendMessage('6281234567890', $message->content);

    expect($result)->toHaveKey('success', true);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.kirimi.id/v1/send-message'
            && str_contains($request['message'], 'BK-');
    });
});

it('returns null when notifiable has no phone number', function () {
    $user = User::factory()->create(['phone_number' => null]);

    $channel = new KirimiChannel(new KirimiService);
    $notification = new BookingCreatedNotification(
        Booking::factory()->create(['user_id' => $user->id]),
    );

    $result = $channel->send($user, $notification);

    expect($result)->toBeNull();
});
