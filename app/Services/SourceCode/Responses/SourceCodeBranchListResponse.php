<?php

namespace App\Services\SourceCode\Responses;

/**
 * Source Code Branch List Response
 *
 * Response for operations that return repository branches.
 */
class SourceCodeBranchListResponse
{
    public function __construct(
        public bool $success,
        public array $branches = [],
        public ?string $error = null,
        public ?array $metadata = null
    ) {}

    public static function success(array $branches, array $metadata = []): self
    {
        return new self(
            success: true,
            branches: $branches,
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
