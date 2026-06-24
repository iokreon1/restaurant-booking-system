<?php

namespace App\Http\Controllers\Webhook;

use App\External\MidtransService;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Transaction;
use App\Notifications\BookingPaymentFailedNotification;
use App\Notifications\BookingPaymentSuccessNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class MidtransWebhook extends Controller
{
    public function __construct(
        private readonly MidtransService $midtransService
    ) {}

    /**
     * Handle Midtrans webhook notifications.
     */
    public function handleMidtransWebhook(Request $request): JsonResponse
    {
        $payload = $this->buildPayload($request);

        Log::info('Midtrans webhook received', [
            'payload' => $payload,
        ]);

        // Verify webhook signature
        if (! $this->midtransService->verifyWebhookSignature($payload)) {
            // Still return 200 to prevent Midtrans from retrying
            return response()->json([
                'message' => 'Webhook signature verification failed',
            ], 200);
        }

        $validator = Validator::make($payload, [
            'order_id' => ['required', 'string'],
            'transaction_status' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            Log::warning('Midtrans webhook missing required fields', [
                'payload' => $payload,
                'errors' => $validator->errors()->toArray(),
            ]);

            return response()->json([
                'message' => 'Missing required fields',
            ], 200);
        }

        $validatedPayload = $validator->validated();
        $orderId = $validatedPayload['order_id'];
        $transactionStatus = $validatedPayload['transaction_status'];

        $booking = Booking::query()
            ->with('transaction')
            ->where('booking_reference', $orderId)
            ->first();

        if (empty($booking)) {
            Log::warning('Booking not found for Midtrans webhook', [
                'order_id' => $orderId,
                'payload' => $payload,
            ]);

            return response()->json([
                'message' => 'Order not found',
            ], 200);
        }

        $transaction = $booking->transaction;

        if ($transaction === null) {
            Log::warning('Transaction not found for Midtrans webhook', [
                'booking_id' => $booking->id,
                'order_id' => $orderId,
                'payload' => $payload,
            ]);

            return response()->json([
                'message' => 'Transaction not found',
            ], 200);
        }

        $fraudStatus = $payload['fraud_status'] ?? null;
        $statusPayload = $this->mapStatusPayload(
            $transactionStatus,
            is_string($fraudStatus) && $fraudStatus !== '' ? $fraudStatus : null,
        );
        $previousPaymentStatus = $booking->payment_status;

        DB::transaction(function () use ($booking, $payload, $transaction, $statusPayload, $orderId): void {
            $midtransTransactionId = $payload['transaction_id'] ?? null;
            $paymentMethod = $payload['payment_type'] ?? null;

            $transactionUpdate = [
                'midtrans_transaction_id' => (is_string($midtransTransactionId) && $midtransTransactionId !== '')
                    ? $midtransTransactionId
                    : ($transaction->midtrans_transaction_id ?? $orderId),
                'payment_method' => is_string($paymentMethod) && $paymentMethod !== '' ? $paymentMethod : null,
                'payment_channel' => $this->resolvePaymentChannel($payload),
                'status' => $statusPayload['transaction_status'],
                'raw_response' => $payload,
                'webhook_received_at' => now(),
            ];

            if ($statusPayload['transaction_status'] === Transaction::STATUS_SUCCESS) {
                $transactionUpdate['paid_at'] = $transaction->paid_at ?? now();
                $transactionUpdate['expired_at'] = null;
            }

            if ($statusPayload['transaction_status'] === Transaction::STATUS_EXPIRED) {
                $transactionUpdate['expired_at'] = $transaction->expired_at ?? now();
            }

            $transaction->update($transactionUpdate);

            $booking->update([
                'payment_status' => $statusPayload['payment_status'],
            ]);
        });

        $booking->refresh();

        // Send notification if payment status is changed
        if ($previousPaymentStatus !== $booking->payment_status) {
            if ($booking->payment_status === Booking::PAYMENT_STATUS_PAID) {
                Notification::send($booking->user, new BookingPaymentSuccessNotification($booking));
            }

            if (in_array($booking->payment_status, [Booking::PAYMENT_STATUS_EXPIRED, Booking::PAYMENT_STATUS_REFUNDED], true)) {
                Notification::send($booking->user, new BookingPaymentFailedNotification($booking));
            }
        }

        Log::info('Midtrans webhook processed', [
            'booking_id' => $booking->id,
            'order_id' => $booking->booking_reference,
            'transaction_status' => $transactionStatus,
        ]);

        return response()->json([
            'message' => 'Webhook processed',
        ], 200);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayload(Request $request): array
    {
        $payload = $request->json()->all();

        if ($payload !== []) {
            return $payload;
        }

        return $request->all();
    }

    /**
     * @return array{payment_status: string, transaction_status: string}
     */
    private function mapStatusPayload(string $transactionStatus, ?string $fraudStatus): array
    {
        if ($transactionStatus === 'capture' && $fraudStatus === 'challenge') {
            return [
                'payment_status' => Booking::PAYMENT_STATUS_PENDING,
                'transaction_status' => Transaction::STATUS_PENDING,
            ];
        }

        return match ($transactionStatus) {
            'capture', 'settlement' => [
                'payment_status' => Booking::PAYMENT_STATUS_PAID,
                'transaction_status' => Transaction::STATUS_SUCCESS,
            ],
            'expire' => [
                'payment_status' => Booking::PAYMENT_STATUS_EXPIRED,
                'transaction_status' => Transaction::STATUS_EXPIRED,
            ],
            'refund', 'partial_refund', 'chargeback', 'partial_chargeback' => [
                'payment_status' => Booking::PAYMENT_STATUS_REFUNDED,
                'transaction_status' => Transaction::STATUS_FAILED,
            ],
            'cancel', 'deny', 'failure' => [
                'payment_status' => Booking::PAYMENT_STATUS_PENDING,
                'transaction_status' => Transaction::STATUS_FAILED,
            ],
            default => [
                'payment_status' => Booking::PAYMENT_STATUS_PENDING,
                'transaction_status' => Transaction::STATUS_PENDING,
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolvePaymentChannel(array $data): ?string
    {
        $candidates = [
            data_get($data, 'va_numbers.0.bank'),
            data_get($data, 'permata_va_number'),
            data_get($data, 'store'),
            data_get($data, 'bank'),
            data_get($data, 'payment_type'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }
}
