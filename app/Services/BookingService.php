<?php

namespace App\Services;

use App\External\MidtransService;
use App\Helper\BookingReferenceHelper;
use App\Models\Booking;
use App\Models\MenuItem;
use App\Models\Table;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\BookingCreatedNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        protected MidtransService $midtransService,
        protected BookingReferenceHelper $bookingReferenceHelper,
    ) {}

    /**
     * @param  array{
     *     customer_name: string,
     *     customer_email: string,
     *     customer_phone: string,
     *     note?: string,
     *     reservation_date: string,
     *     reservation_time: string,
     *     party_size: int,
     *     selected_table_id: int
     * }  $reservation
     * @param  array<int, array{id: int, quantity: int}>  $cartItems
     * @return array{
     *     booking: Booking,
     *     snap_token: string,
     *     redirect_url: string
     * }
     */
    public function createBooking(array $reservation, array $cartItems): array
    {
        $selectedTable = $this->resolveAvailableTableForParty(
            (int) $reservation['selected_table_id'],
            (int) $reservation['party_size'],
            'selectedTableId',
            'partySize',
        );

        $menuItems = $this->menuItemsForCartOrFail($cartItems);

        $orderItems = $this->buildOrderItems(collect($cartItems), $menuItems);
        $totalAmount = $orderItems->sum('subtotal');

        $user = User::query()->firstOrCreate(
            ['email' => (string) $reservation['customer_email']],
            [
                'name' => (string) $reservation['customer_name'],
                'phone_number' => (string) $reservation['customer_phone'],
                'password' => Hash::make(Str::random(40)),
            ],
        );

        $checkout = DB::transaction(function () use ($orderItems, $reservation, $selectedTable, $totalAmount, $user): array {
            $booking = Booking::query()->create([
                'booking_reference' => $this->bookingReferenceHelper->generate(),
                'type' => Booking::TYPE_MICROSITE,
                'user_id' => $user->id,
                'table_id' => $selectedTable->id,
                'items' => $orderItems->values()->all(),
                'total_amount' => $totalAmount,
                'booking_date' => $reservation['reservation_date'],
                'booking_time' => $reservation['reservation_time'],
                'guest_count' => $reservation['party_size'],
                'booking_status' => Booking::BOOKING_STATUS_PENDING,
                'payment_status' => Booking::PAYMENT_STATUS_PENDING,
                'note' => trim((string) data_get($reservation, 'note', '')) !== ''
                    ? trim((string) data_get($reservation, 'note', ''))
                    : null,
            ]);

            $booking->transaction()->create([
                'amount' => $totalAmount,
                'status' => Transaction::STATUS_PENDING,
            ]);

            $booking->load(['transaction', 'table']);

            $payment = $this->midtransService->createSnapTransactionForBooking(
                $booking,
                [
                    'name' => (string) $reservation['customer_name'],
                    'email' => (string) $reservation['customer_email'],
                    'phone' => (string) $reservation['customer_phone'],
                ],
                route('microsite.tracking'),
            );

            $booking->transaction()->update([
                'midtrans_transaction_id' => $booking->booking_reference,
                'snap_token' => $payment['token'],
                'raw_response' => $payment,
            ]);

            return [
                'booking' => $booking->fresh(['transaction', 'table', 'user']),
                'snap_token' => $payment['token'],
                'redirect_url' => $payment['redirect_url'],
            ];
        });

        Notification::send($user, new BookingCreatedNotification($checkout['booking'], $checkout['redirect_url']));

        return $checkout;
    }

    /**
     * @param  array{
     *     user_id: int,
     *     table_id: int,
     *     booking_date: string,
     *     booking_time: string,
     *     guest_count: int,
     *     booking_status: string,
     *     payment_status: string,
     *     note?: string|null,
     *     cancellation_reason?: string|null,
     * }  $payload
     * @param  array<int, array{id: int, quantity: int}>  $cartItems
     */
    public function createManualBooking(array $payload, array $cartItems): Booking
    {
        $selectedTable = $this->resolveAvailableTableForParty(
            (int) $payload['table_id'],
            (int) $payload['guest_count'],
            'table_id',
            'guest_count',
        );

        $menuItems = $this->menuItemsForCartOrFail($cartItems);

        $orderItems = $this->buildOrderItems(collect($cartItems), $menuItems);
        $totalAmount = $orderItems->sum('subtotal');

        return DB::transaction(function () use ($payload, $selectedTable, $orderItems, $totalAmount): Booking {
            $booking = Booking::query()->create([
                'booking_reference' => $this->bookingReferenceHelper->generate(),
                'type' => Booking::TYPE_MANUAL,
                'user_id' => (int) $payload['user_id'],
                'table_id' => $selectedTable->id,
                'items' => $orderItems->values()->all(),
                'total_amount' => $totalAmount,
                'booking_date' => $payload['booking_date'],
                'booking_time' => $payload['booking_time'],
                'guest_count' => (int) $payload['guest_count'],
                'booking_status' => $payload['booking_status'],
                'payment_status' => $payload['payment_status'],
                'note' => isset($payload['note']) && trim((string) $payload['note']) !== ''
                    ? trim((string) $payload['note'])
                    : null,
                'cancellation_reason' => isset($payload['cancellation_reason']) && trim((string) $payload['cancellation_reason']) !== ''
                    ? trim((string) $payload['cancellation_reason'])
                    : null,
            ]);

            $this->createManualTransaction($booking, (float) $totalAmount);

            return $booking->fresh(['transaction']);
        });
    }

    /**
     * Memperbarui hanya item pesanan dan total; field reservasi lain tidak diubah.
     *
     * @param  array<int, array{id: int, quantity: int}>  $cartItems
     */
    public function updateBookingItems(Booking $booking, array $cartItems): Booking
    {
        if ($cartItems === []) {
            throw ValidationException::withMessages([
                'cartItems' => 'Pesanan harus berisi minimal satu item menu.',
            ]);
        }

        $menuItems = $this->menuItemsForCartOrFail($cartItems);
        $orderItems = $this->buildOrderItems(collect($cartItems), $menuItems);
        $totalAmount = (float) $orderItems->sum('subtotal');

        return DB::transaction(function () use ($booking, $orderItems, $totalAmount): Booking {
            $booking->update([
                'items' => $orderItems->values()->all(),
                'total_amount' => $totalAmount,
            ]);

            if ($booking->transaction()->exists()) {
                $booking->transaction()->update(['amount' => $totalAmount]);
            }

            return $booking->fresh(['transaction']);
        });
    }

    private function createManualTransaction(Booking $booking, float $totalAmount): Transaction
    {
        $transactionStatus = match ($booking->payment_status) {
            Booking::PAYMENT_STATUS_PAID => Transaction::STATUS_SUCCESS,
            Booking::PAYMENT_STATUS_EXPIRED => Transaction::STATUS_EXPIRED,
            Booking::PAYMENT_STATUS_REFUNDED => Transaction::STATUS_FAILED,
            default => Transaction::STATUS_PENDING,
        };

        return $booking->transaction()->create([
            'amount' => $totalAmount,
            'status' => $transactionStatus,
            'payment_method' => 'manual',
            'paid_at' => $transactionStatus === Transaction::STATUS_SUCCESS ? now() : null,
        ]);
    }

    /**
     * @param  array<int, array{id: int, quantity: int}>  $cartItems
     */
    private function menuItemsForCartOrFail(array $cartItems): Collection
    {
        $menuItems = MenuItem::query()
            ->whereIn('id', collect($cartItems)->pluck('id'))
            ->where('status', MenuItem::STATUS_AVAILABLE)
            ->get(['id', 'name', 'price']);

        if ($menuItems->count() !== count($cartItems)) {
            throw ValidationException::withMessages([
                'cartItems' => 'Beberapa item menu tidak tersedia lagi.',
            ]);
        }

        return $menuItems;
    }

    private function resolveAvailableTableForParty(
        int $tableId,
        int $partySize,
        string $tableErrorKey,
        string $partyErrorKey,
    ): Table {
        $selectedTable = Table::query()
            ->whereKey($tableId)
            ->where('status', Table::STATUS_AVAILABLE)
            ->first();

        if (! $selectedTable instanceof Table) {
            throw ValidationException::withMessages([
                $tableErrorKey => 'Meja yang dipilih tidak tersedia.',
            ]);
        }

        if ($selectedTable->capacity < $partySize) {
            throw ValidationException::withMessages([
                $partyErrorKey => 'Jumlah tamu melebihi kapasitas meja yang dipilih.',
            ]);
        }

        return $selectedTable;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array{id: int, quantity: int}>  $normalizedCartItems
     * @return \Illuminate\Support\Collection<int, array{menu_item_id: int, name: string, unit_price: float, quantity: int, subtotal: float}>
     */
    private function buildOrderItems(\Illuminate\Support\Collection $normalizedCartItems, Collection $menuItems): \Illuminate\Support\Collection
    {
        /** @var array<int, MenuItem> $menuItemsById */
        $menuItemsById = $menuItems->keyBy('id')->all();

        return $normalizedCartItems->map(function (array $cartItem) use ($menuItemsById): array {
            $menuItem = $menuItemsById[$cartItem['id']];
            $unitPrice = (float) $menuItem->price;
            $quantity = $cartItem['quantity'];

            return [
                'menu_item_id' => $menuItem->id,
                'name' => $menuItem->name,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'subtotal' => $unitPrice * $quantity,
            ];
        });
    }
}
