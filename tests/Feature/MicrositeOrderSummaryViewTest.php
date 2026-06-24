<?php

it('renders order summary with microsite layout assets', function () {
    $response = $this->withSession([
        'microsite.reservation' => [
            'customer_name' => 'Budi Santoso',
            'customer_email' => 'budi@example.com',
            'customer_phone' => '081234567890',
            'reservation_date' => '2026-04-05',
            'reservation_time' => '18:00',
            'party_size' => 4,
            'selected_area' => 'indoor',
            'selected_table_id' => 1,
            'table_number' => 'A-01',
            'table_capacity' => 4,
            'table_location_description' => 'Indoor - Window seat',
        ],
    ])->get(route('microsite.summary'));

    $response->assertSuccessful()
        ->assertSee('Ringkasan Order')
        ->assertSee('Ringkasan Pembayaran')
        ->assertSee('Detail Reservasi')
        ->assertSee('Budi Santoso')
        ->assertSee('budi@example.com')
        ->assertSee('081234567890')
        ->assertSee('Meja A-01')
        ->assertSee('Indoor - Window seat')
        ->assertSee(route('microsite.reservation'), false)
        ->assertSee('Konfirmasi Order');
});
