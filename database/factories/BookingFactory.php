<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_reference' => 'BK-'.strtoupper(uniqid()),
            'type' => Booking::TYPE_MICROSITE,
            'user_id' => User::factory(),
            'table_id' => Table::factory(),
            'items' => [],
            'total_amount' => fake()->randomFloat(2, 100000, 900000),
            'booking_date' => now()->addDays(fake()->numberBetween(0, 14)),
            'booking_time' => '19:00:00',
            'guest_count' => fake()->numberBetween(1, 8),
            'booking_status' => Booking::BOOKING_STATUS_PENDING,
            'payment_status' => Booking::PAYMENT_STATUS_PENDING,
            'cancellation_reason' => null,
            'note' => null,
        ];
    }
}
