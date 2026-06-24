<?php

it('renders order tracking view using microsite layout', function () {
    $response = $this->get(route('microsite.tracking'));

    $response->assertSuccessful()
        ->assertSee('Status Booking')
        ->assertSee('Masukkan order ID Anda terlebih dahulu')
        ->assertSee('Order ID')
        ->assertSee(route('microsite.menu'), false);
});
