<?php

namespace App\Services\SourceCode\Responses;

/**
 * Source Code Connection Response
 *
 * Standardized response for source code provider connection operations.
 * Used for OAuth flows, connection testing, and credential management.
 */
class SourceCodeConnectionResponse
{
    public function __construct(
        public bool $success,
        public ?string $connectionId = null,
        public ?array $accountInfo = null,
        public ?string $error = null,
        public ?array $credentials = null,
        public ?array $metadata = null
    ) {}

    public static function success(
        string $connectionId,
        array $accountInfo,
        array $credentials = [],
        array $metadata = []
    ): self {
        return new self(
            success: true,
            connectionId: $connectionId,
            accountInfo: $accountInfo,
            credentials: $credentials,
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
