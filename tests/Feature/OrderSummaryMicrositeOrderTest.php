<?php

use App\External\MidtransService;
use App\Livewire\Microsite\OrderSummaryMicrosite;
use App\Models\Booking;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Table;
use App\Models\Transaction;
use Livewire\Livewire;

it('creates a booking from the summary page and redirects to the payment url', function () {
    $table = Table::query()->create([
        'table_number' => 'A-01',
        'capacity' => 4,
        'location_description' => 'Indoor - Window seat',
        'status' => Table::STATUS_AVAILABLE,
    ]);

    $category = MenuCategory::query()->create([
        'name' => 'Main Course',
        'thumbnail_path' => 'images/default.png',
        'sort_order' => 1,
        'status' => MenuCategory::STATUS_ACTIVE,
    ]);

    $item = MenuItem::query()->create([
        'category_id' => $category->id,
        'name' => 'Mushroom Risotto',
        'description' => 'Creamy risotto',
        'price' => 145000,
        'thumbnail_path' => 'images/default.png',
        'status' => MenuItem::STATUS_AVAILABLE,
        'sort_order' => 1,
    ]);

    session([
        'microsite.reservation' => [
            'customer_name' => 'Budi Santoso',
            'customer_email' => 'budi@example.com',
            'customer_phone' => '081234567890',
            'notes' => 'Tolong siapkan kursi bayi.',
            'reservation_date' => '2026-04-05',
            'reservation_time' => '18:00',
            'party_size' => 4,
            'selected_table_id' => $table->id,
            'table_number' => 'A-01',
            'table_capacity' => 4,
            'table_location_description' => 'Indoor - Window seat',
        ],
    ]);

    $midtransService = Mockery::mock(MidtransService::class);
    $midtransService
        ->shouldReceive('createSnapTransactionForBooking')
        ->once()
        ->andReturn([
            'token' => 'snap-summary-123',
            'redirect_url' => 'https://pay.example.com/checkout/summary',
        ]);

    $this->app->instance(MidtransService::class, $midtransService);

    Livewire::test(OrderSummaryMicrosite::class)
        ->call('confirmOrder', [
            [
                'id' => $item->id,
                'quantity' => 2,
                'unitPrice' => 1,
            ],
        ])
        ->assertRedirect('https://pay.example.com/checkout/summary');

    expect(Booking::query()->count())->toBe(1)
        ->and(Transaction::query()->count())->toBe(1)
        ->and(Booking::query()->first()?->note)->toBe('Tolong siapkan kursi bayi.')
        ->and(Booking::query()->first()?->type)->toBe(Booking::TYPE_MICROSITE)
        ->and(session()->has('microsite.reservation'))->toBeFalse();
});

it('validates reservation session data and cart items before creating a booking', function () {
    session([
        'microsite.reservation' => [
            'customer_name' => '',
            'customer_email' => 'email-tidak-valid',
            'customer_phone' => 'invalid-phone',
            'notes' => str_repeat('a', 501),
            'reservation_date' => '',
            'reservation_time' => 'bad-time',
            'party_size' => 0,
            'selected_table_id' => null,
        ],
    ]);

    Livewire::test(OrderSummaryMicrosite::class)
        ->call('confirmOrder', [])
        ->assertHasErrors([
            'customerName' => ['required'],
            'customerEmail' => ['email'],
            'customerPhone' => ['regex'],
            'notes' => ['max'],
            'reservationDate' => ['required'],
            'reservationTime' => ['date_format'],
            'partySize' => ['min'],
            'selectedTableId' => ['required'],
            'cartItems',
        ]);
});
