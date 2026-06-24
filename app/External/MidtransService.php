<?php

namespace App\External;

use App\Models\Booking;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService
{
    public function __construct()
    {

        Config::$serverKey = config('app.midtrans_server_key');
        Config::$clientKey = config('app.midtrans_client_key');
        Config::$isProduction = config('app.midtrans_is_production');
    }

    /**
     * Create Snap transaction and return token.
     *
     * @param  string|null  $finishRedirectUrl  Optional finish redirect URL after payment completion
     * @return array{token: string, redirect_url: string}
     */
    public function createSnapTransaction(Order $order, ?string $finishRedirectUrl = null): array
    {
        try {
            $user = $order->user;
            $itemDetails = $this->buildItemDetails($order);
            $grossAmount = (int) collect($itemDetails)->sum(fn (array $item): int => ((int) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 1)));

            $params = [
                'transaction_details' => [
                    'order_id' => $order->transaction_number,
                    'gross_amount' => $grossAmount,
                ],
                'customer_details' => [
                    'first_name' => explode(' ', $user->name)[0] ?? $user->name,
                    'last_name' => implode(' ', array_slice(explode(' ', $user->name), 1)) ?? '',
                    'email' => $user->email,
                    'phone' => $user->phone_number ?? '',
                ],
                'item_details' => $itemDetails,
                'expiry' => [
                    'start_time' => now()->format('Y-m-d H:i:s O'),
                    'unit' => 'hour',
                    'duration' => 24,
                ],
                'callbacks' => [
                    'finish' => $finishRedirectUrl ?? config('app.frontend_url').'/course',
                ],
            ];

            $snapToken = Snap::getSnapToken($params);
            $snapRedirectUrl = Snap::getSnapUrl($params);

            Log::info('Midtrans Snap transaction created', [
                'order_id' => $order->id,
                'transaction_number' => $order->transaction_number,
            ]);

            return [
                'token' => $snapToken,
                'redirect_url' => $snapRedirectUrl,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create Midtrans Snap transaction', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * @param  array{
     *     name: string,
     *     email: string,
     *     phone: string
     * }  $customer
     * @return array{token: string, redirect_url: string}
     */
    public function createSnapTransactionForBooking(Booking $booking, array $customer, ?string $finishRedirectUrl = null): array
    {
        try {
            $itemDetails = $this->formatBookingItems($booking->items ?? []);
            $grossAmount = (int) collect($itemDetails)->sum(
                fn (array $item): int => ((int) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 1)),
            );

            $nameParts = explode(' ', trim($customer['name']));

            $params = [
                'transaction_details' => [
                    'order_id' => $booking->booking_reference,
                    'gross_amount' => $grossAmount,
                ],
                'customer_details' => [
                    'first_name' => $nameParts[0] ?? $customer['name'],
                    'last_name' => implode(' ', array_slice($nameParts, 1)),
                    'email' => $customer['email'],
                    'phone' => $customer['phone'],
                ],
                'item_details' => $itemDetails,
                'expiry' => [
                    'start_time' => now()->format('Y-m-d H:i:s O'),
                    'unit' => 'hour',
                    'duration' => 24,
                ],
                'callbacks' => [
                    'finish' => $finishRedirectUrl ?? route('microsite.tracking'),
                ],
            ];

            $snapToken = Snap::getSnapToken($params);
            $snapRedirectUrl = Snap::getSnapUrl($params);

            Log::info('Midtrans Snap transaction created for booking', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
            ]);

            return [
                'token' => $snapToken,
                'redirect_url' => $snapRedirectUrl,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create Midtrans Snap transaction for booking', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get transaction status from Midtrans.
     */
    public function getTransactionStatus(string $orderId): array
    {
        try {
            $status = Transaction::status($orderId);

            Log::info('Midtrans transaction status retrieved', [
                'order_id' => $orderId,
                'transaction_status' => $status->transaction_status ?? null,
            ]);

            return (array) $status;
        } catch (\Exception $e) {
            Log::error('Failed to get Midtrans transaction status', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Verify webhook signature.
     *
     * Note: Midtrans doesn't use signature verification in the traditional sense.
     * Instead, we verify by checking the order_id and transaction_status
     * by calling the status API. This method validates the webhook data structure.
     */
    public function verifyWebhookSignature(array $data): bool
    {
        // Verify required fields exist
        $requiredFields = ['order_id', 'transaction_status', 'gross_amount'];
        foreach ($requiredFields as $field) {
            if (! isset($data[$field])) {
                Log::warning('Midtrans webhook missing required field', [
                    'field' => $field,
                    'data' => $data,
                ]);

                return false;
            }
        }

        // Verify transaction status by calling Midtrans API
        try {
            $status = $this->getTransactionStatus($data['order_id']);

            // Verify that the transaction status matches
            if (isset($status['transaction_status']) && isset($data['transaction_status'])) {
                return $status['transaction_status'] === $data['transaction_status'];
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to verify Midtrans webhook signature', [
                'order_id' => $data['order_id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Build item_details for Midtrans. Includes order items and shipping cost when applicable.
     * Sum of (price * quantity) for all items must equal gross_amount.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildItemDetails(Order $order): array
    {
        $items = $this->formatOrderItems($order->order_items ?? []);

        $shippingCost = (float) ($order->shipping_cost ?? 0);
        if ($shippingCost > 0) {
            $items[] = [
                'id' => 'shipping',
                'price' => (int) $shippingCost,
                'quantity' => 1,
                'name' => Str::limit($this->formatShippingItemName($order), 50, ''),
            ];
        }

        return $items;
    }

    /**
     * Format order items for Midtrans.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function formatOrderItems(array $items): array
    {
        return array_map(function ($item) {
            // Use final_price if available (price after discount), otherwise use price (original price)
            // This ensures Midtrans receives the correct price that matches total_amount
            $price = $item['final_price'] ?? $item['price'] ?? 0;

            return [
                'id' => $item['id'] ?? uniqid('item_'),
                'price' => (int) $price,
                'quantity' => (int) ($item['quantity'] ?? 1),
                'name' => Str::limit($item['name'] ?? 'Item', 50, ''),
                'membership_period' => $item['membership_period'] ?? null,
                'membership_type' => $item['membership_type'] ?? null,
            ];
        }, $items);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function formatBookingItems(array $items): array
    {
        return array_map(function (array $item): array {
            return [
                'id' => $item['menu_item_id'] ?? uniqid('booking_item_'),
                'price' => (int) ($item['unit_price'] ?? 0),
                'quantity' => (int) ($item['quantity'] ?? 1),
                'name' => Str::limit($item['name'] ?? 'Item', 50, ''),
            ];
        }, $items);
    }

    /**
     * Format shipping item name for Midtrans display.
     */
    private function formatShippingItemName(Order $order): string
    {
        $courier = $order->shipping_courier ?? '';
        $service = $order->shipping_service ?? '';

        $parts = array_filter([$courier, $service]);

        return 'Ongkir'.($parts ? ' - '.implode(' ', $parts) : '');
    }
}
