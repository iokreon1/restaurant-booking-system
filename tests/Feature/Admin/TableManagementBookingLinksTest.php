<?php

use App\Livewire\Admin\TableManagementPage;
use App\Models\Booking;
use App\Models\Table;
use App\Models\User;
use Livewire\Livewire;

test('booked table card links Manage Order to booking edit page', function () {
    $admin = User::factory()->admin()->create();

    $table = Table::factory()->create([
        'status' => Table::STATUS_BOOKED,
        'table_number' => 'A-BOOKED',
    ]);

    $booking = Booking::factory()->create([
        'table_id' => $table->id,
    ]);

    $editUrl = route('admin.bookings.edit', $booking);

    Livewire::actingAs($admin)
        ->test(TableManagementPage::class)
        ->assertOk()
        ->assertSee('Manage Order')
        ->assertSee($editUrl)
        ->assertDontSee('Assign Table');
});

test('inactive table card links Check In to booking detail page', function () {
    $admin = User::factory()->admin()->create();

    $table = Table::factory()->create([
        'status' => Table::STATUS_INACTIVE,
        'table_number' => 'A-INACTIVE',
    ]);

    $booking = Booking::factory()->create([
        'table_id' => $table->id,
    ]);

    $showUrl = route('admin.bookings.show', $booking);

    Livewire::actingAs($admin)
        ->test(TableManagementPage::class)
        ->assertOk()
        ->assertSee('Check In')
        ->assertSee($showUrl)
        ->assertDontSee('Assign Table');
});
