<?php

it('renders order reservation view using microsite layout', function () {
    $response = $this->get(route('microsite.reservation'));

    $response->assertSuccessful()
        ->assertSee('Reservasi Meja')
        ->assertSee('Detail')
        ->assertSee('Reservasi')
        ->assertSee('Nama Pemesan')
        ->assertSee('Email')
        ->assertSee('Nomor Telepon')
        ->assertSee(route('microsite.menu'), false)
        ->assertSee('Lanjutkan ke Ringkasan');
});
