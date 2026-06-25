<?php

use App\Models\User;
use Tests\TestCase;

test('guests are redirected to the login page', function () {
    /** @var TestCase $this */
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated admin users can visit the dashboard', function () {
    /** @var User $user */
    $user = User::factory()->admin()->create();
    /** @var TestCase $this */
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertViewIs('app.dashboard.index');
    $response->assertViewHas([
        'summary',
        'pending_bookings',
        'trend_14_days',
        'trend_max',
        'monthly_reservations',
        'footer',
        'recent_activity',
        'user',
    ]);
});

test('non-admin users are blocked from visiting the dashboard', function () {
    /** @var User $user */
    $user = User::factory()->create();
    /** @var TestCase $this */
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertForbidden();
});
