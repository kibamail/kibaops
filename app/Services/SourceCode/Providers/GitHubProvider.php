<?php

namespace App\Services\SourceCode\Providers;

use App\Contracts\SourceCode\ConnectionInterface;
use App\Contracts\SourceCode\FileInterface;
use App\Contracts\SourceCode\RepositoryInterface;
use App\Contracts\SourceCode\WebhookInterface;
use App\Enums\SourceCodeProviderType;
use App\Services\SourceCode\AbstractSourceCodeProvider;
use App\Services\SourceCode\Providers\GitHub\GitHubConnectionService;
use App\Services\SourceCode\Providers\GitHub\GitHubFileService;
use App\Services\SourceCode\Providers\GitHub\GitHubRepositoryService;
use App\Services\SourceCode\Providers\GitHub\GitHubWebhookService;

class GitHubProvider extends AbstractSourceCodeProvider
{
    public function __construct()
    {
        parent::__construct();
        $this->providerType = SourceCodeProviderType::GITHUB;
    }

    public function connection(): ConnectionInterface
    {
        return new GitHubConnectionService;
    }

    public function repositories(): RepositoryInterface
    {
        return new GitHubRepositoryService;
    }

    public function webhooks(): WebhookInterface
    {
        return new GitHubWebhookService;
    }

    public function files(): FileInterface
    {
        return new GitHubFileService;
    }

    protected function getApiBaseUrl(): string
    {
        return 'https://api.github.com';
    }

    protected function buildAuthUrl(array $scopes = []): string
    {
        $appName = config('services.github.app_name');

        return "https://github.com/apps/{$appName}/installations/new";
    }

    protected function normalizeRepository(array $githubRepo): array
    {
        return [
            'external_repository_id' => (string) $githubRepo['id'],
            'name' => $githubRepo['name'],
            'owner_repo' => $githubRepo['full_name'],
            'description' => $githubRepo['description'],
            'visibility' => $githubRepo['private'] ? 'private' : 'public',
            'default_branch' => $githubRepo['default_branch'],
            'clone_urls' => [
                'https' => $githubRepo['clone_url'],
                'ssh' => $githubRepo['ssh_url'],
                'git' => $githubRepo['git_url'],
            ],
            'web_url' => $githubRepo['html_url'],
            'language' => $githubRepo['language'],
            'topics' => $githubRepo['topics'] ?? [],
            'archived' => $githubRepo['archived'],
            'fork' => $githubRepo['fork'],
            'repository_metadata' => [
                'size' => $githubRepo['size'],
                'stargazers_count' => $githubRepo['stargazers_count'],
                'watchers_count' => $githubRepo['watchers_count'],
                'forks_count' => $githubRepo['forks_count'],
                'open_issues_count' => $githubRepo['open_issues_count'],
                'license' => $githubRepo['license']['name'] ?? null,
                'created_at' => $githubRepo['created_at'],
                'updated_at' => $githubRepo['updated_at'],
                'pushed_at' => $githubRepo['pushed_at'],
            ],
        ];
    }

    protected function normalizeWebhookEvent(array $payload): array
    {
        // This will be implemented when we handle webhooks
        return [];
    }
}
