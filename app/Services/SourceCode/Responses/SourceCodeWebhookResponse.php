<?php

namespace App\Services\SourceCode\Responses;

/**
 * Source Code Webhook Response
 *
 * Response for webhook configuration and management operations.
 */
class SourceCodeWebhookResponse
{
    public function __construct(
        public bool $success,
        public array $configuredRepositories = [],
        public array $failedRepositories = [],
        public ?string $error = null,
        public ?array $metadata = null
    ) {}

    public static function success(
        array $configuredRepositories,
        array $failedRepositories = [],
        array $metadata = []
    ): self {
        return new self(
            success: true,
            configuredRepositories: $configuredRepositories,
            failedRepositories: $failedRepositories,
            metadata: $metadata
        );
    }

    public static function failure(string $error): self
    {
        return new self(
            success: false,
            error: $error
        );
    }
}
