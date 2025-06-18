<?php

namespace App\Enums;

enum SourceCodeProviderType: string
{
    case GITHUB = 'github';
    case BITBUCKET = 'bitbucket';
    case GITLAB = 'gitlab';
    case AZURE_DEVOPS = 'azure_devops';
    case GITEA = 'gitea';
    case CODEBERG = 'codeberg';

    public function label(): string
    {
        return match($this) {
            self::GITHUB => 'GitHub',
            self::BITBUCKET => 'Bitbucket',
            self::GITLAB => 'GitLab',
            self::AZURE_DEVOPS => 'Azure DevOps',
            self::GITEA => 'Gitea',
            self::CODEBERG => 'Codeberg',
        };
    }

    public function authenticationMethod(): AuthenticationMethod
    {
        return match($this) {
            self::GITHUB => AuthenticationMethod::GITHUB_APP,
            self::BITBUCKET => AuthenticationMethod::OAUTH2,
            self::GITLAB => AuthenticationMethod::OAUTH2,
            self::AZURE_DEVOPS => AuthenticationMethod::OAUTH2,
            self::GITEA => AuthenticationMethod::OAUTH2,
            self::CODEBERG => AuthenticationMethod::OAUTH2,
        };
    }

    public function webhookEvents(): array
    {
        return match($this) {
            self::GITHUB => ['push', 'pull_request', 'release', 'issues'],
            self::BITBUCKET => ['repo:push', 'pullrequest:created', 'pullrequest:updated'],
            self::GITLAB => ['push', 'merge_requests', 'tag_push', 'releases'],
            self::AZURE_DEVOPS => ['git.push', 'git.pullrequest.created', 'git.pullrequest.updated'],
            self::GITEA => ['push', 'pull_request', 'release'],
            self::CODEBERG => ['push', 'pull_request', 'release'],
        };
    }

    public function implemented(): bool
    {
        return in_array($this, [
            self::GITHUB,
            // Add others as implemented
        ]);
    }

    public static function implementedProviders(): array
    {
        return array_filter(self::cases(), fn($case) => $case->implemented());
    }

    public static function allProviders(): array
    {
        return array_map(fn($case) => [
            'type' => $case->value,
            'label' => $case->label(),
            'implemented' => $case->implemented(),
            'authentication_method' => $case->authenticationMethod()->value,
            'webhook_events' => $case->webhookEvents(),
        ], self::cases());
    }
}

enum AuthenticationMethod: string
{
    case OAUTH2 = 'oauth2';
    case GITHUB_APP = 'github_app';
    case PERSONAL_ACCESS_TOKEN = 'personal_access_token';
    case SSH_KEY = 'ssh_key';
    case API_KEY = 'api_key';

    public function label(): string
    {
        return match($this) {
            self::OAUTH2 => 'OAuth 2.0',
            self::GITHUB_APP => 'GitHub App',
            self::PERSONAL_ACCESS_TOKEN => 'Personal Access Token',
            self::SSH_KEY => 'SSH Key',
            self::API_KEY => 'API Key',
        };
    }
}
