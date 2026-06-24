<?php

use Livewire\Livewire;
use Tests\Feature\Concerns\DashboardAlertTraitProbe;

test('dashboard alert trait dispatches js for each variant', function (string $kind, string $variant) {
    $expected = 'window.showDashboardAlert('.json_encode([
        'variant' => $variant,
        'title' => 'T',
        'message' => 'M',
    ], JSON_UNESCAPED_UNICODE).')';

    Livewire::test(DashboardAlertTraitProbe::class)
        ->call('fire', $kind)
        ->assertJs($expected);
})->with([
    ['success', 'success'],
    ['warning', 'warning'],
    ['danger', 'danger'],
    ['failed', 'failed'],
]);
