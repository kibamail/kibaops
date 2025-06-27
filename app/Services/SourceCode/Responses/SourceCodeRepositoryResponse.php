<?php

namespace App\Services\SourceCode\Responses;

/**
 * Source Code Repository Response
 *
 * Response for operations that return a single repository.
 */
class SourceCodeRepositoryResponse
{
    public function __construct(
        public bool $success,
        public ?array $repository = null,
        public ?string $error = null,
        public ?array $metadata = null
    ) {}

    public static function success(array $repository, array $metadata = []): self
    {
        return new self(
            success: true,
            repository: $repository,
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
