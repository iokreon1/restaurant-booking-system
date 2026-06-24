<?php

use App\Livewire\Microsite\OrderTrackingMicrosite;
use App\Models\Booking;
use App\Models\Table;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

it('shows booking information when the order id exists', function () {
    $user = User::factory()->create();

    $table = Table::query()->create([
        'table_number' => 'A-01',
        'capacity' => 4,
        'location_description' => 'Indoor - Window seat',
        'status' => Table::STATUS_AVAILABLE,
    ]);

    $booking = Booking::query()->create([
        'booking_reference' => 'BK-TRACK123',
        'user_id' => $user->id,
        'table_id' => $table->id,
        'items' => [
            [
                'name' => 'Mushroom Risotto',
                'quantity' => 2,
                'subtotal' => 290000,
            ],
            [
                'name' => 'Garden Salad',
                'quantity' => 1,
                'subtotal' => 85000,
            ],
        ],
        'total_amount' => 375000,
        'booking_date' => '2026-04-05',
        'booking_time' => '18:00:00',
        'guest_count' => 4,
        'booking_status' => Booking::BOOKING_STATUS_CONFIRMED,
        'payment_status' => Booking::PAYMENT_STATUS_PAID,
        'note' => 'Reserved by Budi Santoso (081234567890)',
    ]);

    Transaction::query()->create([
        'booking_id' => $booking->id,
        'amount' => 375000,
        'status' => Transaction::STATUS_SUCCESS,
    ]);

    Livewire::test(OrderTrackingMicrosite::class)
        ->set('orderId', 'bk-track123')
        ->call('searchOrder')
        ->assertHasNoErrors()
        ->assertSee('BK-TRACK123')
        ->assertSee('Booking Dikonfirmasi')
        ->assertSee('Pembayaran Berhasil')
        ->assertSee('Transaksi Berhasil')
        ->assertSee('Meja A-01')
        ->assertSee('4 Orang')
        ->assertSee('Rp375.000')
        ->assertSee('Mushroom Risotto')
        ->assertSee('Garden Salad');
});

it('shows an error when the order id does not exist', function () {
    Livewire::test(OrderTrackingMicrosite::class)
        ->set('orderId', 'BK-UNKNOWN')
        ->call('searchOrder')
        ->assertHasErrors(['orderId'])
        ->assertSee('Order ID tidak ditemukan. Pastikan kode yang dimasukkan benar.');
});
