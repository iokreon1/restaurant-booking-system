<?php

use App\External\KirimiService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'services.kirimi.user_code' => 'test-user-code',
        'services.kirimi.device_id' => 'test-device-id',
        'services.kirimi.secret' => 'test-secret',
    ]);
});

it('sends a message successfully', function () {
    Http::fake([
        'api.kirimi.id/v1/send-message' => Http::response([
            'success' => true,
            'message' => 'Pesan berhasil dikirim',
            'data' => ['message_id' => 'msg_123', 'status' => 'sent'],
        ]),
    ]);

    $service = new KirimiService;
    $result = $service->sendMessage('6281234567890', 'Hello!');

    expect($result)
        ->toHaveKey('success', true)
        ->toHaveKey('message', 'Message sent successfully')
        ->toHaveKey('data');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.kirimi.id/v1/send-message'
            && $request['phone'] === '6281234567890'
            && $request['message'] === 'Hello!'
            && $request['user_code'] === 'test-user-code'
            && $request['device_id'] === 'test-device-id'
            && $request['secret'] === 'test-secret'
            && ! isset($request['media_url']);
    });
});

it('sends a message with media url', function () {
    Http::fake([
        'api.kirimi.id/v1/send-message' => Http::response([
            'success' => true,
            'message' => 'Pesan berhasil dikirim',
        ]),
    ]);

    $service = new KirimiService;
    $result = $service->sendMessage('6281234567890', 'Check this!', 'https://example.com/image.jpg');

    expect($result)->toHaveKey('success', true);

    Http::assertSent(function ($request) {
        return $request['media_url'] === 'https://example.com/image.jpg';
    });
});

it('handles api failure', function () {
    Http::fake([
        'api.kirimi.id/v1/send-message' => Http::response(['error' => 'Invalid credentials'], 401),
    ]);

    $service = new KirimiService;
    $result = $service->sendMessage('6281234567890', 'Hello!');

    expect($result)
        ->toHaveKey('success', false)
        ->toHaveKey('message', 'Failed to send message');
});

it('reports configured status correctly', function () {
    $service = new KirimiService;
    expect($service->isConfigured())->toBeTrue();

    config(['services.kirimi.user_code' => '']);
    $service = new KirimiService;
    expect($service->isConfigured())->toBeFalse();
});
