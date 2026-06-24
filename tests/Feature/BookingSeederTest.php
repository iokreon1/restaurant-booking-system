<?php

use App\Models\Booking;
use App\Models\Transaction;
use Database\Seeders\BookingSeeder;
use Database\Seeders\MenuCategorySeeder;
use Database\Seeders\MenuItemSeeder;
use Database\Seeders\TableSeeder;
use Database\Seeders\UserSeeder;

it('creates one transaction record for each seeded booking', function () {
    $this->seed([
        UserSeeder::class,
        MenuCategorySeeder::class,
        MenuItemSeeder::class,
        TableSeeder::class,
        BookingSeeder::class,
    ]);

    $bookingCount = Booking::query()->count();

    expect($bookingCount)->toBeGreaterThan(0)
        ->and(Transaction::query()->count())->toBe($bookingCount);
});
