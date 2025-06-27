<?php

namespace App\Services\SourceCode\Responses;

/**
 * Source Code Normalized Webhook Event
 *
 * Standardized webhook event data structure used across all providers.
 */
class SourceCodeNormalizedWebhookEvent
{
    public function __construct(
        public string $eventType,
        public string $action,
        public string $repositoryId,
        public string $repositoryName,
        public ?string $branchName = null,
        public ?string $commitSha = null,
        public ?array $commits = null,
        public array $rawPayload = []
    ) {}
}
