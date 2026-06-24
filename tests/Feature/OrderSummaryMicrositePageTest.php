<?php

it('renders order summary microsite page', function () {
    $this->get(route('microsite.summary'))
        ->assertSuccessful()
        ->assertSee('Pesanan Anda');
});
