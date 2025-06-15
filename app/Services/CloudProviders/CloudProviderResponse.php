<?php

namespace App\Services\CloudProviders;

class CloudProviderResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly ?array $errors = null,
        public readonly ?int $httpStatusCode = null,
        public readonly ?string $providerMessage = null,
        public readonly ?array $rawResponse = null,
        public readonly int $attemptCount = 1
    ) {}

    /**
     * Create a successful response.
     */
    public static function success(
        string $message = 'Credentials verified successfully',
        ?array $rawResponse = null,
        int $attemptCount = 1
    ): self {
        return new self(
            success: true,
            message: $message,
            rawResponse: $rawResponse,
            attemptCount: $attemptCount
        );
    }

    /**
     * Create a failed response.
     */
    public static function failure(
        string $message,
        ?array $errors = null,
        ?int $httpStatusCode = null,
        ?string $providerMessage = null,
        ?array $rawResponse = null,
        int $attemptCount = 1
    ): self {
        return new self(
            success: false,
            message: $message,
            errors: $errors,
            httpStatusCode: $httpStatusCode,
            providerMessage: $providerMessage,
            rawResponse: $rawResponse,
            attemptCount: $attemptCount
        );
    }

    /**
     * Check if this is a retryable error based on HTTP status code.
     */
    public function isRetryable(): bool
    {
        if ($this->success || ! $this->httpStatusCode) {
            return false;
        }

        return in_array($this->httpStatusCode, [
            429, // Too Many Requests
            500, // Internal Server Error
            502, // Bad Gateway
            503, // Service Unavailable
            504, // Gateway Timeout
        ]);
    }

    /**
     * Get a user-friendly error message combining our message with provider message.
     */
    public function getDetailedMessage(): string
    {
        if ($this->success) {
            return $this->message;
        }

        $message = $this->message;

        if ($this->providerMessage) {
            $message .= " Provider error: {$this->providerMessage}";
        }

        if ($this->httpStatusCode) {
            $message .= " (HTTP {$this->httpStatusCode})";
        }

        return $message;
    }

    /**
     * Convert to array for logging or API responses.
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'detailed_message' => $this->getDetailedMessage(),
            'errors' => $this->errors,
            'http_status_code' => $this->httpStatusCode,
            'provider_message' => $this->providerMessage,
            'attempt_count' => $this->attemptCount,
            'is_retryable' => $this->isRetryable(),
        ];
    }
}
