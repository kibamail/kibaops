<?php

namespace App\Services\CloudProviders;

use App\Contracts\CloudProviderInterface;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

abstract class AbstractCloudProvider implements CloudProviderInterface
{
    protected HttpClient $http;

    protected int $maxRetries = 2;

    protected int $baseDelayMs = 1000;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    public function setRetryConfig(int $maxRetries = 2, int $baseDelayMs = 1000): self
    {
        $this->maxRetries = $maxRetries;
        $this->baseDelayMs = $baseDelayMs;

        return $this;
    }

    abstract public function verify(array $credentials): CloudProviderResponse;

    protected function makeRequest(string $method, string $url, array $options = []): CloudProviderResponse
    {
        $attempt = 1;
        $lastResponse = null;

        while ($attempt <= $this->maxRetries + 1) {
            try {
                $this->logAttempt($method, $url, $attempt, $options);

                $response = $this->executeRequest($method, $url, $options);
                $lastResponse = $response;

                if ($response->successful()) {
                    $this->logSuccess($method, $url, $response, $attempt);

                    return CloudProviderResponse::success(
                        message: 'API request completed successfully',
                        rawResponse: $response->json(),
                        attemptCount: $attempt
                    );
                }

                $errorResponse = $this->handleErrorResponse($response, $attempt);

                if (! $errorResponse->isRetryable() || $attempt > $this->maxRetries) {
                    $this->logFailure($method, $url, $response, $attempt, $errorResponse);

                    return $errorResponse;
                }

                $delay = $this->calculateDelay($attempt);
                $this->logRetry($method, $url, $response, $attempt, $delay);
                usleep($delay * 1000);

            } catch (RequestException $e) {
                if ($attempt > $this->maxRetries) {
                    $this->logNetworkError($method, $url, $attempt, $e);

                    return CloudProviderResponse::failure(
                        message: 'Network error occurred while contacting cloud provider',
                        errors: ['network' => $e->getMessage()],
                        attemptCount: $attempt
                    );
                }
                usleep($this->calculateDelay($attempt) * 1000);

            } catch (\Exception $e) {
                $this->logUnexpectedError($method, $url, $attempt, $e);

                return CloudProviderResponse::failure(
                    message: 'Unexpected error occurred while contacting cloud provider',
                    errors: ['unexpected' => $e->getMessage()],
                    attemptCount: $attempt
                );
            }

            $attempt++;
        }

        return CloudProviderResponse::failure(
            message: 'Maximum retry attempts exceeded',
            httpStatusCode: $lastResponse?->status(),
            attemptCount: $attempt - 1
        );
    }

    protected function executeRequest(string $method, string $url, array $options): Response
    {
        return match ($method) {
            'get' => $this->http->withHeaders($options['headers'] ?? [])->get($url),
            'post' => $this->http->withHeaders($options['headers'] ?? [])->post($url, $options['data'] ?? []),
            'put' => $this->http->withHeaders($options['headers'] ?? [])->put($url, $options['data'] ?? []),
            'delete' => $this->http->withHeaders($options['headers'] ?? [])->delete($url),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
        };
    }

    protected function logAttempt(string $method, string $url, int $attempt, array $options): void
    {
        Log::info('Cloud provider API request', [
            'method' => strtoupper($method),
            'url' => $url,
            'attempt' => $attempt,
            'headers' => $this->sanitizeHeaders($options['headers'] ?? []),
        ]);
    }

    protected function logSuccess(string $method, string $url, Response $response, int $attempt): void
    {
        Log::info('Cloud provider API success', [
            'method' => strtoupper($method),
            'url' => $url,
            'status' => $response->status(),
            'attempt' => $attempt,
        ]);
    }

    protected function logFailure(string $method, string $url, Response $response, int $attempt, CloudProviderResponse $errorResponse): void
    {
        Log::error('Cloud provider API failed', [
            'method' => strtoupper($method),
            'url' => $url,
            'status' => $response->status(),
            'attempt' => $attempt,
            'retryable' => $errorResponse->isRetryable(),
            'body' => $response->body(),
        ]);
    }

    protected function logRetry(string $method, string $url, Response $response, int $attempt, int $delay): void
    {
        Log::warning('Cloud provider API retry', [
            'method' => strtoupper($method),
            'url' => $url,
            'status' => $response->status(),
            'attempt' => $attempt,
            'delay_ms' => $delay,
        ]);
    }

    protected function logNetworkError(string $method, string $url, int $attempt, RequestException $e): void
    {
        Log::error('Cloud provider network error', [
            'method' => strtoupper($method),
            'url' => $url,
            'attempt' => $attempt,
            'error' => $e->getMessage(),
        ]);
    }

    protected function logUnexpectedError(string $method, string $url, int $attempt, \Exception $e): void
    {
        Log::error('Cloud provider unexpected error', [
            'method' => strtoupper($method),
            'url' => $url,
            'attempt' => $attempt,
            'error' => $e->getMessage(),
        ]);
    }

    protected function handleErrorResponse(Response $response, int $attempt): CloudProviderResponse
    {
        $statusCode = $response->status();
        $responseBody = $response->json() ?? [];
        $providerMessage = $this->extractProviderErrorMessage($responseBody);

        $message = match ($statusCode) {
            401 => 'Invalid credentials provided',
            403 => 'Access denied - insufficient permissions',
            404 => 'API endpoint not found',
            429 => 'Rate limit exceeded - too many requests',
            500 => 'Cloud provider internal server error',
            502 => 'Bad gateway - cloud provider temporarily unavailable',
            503 => 'Service unavailable - cloud provider maintenance',
            504 => 'Gateway timeout - cloud provider not responding',
            default => "API request failed with status {$statusCode}",
        };

        return CloudProviderResponse::failure(
            message: $message,
            errors: $responseBody['errors'] ?? null,
            httpStatusCode: $statusCode,
            providerMessage: $providerMessage,
            rawResponse: $responseBody,
            attemptCount: $attempt
        );
    }

    protected function extractProviderErrorMessage(array $responseBody): ?string
    {
        return $responseBody['error']['message'] ??
               $responseBody['message'] ??
               $responseBody['error'] ??
               null;
    }

    protected function calculateDelay(int $attempt): int
    {
        return min($this->baseDelayMs * (2 ** ($attempt - 1)), 10000);
    }

    protected function sanitizeHeaders(array $headers): array
    {
        $sanitized = $headers;

        if (isset($sanitized['Authorization'])) {
            $auth = $sanitized['Authorization'];
            if (str_starts_with($auth, 'Bearer ')) {
                $token = substr($auth, 7);
                $sanitized['Authorization'] = 'Bearer '.substr($token, 0, 8).'***';
            }
        }

        return $sanitized;
    }
}
