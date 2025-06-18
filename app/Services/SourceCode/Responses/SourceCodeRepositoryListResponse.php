<?php

namespace App\Services\SourceCode\Responses;

/**
 * Source Code Repository List Response
 *
 * Response for operations that return multiple repositories with pagination support.
 */
class SourceCodeRepositoryListResponse
{
    public function __construct(
        public bool $success,
        public array $repositories = [],
        public ?string $nextCursor = null,
        public ?string $error = null,
        public ?array $metadata = null
    ) {}

    public static function success(
        array $repositories,
        ?string $nextCursor = null,
        array $metadata = []
    ): self {
        return new self(
            success: true,
            repositories: $repositories,
            nextCursor: $nextCursor,
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
