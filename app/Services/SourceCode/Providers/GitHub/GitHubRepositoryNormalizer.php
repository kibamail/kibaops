<?php

namespace App\Services\SourceCode\Providers\GitHub;

use Carbon\Carbon;

/**
 * GitHub Repository Normalizer
 *
 * Normalizes GitHub API repository data to the format expected by
 * the SourceCodeRepository model for database storage.
 */
class GitHubRepositoryNormalizer
{
    /**
     * Normalize a GitHub repository array to SourceCodeRepository format.
     *
     * @param  array  $githubRepo  The GitHub repository data from API
     * @return array Normalized repository data ready for database storage
     */
    public static function normalize(array $githubRepo): array
    {
        return [
            'external_repository_id' => (string) $githubRepo['id'],
            'name' => $githubRepo['name'],
            'owner_repo' => $githubRepo['full_name'],
            'description' => $githubRepo['description'],
            'visibility' => $githubRepo['visibility'] ?? ($githubRepo['private'] ? 'private' : 'public'),
            'default_branch' => $githubRepo['default_branch'] ?? 'main',
            'clone_urls' => self::extractCloneUrls($githubRepo),
            'web_url' => $githubRepo['html_url'],
            'language' => $githubRepo['language'],
            'topics' => $githubRepo['topics'] ?? [],
            'archived' => $githubRepo['archived'] ?? false,
            'fork' => $githubRepo['fork'] ?? false,
            'repository_metadata' => self::extractMetadata($githubRepo),
            'webhook_configured' => false,
            'last_activity_at' => self::parseLastActivity($githubRepo),
        ];
    }

    /**
     * Extract clone URLs from GitHub repository data.
     *
     * @param  array  $githubRepo  The GitHub repository data
     * @return array Array of clone URLs indexed by protocol
     */
    private static function extractCloneUrls(array $githubRepo): array
    {
        $urls = [];

        if (isset($githubRepo['clone_url'])) {
            $urls['https'] = $githubRepo['clone_url'];
        }

        if (isset($githubRepo['ssh_url'])) {
            $urls['ssh'] = $githubRepo['ssh_url'];
        }

        if (isset($githubRepo['git_url'])) {
            $urls['git'] = $githubRepo['git_url'];
        }

        if (isset($githubRepo['svn_url'])) {
            $urls['svn'] = $githubRepo['svn_url'];
        }

        return $urls;
    }

    /**
     * Extract GitHub-specific metadata for storage.
     *
     * @param  array  $githubRepo  The GitHub repository data
     * @return array GitHub-specific metadata
     */
    private static function extractMetadata(array $githubRepo): array
    {
        return [
            'node_id' => $githubRepo['node_id'] ?? null,
            'size' => $githubRepo['size'] ?? null,
            'stargazers_count' => $githubRepo['stargazers_count'] ?? 0,
            'watchers_count' => $githubRepo['watchers_count'] ?? 0,
            'forks_count' => $githubRepo['forks_count'] ?? 0,
            'open_issues_count' => $githubRepo['open_issues_count'] ?? 0,
            'has_issues' => $githubRepo['has_issues'] ?? false,
            'has_projects' => $githubRepo['has_projects'] ?? false,
            'has_downloads' => $githubRepo['has_downloads'] ?? false,
            'has_wiki' => $githubRepo['has_wiki'] ?? false,
            'has_pages' => $githubRepo['has_pages'] ?? false,
            'has_discussions' => $githubRepo['has_discussions'] ?? false,
            'license' => $githubRepo['license'],
            'allow_forking' => $githubRepo['allow_forking'] ?? false,
            'is_template' => $githubRepo['is_template'] ?? false,
            'web_commit_signoff_required' => $githubRepo['web_commit_signoff_required'] ?? false,
            'disabled' => $githubRepo['disabled'] ?? false,
            'mirror_url' => $githubRepo['mirror_url'],
            'homepage' => $githubRepo['homepage'],
            'owner' => self::extractOwnerData($githubRepo['owner'] ?? []),
            'api_urls' => self::extractApiUrls($githubRepo),
        ];
    }

    /**
     * Extract owner information from GitHub repository data.
     *
     * @param  array  $owner  The owner data from GitHub API
     * @return array Simplified owner information
     */
    private static function extractOwnerData(array $owner): array
    {
        return [
            'id' => $owner['id'] ?? null,
            'login' => $owner['login'] ?? null,
            'type' => $owner['type'] ?? null,
            'avatar_url' => $owner['avatar_url'] ?? null,
            'html_url' => $owner['html_url'] ?? null,
        ];
    }

    /**
     * Extract relevant API URLs from GitHub repository data.
     *
     * @param  array  $githubRepo  The GitHub repository data
     * @return array Relevant API URLs
     */
    private static function extractApiUrls(array $githubRepo): array
    {
        return [
            'url' => $githubRepo['url'] ?? null,
            'hooks_url' => $githubRepo['hooks_url'] ?? null,
            'contents_url' => $githubRepo['contents_url'] ?? null,
            'commits_url' => $githubRepo['commits_url'] ?? null,
            'branches_url' => $githubRepo['branches_url'] ?? null,
            'tags_url' => $githubRepo['tags_url'] ?? null,
            'releases_url' => $githubRepo['releases_url'] ?? null,
            'issues_url' => $githubRepo['issues_url'] ?? null,
            'pulls_url' => $githubRepo['pulls_url'] ?? null,
        ];
    }

    /**
     * Parse the last activity timestamp from GitHub repository data.
     *
     * @param  array  $githubRepo  The GitHub repository data
     * @return Carbon|null The last activity timestamp or null
     */
    private static function parseLastActivity(array $githubRepo): ?Carbon
    {
        $pushedAt = $githubRepo['pushed_at'] ?? null;
        $updatedAt = $githubRepo['updated_at'] ?? null;

        $lastActivity = $pushedAt ?: $updatedAt;

        return $lastActivity ? Carbon::parse($lastActivity) : null;
    }

    /**
     * Normalize multiple GitHub repositories.
     *
     * @param  array  $githubRepos  Array of GitHub repository data
     * @return array Array of normalized repository data
     */
    public static function normalizeMany(array $githubRepos): array
    {
        return array_map(
            fn ($repo) => self::normalize($repo),
            $githubRepos
        );
    }

    /**
     * Extract repository data from GitHub Apps API response.
     *
     * @param  array  $appsResponse  The response from GitHub Apps API
     * @return array Array of normalized repository data
     */
    public static function normalizeFromAppsResponse(array $appsResponse): array
    {
        $repositories = $appsResponse['repositories'] ?? [];

        return self::normalizeMany($repositories);
    }
}
