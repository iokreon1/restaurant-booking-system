<?php

use App\Http\Controllers\Webhook\MidtransWebhook;
use Illuminate\Support\Facades\Route;

Route::post('webhooks/midtrans', [MidtransWebhook::class, 'handleMidtransWebhook'])
    ->name('api.webhooks.midtrans');
