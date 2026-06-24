<?php

namespace App\External;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class HttpClient
{
    protected string $apiKey;

    protected string $apiBaseUrl;

    protected int $timeout = 30;

    protected string $serviceName;

    /**
     * Get the service name for logging purposes.
     */
    abstract protected function getServiceName(): string;

    /**
     * Get default headers for all requests.
     *
     * @return array<string, string>
     */
    protected function getDefaultHeaders(): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if (! empty($this->apiKey)) {
            $headers['Authorization'] = "Bearer {$this->apiKey}";
        }

        return $headers;
    }

    /**
     * Create a configured HTTP client instance.
     */
    protected function client(): PendingRequest
    {
        return Http::withHeaders($this->getDefaultHeaders())
            ->timeout($this->timeout);
    }

    /**
     * Make a GET request.
     *
     * @param  string  $url  The endpoint URL (relative to base URL or full URL)
     * @param  array<string, mixed>  $query  Query parameters
     */
    protected function get(string $url, array $query = []): Response
    {
        $fullUrl = $this->buildUrl($url);
        $payload = ['query' => $query];

        $this->logRequest('GET', $fullUrl, $payload);

        $response = $this->client()->get($fullUrl, $query);

        $this->logResponse($response, 'GET');

        return $response;
    }

    /**
     * Make a POST request.
     *
     * @param  string  $url  The endpoint URL (relative to base URL or full URL)
     * @param  array<string, mixed>  $data  Request body data
     */
    protected function post(string $url, array $data = []): Response
    {
        $fullUrl = $this->buildUrl($url);

        $this->logRequest('POST', $fullUrl, $data);

        $response = $this->client()->post($fullUrl, $data);

        $this->logResponse($response, 'POST');

        return $response;
    }

    /**
     * Make a PUT request.
     *
     * @param  string  $url  The endpoint URL (relative to base URL or full URL)
     * @param  array<string, mixed>  $data  Request body data
     */
    protected function put(string $url, array $data = []): Response
    {
        $fullUrl = $this->buildUrl($url);

        $this->logRequest('PUT', $fullUrl, $data);

        $response = $this->client()->put($fullUrl, $data);

        $this->logResponse($response, 'PUT');

        return $response;
    }

    /**
     * Make a PATCH request.
     *
     * @param  string  $url  The endpoint URL (relative to base URL or full URL)
     * @param  array<string, mixed>  $data  Request body data
     */
    protected function patch(string $url, array $data = []): Response
    {
        $fullUrl = $this->buildUrl($url);

        $this->logRequest('PATCH', $fullUrl, $data);

        $response = $this->client()->patch($fullUrl, $data);

        $this->logResponse($response, 'PATCH');

        return $response;
    }

    /**
     * Make a DELETE request.
     *
     * @param  string  $url  The endpoint URL (relative to base URL or full URL)
     * @param  array<string, mixed>  $data  Request body data
     */
    protected function delete(string $url, array $data = []): Response
    {
        $fullUrl = $this->buildUrl($url);

        $this->logRequest('DELETE', $fullUrl, $data);

        $response = $this->client()->delete($fullUrl, $data);

        $this->logResponse($response, 'DELETE');

        return $response;
    }

    /**
     * Build full URL from endpoint.
     */
    protected function buildUrl(string $url): string
    {
        // If URL is already full (starts with http:// or https://), return as is
        if (preg_match('/^https?:\/\//', $url)) {
            return $url;
        }

        // Otherwise, prepend base URL
        $baseUrl = rtrim($this->apiBaseUrl, '/');
        $endpoint = ltrim($url, '/');

        return "{$baseUrl}/{$endpoint}";
    }

    /**
     * Log HTTP request.
     *
     * @param  string  $method  HTTP method
     * @param  string  $url  Full URL
     * @param  array<string, mixed>  $payload  Request payload
     */
    protected function logRequest(string $method, string $url, array $payload = []): void
    {
        Log::info("{$this->getServiceName()}: HTTP Request", [
            'method' => $method,
            'url' => $url,
            'payload' => $this->sanitizePayload($payload),
        ]);
    }

    /**
     * Log HTTP response.
     *
     * @param  Response  $response  HTTP response
     * @param  string  $method  HTTP method used
     */
    protected function logResponse(Response $response, string $method): void
    {
        $logData = [
            'method' => $method,
            'status' => $response->status(),
            'headers' => $response->headers(),
            'body' => $response->body(),
            'json' => $response->json(),
        ];

        if ($response->successful()) {
            Log::info("{$this->getServiceName()}: HTTP Response", $logData);
        } else {
            Log::error("{$this->getServiceName()}: HTTP Error Response", $logData);
        }
    }

    /**
     * Sanitize payload for logging (remove sensitive data if needed).
     *
     * @param  array<string, mixed>  $payload  Original payload
     * @return array<string, mixed> Sanitized payload
     */
    protected function sanitizePayload(array $payload): array
    {
        // Create a copy to avoid modifying the original
        $sanitized = $payload;

        // Override this method in child classes to mask sensitive data
        // Example: mask API keys, passwords, tokens, etc.

        return $sanitized;
    }

    /**
     * Handle API response and return standardized format.
     *
     * @param  Response  $response  HTTP response
     * @param  string  $successMessage  Success message
     * @param  string  $errorMessage  Error message
     * @return array{success: bool, message: string, data?: array}
     */
    protected function handleResponse(Response $response, string $successMessage = 'Request successful', string $errorMessage = 'Request failed'): array
    {
        if ($response->successful()) {
            $responseData = $response->json();

            return [
                'success' => true,
                'message' => $successMessage,
                'data' => $responseData,
            ];
        }

        return [
            'success' => false,
            'message' => $errorMessage,
            'data' => [
                'status' => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ],
        ];
    }

    /**
     * Handle exceptions and return standardized format.
     *
     * @param  \Exception  $e  Exception
     * @param  string  $context  Context information for logging
     * @return array{success: bool, message: string}
     */
    protected function handleException(\Exception $e, string $context = ''): array
    {
        $contextData = ! empty($context) ? ['context' => $context] : [];

        Log::error("{$this->getServiceName()}: Exception occurred", array_merge($contextData, [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]));

        return [
            'success' => false,
            'message' => 'Exception occurred: '.$e->getMessage(),
        ];
    }

    /**
     * Check if service is properly configured.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->apiKey) && ! empty($this->apiBaseUrl);
    }

    /**
     * Validate required configuration.
     *
     * @param  array<string>  $requiredFields  List of required field names
     * @return array{valid: bool, message?: string}
     */
    protected function validateConfiguration(array $requiredFields = []): array
    {
        if (empty($this->apiKey)) {
            return [
                'valid' => false,
                'message' => "{$this->getServiceName()} API key is not configured",
            ];
        }

        if (empty($this->apiBaseUrl)) {
            return [
                'valid' => false,
                'message' => "{$this->getServiceName()} API base URL is not configured",
            ];
        }

        foreach ($requiredFields as $field) {
            if (empty($this->$field ?? null)) {
                return [
                    'valid' => false,
                    'message' => "{$this->getServiceName()} {$field} is not configured",
                ];
            }
        }

        return ['valid' => true];
    }
}
