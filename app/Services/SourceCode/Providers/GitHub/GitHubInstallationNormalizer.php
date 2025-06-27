<?php

namespace App\Services\SourceCode\Providers\GitHub;

use App\Enums\SourceCodeProviderType;

/**
 * GitHub Installation Normalizer
 *
 * Normalizes GitHub Apps installation data to the format expected by
 * the SourceCodeConnection model for database storage.
 */
class GitHubInstallationNormalizer
{
    /**
     * Normalize a GitHub installation array to SourceCodeConnection format.
     *
     * @param  array  $installation  The GitHub installation data from getInstallation()
     * @return array Normalized connection data ready for database storage
     */
    public static function normalize(array $installation): array
    {
        $account = $installation['account'] ?? [];

        return [
            'provider_type' => SourceCodeProviderType::GITHUB->value,
            'connection_name' => self::generateConnectionName($account),
            'external_account_id' => (string) $installation['target_id'],
            'external_account_name' => $account['login'] ?? 'Unknown',
            'external_account_type' => strtolower($installation['target_type'] ?? 'user'),
            'avatar_url' => $account['avatar_url'] ?? null,
            'permissions_scope' => self::extractPermissions($installation),
            'vault_credentials_path' => null,
            'connection_status' => 'active',
            'last_sync_at' => null,
            'metadata' => self::extractMetadata($installation),
        ];
    }

    /**
     * Generate a connection name based on account information.
     *
     * @param  array  $account  The account data from installation
     * @return string Generated connection name
     */
    private static function generateConnectionName(array $account): string
    {
        $login = $account['login'] ?? 'Unknown';
        $type = $account['type'] ?? 'User';

        return "{$login} ({$type})";
    }

    /**
     * Extract permissions from GitHub installation data.
     *
     * @param  array  $installation  The GitHub installation data
     * @return array Array of permissions
     */
    private static function extractPermissions(array $installation): array
    {
        $permissions = $installation['permissions'] ?? [];
        $repositorySelection = $installation['repository_selection'] ?? 'selected';

        return [
            'repository_selection' => $repositorySelection,
            'permissions' => $permissions,
            'events' => $installation['events'] ?? [],
        ];
    }

    /**
     * Extract GitHub-specific metadata for storage.
     *
     * @param  array  $installation  The GitHub installation data
     * @return array GitHub-specific metadata
     */
    private static function extractMetadata(array $installation): array
    {
        $account = $installation['account'] ?? [];

        return [
            'installation_id' => $installation['id'],
            'app_id' => $installation['app_id'],
            'app_slug' => $installation['app_slug'] ?? null,
            'client_id' => $installation['client_id'] ?? null,
            'repository_selection' => $installation['repository_selection'] ?? 'selected',
            'access_tokens_url' => $installation['access_tokens_url'] ?? null,
            'repositories_url' => $installation['repositories_url'] ?? null,
            'html_url' => $installation['html_url'] ?? null,
            'single_file_name' => $installation['single_file_name'],
            'has_multiple_single_files' => $installation['has_multiple_single_files'] ?? false,
            'single_file_paths' => $installation['single_file_paths'] ?? [],
            'suspended_by' => $installation['suspended_by'],
            'suspended_at' => $installation['suspended_at'],
            'created_at' => $installation['created_at'],
            'updated_at' => $installation['updated_at'],
            'account' => [
                'id' => $account['id'] ?? null,
                'login' => $account['login'] ?? null,
                'node_id' => $account['node_id'] ?? null,
                'type' => $account['type'] ?? null,
                'user_view_type' => $account['user_view_type'] ?? null,
                'site_admin' => $account['site_admin'] ?? false,
                'url' => $account['url'] ?? null,
                'html_url' => $account['html_url'] ?? null,
                'followers_url' => $account['followers_url'] ?? null,
                'following_url' => $account['following_url'] ?? null,
                'gists_url' => $account['gists_url'] ?? null,
                'starred_url' => $account['starred_url'] ?? null,
                'subscriptions_url' => $account['subscriptions_url'] ?? null,
                'organizations_url' => $account['organizations_url'] ?? null,
                'repos_url' => $account['repos_url'] ?? null,
                'events_url' => $account['events_url'] ?? null,
                'received_events_url' => $account['received_events_url'] ?? null,
                'gravatar_id' => $account['gravatar_id'] ?? null,
            ],
        ];
    }

    /**
     * Check if the installation is suspended.
     *
     * @param  array  $installation  The GitHub installation data
     * @return bool True if the installation is suspended
     */
    public static function isSuspended(array $installation): bool
    {
        return ! empty($installation['suspended_at']);
    }

    /**
     * Check if the installation has access to all repositories.
     *
     * @param  array  $installation  The GitHub installation data
     * @return bool True if installation has access to all repositories
     */
    public static function hasAllRepositories(array $installation): bool
    {
        return ($installation['repository_selection'] ?? 'selected') === 'all';
    }

    /**
     * Get the installation ID from the normalized data or raw installation.
     *
     * @param  array  $installation  The GitHub installation data
     * @return int The installation ID
     */
    public static function getInstallationId(array $installation): int
    {
        return $installation['id'] ?? $installation['installation_id'] ?? 0;
    }

    /**
     * Get the target account login from the installation data.
     *
     * @param  array  $installation  The GitHub installation data
     * @return string The account login
     */
    public static function getAccountLogin(array $installation): string
    {
        return $installation['account']['login'] ?? 'Unknown';
    }
}
