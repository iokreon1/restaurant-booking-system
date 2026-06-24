<?php

namespace App\External;

class KirimiService extends HttpClient
{
    private string $userCode;

    private string $deviceId;

    private string $secret;

    public function __construct()
    {
        $this->apiBaseUrl = 'https://api.kirimi.id/v1';
        $this->userCode = (string) config('services.kirimi.user_code', '');
        $this->deviceId = (string) config('services.kirimi.device_id', '');
        $this->secret = (string) config('services.kirimi.secret', '');
    }

    protected function getServiceName(): string
    {
        return 'Kirimi';
    }

    /**
     * Send a WhatsApp message via Kirimi API.
     *
     * @param  string  $phone  Recipient WhatsApp number
     * @param  string  $message  Text message to send
     * @param  string|null  $mediaUrl  Optional media URL (image/video/audio/document)
     * @return array{success: bool, message: string, data?: array}
     */
    public function sendMessage(string $phone, string $message, ?string $mediaUrl = null): array
    {
        try {
            $payload = [
                'user_code' => $this->userCode,
                'secret' => $this->secret,
                'device_id' => $this->deviceId,
                'phone' => $phone,
                'message' => $message,
            ];

            if ($mediaUrl !== null) {
                $payload['media_url'] = $mediaUrl;
            }

            $response = $this->post('/send-message', $payload);

            return $this->handleResponse($response, 'Message sent successfully', 'Failed to send message');
        } catch (\Exception $e) {
            return $this->handleException($e, 'sendMessage');
        }
    }

    protected function sanitizePayload(array $payload): array
    {
        $sanitized = $payload;

        if (isset($sanitized['secret'])) {
            $sanitized['secret'] = '***';
        }

        if (isset($sanitized['user_code'])) {
            $sanitized['user_code'] = '***';
        }

        return $sanitized;
    }

    public function isConfigured(): bool
    {
        return ! empty($this->userCode)
            && ! empty($this->deviceId)
            && ! empty($this->secret);
    }
}
