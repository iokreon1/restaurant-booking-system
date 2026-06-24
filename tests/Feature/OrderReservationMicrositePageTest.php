<?php

use App\Models\Table;

it('renders available tables on reservation page', function () {
    Table::query()->create([
        'table_number' => 'A-01',
        'capacity' => 4,
        'location_description' => 'Indoor - Window seat',
        'status' => Table::STATUS_AVAILABLE,
    ]);

    Table::query()->create([
        'table_number' => 'A-03',
        'capacity' => 4,
        'location_description' => 'Indoor - Corner',
        'status' => Table::STATUS_BOOKED,
    ]);

    $this->get(route('microsite.reservation'))
        ->assertSuccessful()
        ->assertSee('Meja A-01')
        ->assertDontSee('Meja A-03');
});
