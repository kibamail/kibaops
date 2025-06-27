<?php

namespace App\Services\SourceCode\Providers\GitHub;

use App\Contracts\SourceCode\WebhookInterface;
use App\Services\SourceCode\Responses\SourceCodeNormalizedWebhookEvent;
use App\Services\SourceCode\Responses\SourceCodeWebhookResponse;

class GitHubWebhookService implements WebhookInterface
{
    public function __construct() {}

    public function configure(string $connectionId, array $repositories): SourceCodeWebhookResponse
    {
        return SourceCodeWebhookResponse::failure('Not implemented yet');
    }

    public function validateSignature(string $payload, string $signature): bool
    {
        $secret = config('services.github.webhook_secret');

        if (empty($secret) || empty($signature)) {
            return false;
        }

        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    public function parse(string $payload): SourceCodeNormalizedWebhookEvent
    {
        $data = json_decode($payload, true);

        if (! $data) {
            return new SourceCodeNormalizedWebhookEvent(
                eventType: 'unknown',
                action: 'unknown',
                repositoryId: '',
                repositoryName: '',
                rawPayload: []
            );
        }

        $eventInfo = $this->extractEventInfo($data);

        return new SourceCodeNormalizedWebhookEvent(
            eventType: $eventInfo['event_type'],
            action: $eventInfo['action'],
            repositoryId: $eventInfo['repository_id'],
            repositoryName: $eventInfo['repository_name'],
            rawPayload: $data
        );
    }

    public function remove(string $connectionId, array $repositories): SourceCodeWebhookResponse
    {
        return SourceCodeWebhookResponse::failure('Not implemented yet');
    }

    protected function getInstallationToken(string $connectionId): ?string
    {
        return null;
    }

    protected function createRepositoryWebhook(string $token, string $repositoryId, array $events): array
    {
        return [];
    }

    protected function removeRepositoryWebhook(string $token, string $repositoryId, string $webhookId): bool
    {
        return false;
    }

    protected function calculateSignature(string $payload, string $secret): string
    {
        return hash_hmac('sha256', $payload, $secret);
    }

    protected function extractEventInfo(array $payload): array
    {
        $repository = $payload['repository'] ?? [];
        $action = $payload['action'] ?? 'unknown';
        $eventType = $payload['_github_event'] ?? 'unknown';

        return [
            'event_type' => $eventType,
            'action' => $action,
            'repository_id' => (string) ($repository['id'] ?? ''),
            'repository_name' => $repository['full_name'] ?? '',
            'branch_name' => $this->extractBranchName($payload),
            'commit_sha' => $this->extractCommitSha($payload),
            'commits' => $payload['commits'] ?? [],
        ];
    }

    protected function extractBranchName(array $payload): ?string
    {
        if (isset($payload['ref']) && str_starts_with($payload['ref'], 'refs/heads/')) {
            return substr($payload['ref'], 11);
        }

        return $payload['pull_request']['head']['ref'] ?? null;
    }

    protected function extractCommitSha(array $payload): ?string
    {
        return $payload['after'] ?? $payload['pull_request']['head']['sha'] ?? null;
    }

    protected function mapGitHubEvent(array $payload): string
    {
        return 'unknown';
    }
}
