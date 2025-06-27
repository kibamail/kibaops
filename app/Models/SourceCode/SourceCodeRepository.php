<?php

namespace App\Models\SourceCode;

use App\Traits\HasUuid;
use Database\Factories\SourceCodeRepositoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Source Code Repository Model
 *
 * Represents a repository from any source code provider in a normalized format.
 * Stores repository metadata, clone URLs, and webhook configuration status.
 * Provider-specific data is stored in the repository_metadata JSON field.
 *
 * @property string $id
 * @property string $source_code_connection_id
 * @property string $external_repository_id
 * @property string $name
 * @property string $owner_repo
 * @property string|null $description
 * @property string $visibility
 * @property string $default_branch
 * @property array $clone_urls
 * @property string $web_url
 * @property string|null $language
 * @property array|null $topics
 * @property bool $archived
 * @property bool $fork
 * @property array|null $repository_metadata
 * @property bool $webhook_configured
 * @property \Carbon\Carbon|null $last_activity_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read SourceCodeConnection $sourceCodeConnection
 * @property-read \Illuminate\Database\Eloquent\Collection<SourceCodeWebhookEvent> $webhookEvents
 * @property-read string $provider_type
 * @property-read mixed $workspace
 * @property-read string|null $clone_url
 * @property-read string|null $ssh_url
 */
class SourceCodeRepository extends Model
{
    use HasFactory, HasUuid;

    protected static function newFactory()
    {
        return SourceCodeRepositoryFactory::new();
    }

    protected $fillable = [
        'source_code_connection_id',
        'external_repository_id',
        'name',
        'owner_repo',
        'description',
        'visibility',
        'default_branch',
        'clone_urls',
        'web_url',
        'language',
        'topics',
        'archived',
        'fork',
        'repository_metadata',
        'webhook_configured',
        'last_activity_at',
    ];

    protected $casts = [
        'clone_urls' => 'array',
        'topics' => 'array',
        'repository_metadata' => 'array',
        'archived' => 'boolean',
        'fork' => 'boolean',
        'webhook_configured' => 'boolean',
        'last_activity_at' => 'datetime',
    ];

    /**
     * Get the source code connection that owns this repository.
     */
    public function sourceCodeConnection(): BelongsTo
    {
        return $this->belongsTo(SourceCodeConnection::class, 'source_code_connection_id');
    }

    /**
     * Get all webhook events received for this repository.
     */
    public function webhookEvents(): HasMany
    {
        return $this->hasMany(SourceCodeWebhookEvent::class);
    }

    /**
     * Get the provider type for this repository.
     *
     * @return string The provider type (github, bitbucket, etc.)
     */
    public function getProviderTypeAttribute(): string
    {
        return $this->sourceCodeConnection->provider_type->value;
    }

    /**
     * Get the workspace that owns this repository through the connection.
     *
     * @return mixed The workspace model
     */
    public function getWorkspaceAttribute()
    {
        return $this->sourceCodeConnection->workspace;
    }

    /**
     * Get the primary HTTPS clone URL for this repository.
     *
     * @return string|null The HTTPS clone URL or null if not available
     */
    public function getCloneUrlAttribute(): ?string
    {
        return $this->clone_urls['https'] ?? $this->clone_urls['git'] ?? null;
    }

    /**
     * Get the SSH clone URL for this repository.
     *
     * @return string|null The SSH clone URL or null if not available
     */
    public function getSshUrlAttribute(): ?string
    {
        return $this->clone_urls['ssh'] ?? null;
    }

    /**
     * Check if this repository is private.
     *
     * @return bool True if the repository is private
     */
    public function isPrivate(): bool
    {
        return $this->visibility === 'private';
    }

    /**
     * Check if this repository is public.
     *
     * @return bool True if the repository is public
     */
    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    /**
     * Check if this repository is archived.
     *
     * @return bool True if the repository is archived
     */
    public function isArchived(): bool
    {
        return $this->archived;
    }

    /**
     * Check if this repository is a fork.
     *
     * @return bool True if the repository is a fork
     */
    public function isFork(): bool
    {
        return $this->fork;
    }

    /**
     * Check if webhooks are configured for this repository.
     *
     * @return bool True if webhooks are configured
     */
    public function hasWebhookConfigured(): bool
    {
        return $this->webhook_configured;
    }

    /**
     * Mark webhooks as configured for this repository.
     */
    public function markWebhookConfigured(): void
    {
        $this->update(['webhook_configured' => true]);
    }

    /**
     * Mark webhooks as not configured for this repository.
     */
    public function markWebhookNotConfigured(): void
    {
        $this->update(['webhook_configured' => false]);
    }

    /**
     * Update the last activity timestamp to now.
     */
    public function updateLastActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Scope query to only active (non-archived) repositories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('archived', false);
    }

    /**
     * Scope query to only private repositories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePrivate($query)
    {
        return $query->where('visibility', 'private');
    }

    /**
     * Scope query to only public repositories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    /**
     * Scope query to repositories with webhooks configured.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithWebhooks($query)
    {
        return $query->where('webhook_configured', true);
    }

    /**
     * Scope query to repositories without webhooks configured.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutWebhooks($query)
    {
        return $query->where('webhook_configured', false);
    }

    /**
     * Scope query to repositories for a specific connection.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForConnection($query, string $connectionId)
    {
        return $query->where('source_code_connection_id', $connectionId);
    }

    /**
     * Scope query to repositories by owner/repo format.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByOwnerRepo($query, string $ownerRepo)
    {
        return $query->where('owner_repo', $ownerRepo);
    }

    /**
     * Scope query to repositories by programming language.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByLanguage($query, string $language)
    {
        return $query->where('language', $language);
    }

    /**
     * Scope query to repositories with recent activity.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $days  Number of days to look back (default: 30)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecentActivity($query, int $days = 30)
    {
        return $query->where('last_activity_at', '>=', now()->subDays($days));
    }
}
