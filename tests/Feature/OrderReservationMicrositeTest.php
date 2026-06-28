<?php

use App\Livewire\Microsite\OrderReservationMicrosite;
use App\Models\Table;
use Livewire\Livewire;

it('renders reservation time options from 08:00 until 22:00', function () {
    Livewire::test(OrderReservationMicrosite::class)
        ->assertSeeHtml('value="08:00"')
        ->assertSee('08:00')
        ->assertSeeHtml('value="22:00"')
        ->assertSee('22:00')
        ->assertDontSee('23:00');
});

it('auto selects the first available indoor table', function () {
    $firstIndoorTable = Table::query()->create([
        'table_number' => 'A-01',
        'capacity' => 2,
        'location_description' => 'Indoor - Window seat',
        'status' => Table::STATUS_AVAILABLE,
    ]);

    Table::query()->create([
        'table_number' => 'A-02',
        'capacity' => 4,
        'location_description' => 'Indoor - Center area',
        'status' => Table::STATUS_AVAILABLE,
    ]);

    Livewire::test(OrderReservationMicrosite::class)
        ->assertSet('selectedTableId', $firstIndoorTable->id);
});

it('renders stable radio bindings and keeps the selected table checked', function () {
    $firstIndoorTable = Table::query()->create([
        'table_number' => 'A-01',
        'capacity' => 2,
        'location_description' => 'Indoor - Window seat',
        'status' => Table::STATUS_AVAILABLE,
    ]);

    $secondIndoorTable = Table::query()->create([
        'table_number' => 'A-02',
        'capacity' => 4,
        'location_description' => 'Indoor - Center area',
        'status' => Table::STATUS_AVAILABLE,
    ]);

    $component = Livewire::test(OrderReservationMicrosite::class)
        ->assertSet('selectedTableId', $firstIndoorTable->id);

    $initialHtml = $component->html();

    expect($initialHtml)
        ->toContain("wire:key=\"table-option-{$firstIndoorTable->id}\"")
        ->toContain("id=\"table-selection-{$firstIndoorTable->id}\"")
        ->toContain('wire:model.live.number="selectedTableId"');

    expect($initialHtml)
        ->toMatch('/id="table-selection-'.$firstIndoorTable->id.'".*?value="'.$firstIndoorTable->id.'".*?checked/s');

    $component
        ->set('selectedTableId', $secondIndoorTable->id)
        ->assertSet('selectedTableId', $secondIndoorTable->id);

    $updatedHtml = $component->html();

    expect($updatedHtml)
        ->toContain("wire:key=\"table-option-{$secondIndoorTable->id}\"")
        ->toContain("id=\"table-selection-{$secondIndoorTable->id}\"");

    expect($updatedHtml)
        ->toMatch('/id="table-selection-'.$secondIndoorTable->id.'".*?value="'.$secondIndoorTable->id.'".*?checked/s');
});

it('stores reservation details in session and redirects to the summary page', function () {
    $selectedTable = Table::query()->create([
        'table_number' => 'A-01',
        'capacity' => 4,
        'location_description' => 'Indoor - Window seat',
        'status' => Table::STATUS_AVAILABLE,
    ]);

    Livewire::test(OrderReservationMicrosite::class)
        ->set('customerName', 'Budi Santoso')
        ->set('customerEmail', 'budi@example.com')
        ->set('customerPhone', '081234567890')
        ->set('notes', 'Tolong siapkan kursi bayi.')
        ->set('reservationDate', '2026-04-05')
        ->set('reservationTime', '18:00')
        ->set('partySize', 4)
        ->set('selectedTableId', $selectedTable->id)
        ->call('proceedToSummary')
        ->assertRedirect(route('microsite.summary'));

    expect(session('microsite.reservation'))
        ->toMatchArray([
            'customer_name' => 'Budi Santoso',
            'customer_email' => 'budi@example.com',
            'customer_phone' => '081234567890',
            'notes' => 'Tolong siapkan kursi bayi.',
            'reservation_date' => '2026-04-05',
            'reservation_time' => '18:00',
            'party_size' => 4,
            'selected_area' => 'indoor',
            'selected_table_id' => $selectedTable->id,
            'table_number' => 'A-01',
            'table_capacity' => 4,
            'table_location_description' => 'Indoor - Window seat',
        ]);
});

it('validates customer contact details before proceeding to summary', function () {
    $selectedTable = Table::query()->create([
        'table_number' => 'A-01',
        'capacity' => 4,
        'location_description' => 'Indoor - Window seat',
        'status' => Table::STATUS_AVAILABLE,
    ]);

    Livewire::test(OrderReservationMicrosite::class)
        ->set('customerName', '')
        ->set('customerEmail', 'email-tidak-valid')
        ->set('customerPhone', 'phone-invalid')
        ->set('selectedTableId', $selectedTable->id)
        ->call('proceedToSummary')
        ->assertHasErrors([
            'customerName' => ['required'],
            'customerEmail' => ['email'],
            'customerPhone' => ['regex'],
        ]);
});

it('validates the notes length before proceeding to summary', function () {
    $selectedTable = Table::query()->create([
        'table_number' => 'A-01',
        'capacity' => 4,
        'location_description' => 'Indoor - Window seat',
        'status' => Table::STATUS_AVAILABLE,
    ]);

    Livewire::test(OrderReservationMicrosite::class)
        ->set('customerName', 'Budi Santoso')
        ->set('customerEmail', 'budi@example.com')
        ->set('customerPhone', '081234567890')
        ->set('notes', str_repeat('a', 501))
        ->set('reservationDate', '2026-04-05')
        ->set('reservationTime', '18:00')
        ->set('partySize', 4)
        ->set('selectedTableId', $selectedTable->id)
        ->call('proceedToSummary')
        ->assertHasErrors([
            'notes' => ['max'],
        ]);
});

it('allows the customer to select outdoor area and shows outdoor tables', function () {
    $indoorTable = Table::query()->create([
        'table_number' => 'A-01',
        'capacity' => 2,
        'location_description' => 'Indoor - Window seat',
        'status' => Table::STATUS_AVAILABLE,
    ]);

    $outdoorTable = Table::query()->create([
        'table_number' => 'B-01',
        'capacity' => 2,
        'location_description' => 'Outdoor garden view',
        'status' => Table::STATUS_AVAILABLE,
    ]);

    Livewire::test(OrderReservationMicrosite::class)
        ->assertSet('selectedArea', 'indoor')
        ->assertSet('selectedTableId', $indoorTable->id)
        ->set('selectedArea', 'outdoor')
        ->assertSet('selectedArea', 'outdoor')
        ->assertSet('selectedTableId', $outdoorTable->id);
});
