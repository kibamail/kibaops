<?php

namespace App\Services\SourceCode\Responses;

/**
 * Source Code Sync Response
 *
 * Response for repository synchronization operations with statistics.
 */
class SourceCodeSyncResponse
{
    public function __construct(
        public bool $success,
        public int $repositoriesAdded = 0,
        public int $repositoriesUpdated = 0,
        public int $repositoriesRemoved = 0,
        public ?string $error = null,
        public ?array $metadata = null
    ) {}

    public static function success(
        int $repositoriesAdded,
        int $repositoriesUpdated,
        int $repositoriesRemoved,
        array $metadata = []
    ): self {
        return new self(
            success: true,
            repositoriesAdded: $repositoriesAdded,
            repositoriesUpdated: $repositoriesUpdated,
            repositoriesRemoved: $repositoriesRemoved,
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
