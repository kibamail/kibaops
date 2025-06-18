<?php

namespace App\Services\SourceCode\Responses;

/**
 * Source Code Commit List Response
 *
 * Response for operations that return repository commits with pagination support.
 */
class SourceCodeCommitListResponse
{
    public function __construct(
        public bool $success,
        public array $commits = [],
        public ?string $nextCursor = null,
        public ?string $error = null,
        public ?array $metadata = null
    ) {}

    public static function success(
        array $commits,
        ?string $nextCursor = null,
        array $metadata = []
    ): self {
        return new self(
            success: true,
            commits: $commits,
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
