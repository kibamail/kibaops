<?php

namespace App\Services\SourceCode;

use App\Contracts\SourceCode\SourceCodeProviderInterface;
use App\Enums\SourceCodeProviderType;
use App\Services\SourceCode\Providers\GitHubProvider;
use InvalidArgumentException;

class SourceCodeProviderFactory
{
    public static function create(SourceCodeProviderType $type): SourceCodeProviderInterface
    {
        return match($type) {
            SourceCodeProviderType::GITHUB => new GitHubProvider(),
            // TODO: Add other providers as they are implemented
            // SourceCodeProviderType::BITBUCKET => new BitbucketProvider(),
            // SourceCodeProviderType::GITLAB => new GitLabProvider(),
            // SourceCodeProviderType::AZURE_DEVOPS => new AzureDevOpsProvider(),
            // SourceCodeProviderType::GITEA => new GiteaProvider(),
            // SourceCodeProviderType::CODEBERG => new CodebergProvider(),
            default => throw new InvalidArgumentException("Provider {$type->value} not implemented yet"),
        };
    }

    public function getSupportedProviders(): array
    {
        return SourceCodeProviderType::implementedProviders();
    }

    public function getAllProviders(): array
    {
        return SourceCodeProviderType::allProviders();
    }
}
