<?php

use App\External\MidtransService;
use App\Http\Controllers\Webhook\MidtransWebhook;
use App\Models\Booking;
use App\Models\Transaction;
use App\Notifications\BookingPaymentFailedNotification;
use App\Notifications\BookingPaymentSuccessNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

it('marks booking as paid when settlement webhook is received', function () {
    Notification::fake();

    $booking = Booking::factory()->create([
        'payment_status' => Booking::PAYMENT_STATUS_PENDING,
    ]);

    $transaction = $booking->transaction()->create([
        'midtrans_transaction_id' => $booking->booking_reference,
        'snap_token' => 'snap-token-123',
        'amount' => (float) $booking->total_amount,
        'status' => Transaction::STATUS_PENDING,
    ]);

    mockMidtransWebhookVerification($booking->booking_reference);

    $response = app(MidtransWebhook::class)->handleMidtransWebhook(
        Request::create('/webhook/midtrans', 'POST', webhookPayload($booking->booking_reference, [
            'transaction_status' => 'settlement',
            'transaction_id' => 'midtrans-trx-123',
            'payment_type' => 'bank_transfer',
            'va_numbers' => [
                ['bank' => 'bca'],
            ],
        ]))
    );

    $booking->refresh();
    $transaction->refresh();

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getData(true)['message'])->toBe('Webhook processed')
        ->and($booking->payment_status)->toBe(Booking::PAYMENT_STATUS_PAID)
        ->and($transaction->status)->toBe(Transaction::STATUS_SUCCESS)
        ->and($transaction->midtrans_transaction_id)->toBe('midtrans-trx-123')
        ->and($transaction->payment_method)->toBe('bank_transfer')
        ->and($transaction->payment_channel)->toBe('bca')
        ->and($transaction->paid_at)->not->toBeNull()
        ->and($transaction->webhook_received_at)->not->toBeNull()
        ->and($transaction->raw_response['order_id'])->toBe($booking->booking_reference);

    Notification::assertSentTo($booking->user, BookingPaymentSuccessNotification::class);
});

it('marks booking and transaction as expired when expire webhook is received', function () {
    Notification::fake();

    $booking = Booking::factory()->create([
        'payment_status' => Booking::PAYMENT_STATUS_PENDING,
    ]);

    $transaction = $booking->transaction()->create([
        'midtrans_transaction_id' => $booking->booking_reference,
        'amount' => (float) $booking->total_amount,
        'status' => Transaction::STATUS_PENDING,
    ]);

    mockMidtransWebhookVerification($booking->booking_reference);

    $response = app(MidtransWebhook::class)->handleMidtransWebhook(
        Request::create('/webhook/midtrans', 'POST', webhookPayload($booking->booking_reference, [
            'transaction_status' => 'expire',
            'payment_type' => 'bank_transfer',
            'va_numbers' => [
                ['bank' => 'mandiri'],
            ],
        ]))
    );

    $booking->refresh();
    $transaction->refresh();

    expect($response->getStatusCode())->toBe(200)
        ->and($booking->payment_status)->toBe(Booking::PAYMENT_STATUS_EXPIRED)
        ->and($transaction->status)->toBe(Transaction::STATUS_EXPIRED)
        ->and($transaction->payment_channel)->toBe('mandiri')
        ->and($transaction->expired_at)->not->toBeNull()
        ->and($transaction->webhook_received_at)->not->toBeNull();

    Notification::assertSentTo($booking->user, BookingPaymentFailedNotification::class);
});

it('keeps booking pending when capture webhook is challenged by fraud status', function () {
    $booking = Booking::factory()->create([
        'payment_status' => Booking::PAYMENT_STATUS_PENDING,
    ]);

    $transaction = $booking->transaction()->create([
        'midtrans_transaction_id' => $booking->booking_reference,
        'amount' => (float) $booking->total_amount,
        'status' => Transaction::STATUS_PENDING,
    ]);

    mockMidtransWebhookVerification($booking->booking_reference);

    $response = app(MidtransWebhook::class)->handleMidtransWebhook(
        Request::create('/webhook/midtrans', 'POST', webhookPayload($booking->booking_reference, [
            'transaction_status' => 'capture',
            'fraud_status' => 'challenge',
            'payment_type' => 'credit_card',
            'bank' => 'bni',
        ]))
    );

    $booking->refresh();
    $transaction->refresh();

    expect($response->getStatusCode())->toBe(200)
        ->and($booking->payment_status)->toBe(Booking::PAYMENT_STATUS_PENDING)
        ->and($transaction->status)->toBe(Transaction::STATUS_PENDING)
        ->and($transaction->payment_method)->toBe('credit_card')
        ->and($transaction->payment_channel)->toBe('bni')
        ->and($transaction->paid_at)->toBeNull();
});

it('does not create a transaction when webhook is received for a booking without transaction', function () {
    $booking = Booking::factory()->create([
        'payment_status' => Booking::PAYMENT_STATUS_PENDING,
    ]);

    mockMidtransWebhookVerification($booking->booking_reference);

    $response = app(MidtransWebhook::class)->handleMidtransWebhook(
        Request::create('/webhook/midtrans', 'POST', webhookPayload($booking->booking_reference, [
            'transaction_status' => 'settlement',
            'transaction_id' => 'midtrans-trx-missing',
            'payment_type' => 'bank_transfer',
        ]))
    );

    $booking->refresh();

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getData(true)['message'])->toBe('Transaction not found')
        ->and($booking->payment_status)->toBe(Booking::PAYMENT_STATUS_PENDING)
        ->and($booking->transaction()->exists())->toBeFalse();
});

function mockMidtransWebhookVerification(string $bookingReference): void
{
    $midtransService = Mockery::mock(MidtransService::class);
    $midtransService
        ->shouldReceive('verifyWebhookSignature')
        ->once()
        ->withArgs(fn (array $data): bool => ($data['order_id'] ?? null) === $bookingReference)
        ->andReturn(true);

    app()->instance(MidtransService::class, $midtransService);
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function webhookPayload(string $bookingReference, array $overrides = []): array
{
    return array_merge([
        'order_id' => $bookingReference,
        'transaction_status' => 'pending',
        'fraud_status' => 'accept',
        'gross_amount' => '250000.00',
    ], $overrides);
}
