<?php

namespace App\Services\SourceCode;

use App\Contracts\SourceCode\SourceCodeProviderInterface;
use App\Enums\SourceCodeProviderType;
use App\Models\SourceCode\SourceCodeConnection;

abstract class AbstractSourceCodeProvider implements SourceCodeProviderInterface
{
    protected SourceCodeProviderType $providerType;

    protected ?SourceCodeConnection $connection = null;

    public function __construct() {}

    public function setConnection(SourceCodeConnection $connection): void
    {
        $this->connection = $connection;
    }

    protected function getConnection(): ?SourceCodeConnection
    {
        return $this->connection;
    }

    protected function getCredentials(): array
    {
        if (! $this->connection) {
            return [];
        }

        return $this->connection->getCredentials();
    }

    protected function storeCredentials(string $connectionId, array $credentials): void
    {
        $connection = SourceCodeConnection::find($connectionId);
        if ($connection) {
            $connection->storeCredentials($credentials);
        }
    }

    protected function makeRequest(string $method, string $url, array $data = []): array
    {
        // This will be implemented by concrete providers
        // Each provider will have its own HTTP client implementation
        return [];
    }

    protected function normalizeRepository(array $providerRepo): array
    {
        // Each provider will implement its own normalization logic
        return [];
    }

    protected function normalizeWebhookEvent(array $payload): array
    {
        // Each provider will implement its own webhook normalization
        return [];
    }

    protected function buildAuthUrl(array $scopes = []): string
    {
        // Each provider will implement its own auth URL building
        return '';
    }

    protected function exchangeCodeForToken(string $code, string $state = ''): array
    {
        // Each provider will implement its own token exchange
        return [];
    }

    protected function refreshToken(string $refreshToken): array
    {
        // Each provider will implement its own token refresh
        return [];
    }

    protected function validateCredentials(array $credentials): bool
    {
        // Each provider will implement its own credential validation
        return false;
    }

    protected function getApiBaseUrl(): string
    {
        // Each provider will return its API base URL
        return '';
    }

    protected function getWebhookEvents(): array
    {
        return $this->providerType->webhookEvents();
    }

    protected function getAuthenticationMethod(): string
    {
        return $this->providerType->authenticationMethod()->value;
    }
}
