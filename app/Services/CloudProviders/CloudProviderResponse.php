<?php

namespace App\Services\CloudProviders;

/**
 * Represents the response from a cloud provider credential verification operation.
 *
 * This class provides a simple, standardized way to communicate the result of
 * cloud provider API operations, particularly credential verification. It contains
 * only the essential information needed to determine if an operation succeeded
 * and provides a human-readable message describing the outcome.
 *
 * The class is designed to be immutable with readonly properties to ensure
 * response integrity throughout the application lifecycle.
 */
class CloudProviderResponse
{
    /**
     * Create a new cloud provider response.
     *
     * @param  bool  $success  Whether the cloud provider operation was successful
     * @param  string  $message  A descriptive message about the operation result
     * @param  mixed  $data  Optional data returned from the operation (e.g., for GET requests)
     */
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly mixed $data = null
    ) {}

    /**
     * Create a successful response instance.
     *
     * This factory method creates a response indicating that the cloud provider
     * operation completed successfully. Used when credentials are valid and
     * the provider API responds as expected.
     *
     * @param  string  $message  Optional success message (defaults to generic success message)
     * @param  mixed  $data  Optional data returned from the operation
     * @return self A new successful CloudProviderResponse instance
     */
    public static function success(string $message = 'Credentials verified successfully', mixed $data = null): self
    {
        return new self(true, $message, $data);
    }

    /**
     * Create a failure response instance.
     *
     * This factory method creates a response indicating that the cloud provider
     * operation failed. Used when credentials are invalid, API is unreachable,
     * or any other error condition occurs during verification.
     *
     * @param  string  $message  Descriptive error message explaining why the operation failed
     * @return self A new failed CloudProviderResponse instance
     */
    public static function failure(string $message): self
    {
        return new self(false, $message);
    }
}
