<?php

namespace App\Services\SourceCode\Responses;

/**
 * Source Code File Response
 *
 * Response for file operations (get, create, update, delete).
 */
class SourceCodeFileResponse
{
    public function __construct(
        public bool $success,
        public ?string $content = null,
        public ?string $sha = null,
        public ?string $encoding = null,
        public ?string $error = null,
        public ?array $metadata = null
    ) {}

    public static function success(
        ?string $content = null,
        ?string $sha = null,
        ?string $encoding = null,
        array $metadata = []
    ): self {
        return new self(
            success: true,
            content: $content,
            sha: $sha,
            encoding: $encoding,
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
