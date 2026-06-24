<?php

namespace Database\Seeders;

use App\Helper\BookingReferenceHelper;
use App\Models\Booking;
use App\Models\MenuItem;
use App\Models\Table;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menuItems = MenuItem::query()
            ->where('status', MenuItem::STATUS_AVAILABLE)
            ->orderBy('id')
            ->get();

        $tables = Table::query()
            ->where('status', Table::STATUS_AVAILABLE)
            ->orderBy('id')
            ->get();

        $users = User::query()->orderBy('id')->get();

        if ($menuItems->count() < 3 || $tables->isEmpty() || $users->count() < 2) {
            $this->command?->warn('BookingSeeder skipped: need at least 3 available menu items, one available table, and two users. Run User, menu, and table seeders first.');

            return;
        }

        /** @var BookingReferenceHelper $references */
        $references = app(BookingReferenceHelper::class);

        $scenarios = [
            [
                'type' => Booking::TYPE_MICROSITE,
                'lines' => [
                    [$menuItems[0], 2],
                    [$menuItems[1], 1],
                ],
                'booking_status' => Booking::BOOKING_STATUS_CONFIRMED,
                'payment_status' => Booking::PAYMENT_STATUS_PAID,
                'note' => 'Microsite — pembayaran sukses.',
                'days_from_now' => 2,
            ],
            [
                'type' => Booking::TYPE_MICROSITE,
                'lines' => [
                    [$menuItems[1], 1],
                ],
                'booking_status' => Booking::BOOKING_STATUS_PENDING,
                'payment_status' => Booking::PAYMENT_STATUS_PENDING,
                'note' => 'Microsite — menunggu pembayaran.',
                'days_from_now' => 5,
            ],
            [
                'type' => Booking::TYPE_MICROSITE,
                'lines' => [
                    [$menuItems[2], 3],
                ],
                'booking_status' => Booking::BOOKING_STATUS_CANCELLED,
                'payment_status' => Booking::PAYMENT_STATUS_EXPIRED,
                'note' => 'Microsite — pembayaran kedaluwarsa.',
                'days_from_now' => -1,
            ],
            [
                'type' => Booking::TYPE_MANUAL,
                'lines' => [
                    [$menuItems[0], 1],
                    [$menuItems[2], 2],
                ],
                'booking_status' => Booking::BOOKING_STATUS_CONFIRMED,
                'payment_status' => Booking::PAYMENT_STATUS_PAID,
                'note' => 'Admin — tunai di kasir.',
                'days_from_now' => 1,
            ],
            [
                'type' => Booking::TYPE_MANUAL,
                'lines' => [
                    [$menuItems[1], 2],
                ],
                'booking_status' => Booking::BOOKING_STATUS_PENDING,
                'payment_status' => Booking::PAYMENT_STATUS_PENDING,
                'note' => 'Admin — tagih di meja.',
                'days_from_now' => 7,
            ],
            [
                'type' => Booking::TYPE_MANUAL,
                'lines' => [
                    [$menuItems[2], 1],
                ],
                'booking_status' => Booking::BOOKING_STATUS_CANCELLED,
                'payment_status' => Booking::PAYMENT_STATUS_REFUNDED,
                'note' => 'Admin — refund ke rekening tamu.',
                'days_from_now' => -3,
            ],
        ];

        foreach ($scenarios as $index => $scenario) {
            $user = $users[$index % $users->count()];
            $table = $tables[$index % $tables->count()];
            [$itemsPayload, $totalAmount] = $this->buildOrderLines($scenario['lines']);
            $guestCount = min(6, max(2, (int) $table->capacity));

            $bookingDate = Carbon::now()->addDays((int) $scenario['days_from_now'])->toDateString();

            $booking = Booking::query()->create([
                'booking_reference' => $references->generate(),
                'type' => $scenario['type'],
                'user_id' => $user->id,
                'table_id' => $table->id,
                'items' => $itemsPayload,
                'total_amount' => $totalAmount,
                'booking_date' => $bookingDate,
                'booking_time' => $index % 2 === 0 ? '19:00:00' : '12:30:00',
                'guest_count' => $guestCount,
                'booking_status' => $scenario['booking_status'],
                'payment_status' => $scenario['payment_status'],
                'note' => $scenario['note'],
                'cancellation_reason' => null,
            ]);

            $this->createTransactionForSeededBooking($booking, (float) $totalAmount);
        }
    }

    /**
     * @param  list<array{0: MenuItem, 1: int}>  $lines
     * @return array{0: list<array<string, mixed>>, 1: float}
     */
    private function buildOrderLines(array $lines): array
    {
        $itemsPayload = [];
        $totalAmount = 0.0;

        foreach ($lines as [$menuItem, $quantity]) {
            $unitPrice = (float) $menuItem->price;
            $subtotal = $unitPrice * $quantity;
            $totalAmount += $subtotal;
            $itemsPayload[] = [
                'menu_item_id' => $menuItem->id,
                'name' => $menuItem->name,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ];
        }

        return [$itemsPayload, $totalAmount];
    }

    private function createTransactionForSeededBooking(Booking $booking, float $amount): Transaction
    {
        $transactionStatus = match ($booking->payment_status) {
            Booking::PAYMENT_STATUS_PAID => Transaction::STATUS_SUCCESS,
            Booking::PAYMENT_STATUS_EXPIRED => Transaction::STATUS_EXPIRED,
            Booking::PAYMENT_STATUS_REFUNDED => Transaction::STATUS_FAILED,
            default => Transaction::STATUS_PENDING,
        };

        $isMicrosite = $booking->type === Booking::TYPE_MICROSITE;

        return $booking->transaction()->create([
            'amount' => $amount,
            'status' => $transactionStatus,
            'payment_method' => $isMicrosite ? 'midtrans' : 'manual',
            'midtrans_transaction_id' => $isMicrosite ? $booking->booking_reference : null,
            'snap_token' => $isMicrosite ? 'SEED-SNAP-'.strtoupper(uniqid()) : null,
            'paid_at' => $transactionStatus === Transaction::STATUS_SUCCESS ? now() : null,
            'raw_response' => $isMicrosite ? ['source' => 'seeder', 'booking_reference' => $booking->booking_reference] : null,
        ]);
    }
}
