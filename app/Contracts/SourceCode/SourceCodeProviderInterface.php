<?php

namespace App\Contracts\SourceCode;

use App\Services\SourceCode\Responses\SourceCodeBranchListResponse;
use App\Services\SourceCode\Responses\SourceCodeCommitListResponse;
use App\Services\SourceCode\Responses\SourceCodeConnectionResponse;
use App\Services\SourceCode\Responses\SourceCodeFileResponse;
use App\Services\SourceCode\Responses\SourceCodeNormalizedWebhookEvent;
use App\Services\SourceCode\Responses\SourceCodeRepositoryListResponse;
use App\Services\SourceCode\Responses\SourceCodeRepositoryResponse;
use App\Services\SourceCode\Responses\SourceCodeSyncResponse;
use App\Services\SourceCode\Responses\SourceCodeWebhookResponse;

interface SourceCodeProviderInterface
{
    /**
     * Get the connection management interface.
     */
    public function connection(): ConnectionInterface;

    /**
     * Get the repository management interface.
     */
    public function repositories(): RepositoryInterface;

    /**
     * Get the webhook management interface.
     */
    public function webhooks(): WebhookInterface;

    /**
     * Get the file operations interface.
     */
    public function files(): FileInterface;
}

interface ConnectionInterface
{
    /**
     * Initiate a new connection with the provider.
     */
    public function initiate(array $config, string $state): SourceCodeConnectionResponse;

    /**
     * Complete the connection process (OAuth callback).
     */
    public function complete(string $code, array $state): SourceCodeConnectionResponse;

    /**
     * Refresh the connection credentials.
     */
    public function refresh(string $connectionId): SourceCodeConnectionResponse;

    /**
     * Test if the connection is valid.
     */
    public function test(string $connectionId): SourceCodeConnectionResponse;

    /**
     * Revoke the connection.
     */
    public function revoke(string $connectionId): SourceCodeConnectionResponse;
}

interface RepositoryInterface
{
    /**
     * Get all repositories for a connection.
     */
    public function get(string $connectionId, ?string $cursor = null): SourceCodeRepositoryListResponse;

    /**
     * Get a specific repository.
     */
    public function find(string $connectionId, string $repositoryId): SourceCodeRepositoryResponse;

    /**
     * Sync repositories from the provider.
     */
    public function sync(string $connectionId): SourceCodeSyncResponse;

    /**
     * Get branches for a repository.
     */
    public function branches(string $connectionId, string $repositoryId): SourceCodeBranchListResponse;

    /**
     * Get commits for a repository branch.
     */
    public function commits(string $connectionId, string $repositoryId, string $branch): SourceCodeCommitListResponse;
}

interface WebhookInterface
{
    /**
     * Configure webhooks for repositories.
     */
    public function configure(string $connectionId, array $repositories): SourceCodeWebhookResponse;

    /**
     * Validate webhook signature.
     */
    public function validateSignature(string $payload, string $signature): bool;

    /**
     * Parse webhook payload into normalized format.
     */
    public function parse(string $payload): SourceCodeNormalizedWebhookEvent;

    /**
     * Remove webhooks for repositories.
     */
    public function remove(string $connectionId, array $repositories): SourceCodeWebhookResponse;
}

interface FileInterface
{
    /**
     * Get file content from repository.
     */
    public function get(string $connectionId, string $repositoryId, string $path, string $ref): SourceCodeFileResponse;

    /**
     * Create a new file in repository.
     */
    public function create(string $connectionId, string $repositoryId, string $path, string $content, string $message): SourceCodeFileResponse;

    /**
     * Update an existing file in repository.
     */
    public function update(string $connectionId, string $repositoryId, string $path, string $content, string $message, string $sha): SourceCodeFileResponse;

    /**
     * Delete a file from repository.
     */
    public function delete(string $connectionId, string $repositoryId, string $path, string $message, string $sha): SourceCodeFileResponse;
}
