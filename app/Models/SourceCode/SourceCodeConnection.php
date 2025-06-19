<?php

namespace App\Models\SourceCode;

use App\Enums\SourceCodeProviderType;
use App\Models\Workspace;
use App\Traits\HasUuid;
use Database\Factories\SourceCodeConnectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Source Code Connection Model
 *
 * Represents a connection between a workspace and a source code provider
 * (GitHub, Bitbucket, GitLab, etc.). Manages authentication credentials
 * and provider-specific metadata in a provider-agnostic way.
 *
 * @property string $id
 * @property string $workspace_id
 * @property SourceCodeProviderType $provider_type
 * @property string $connection_name
 * @property string $external_account_id
 * @property string $external_account_name
 * @property string $external_account_type
 * @property string|null $avatar_url
 * @property array|null $permissions_scope
 * @property string $vault_credentials_path
 * @property string $connection_status
 * @property \Carbon\Carbon|null $last_sync_at
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read Workspace $workspace
 * @property-read \Illuminate\Database\Eloquent\Collection<SourceCodeRepository> $repositories
 * @property-read \Illuminate\Database\Eloquent\Collection<SourceCodeWebhookEvent> $webhookEvents
 * @property-read string $vault_key
 */
class SourceCodeConnection extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected static function newFactory()
    {
        return SourceCodeConnectionFactory::new();
    }

    protected $fillable = [
        'workspace_id',
        'provider_type',
        'connection_name',
        'external_account_id',
        'external_account_name',
        'external_account_type',
        'avatar_url',
        'permissions_scope',
        'vault_credentials_path',
        'connection_status',
        'last_sync_at',
        'metadata',
    ];

    protected $casts = [
        'provider_type' => SourceCodeProviderType::class,
        'permissions_scope' => 'array',
        'metadata' => 'array',
        'last_sync_at' => 'datetime',
    ];

    /**
     * Get the workspace that owns this source code connection.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get all repositories associated with this connection.
     */
    public function repositories(): HasMany
    {
        return $this->hasMany(SourceCodeRepository::class);
    }

    /**
     * Get all webhook events received for this connection.
     */
    public function webhookEvents(): HasMany
    {
        return $this->hasMany(SourceCodeWebhookEvent::class);
    }

    /**
     * Generate the Vault key path for storing credentials.
     */
    public function getVaultKeyAttribute(): string
    {
        return "source-code/{$this->provider_type->value}/{$this->id}";
    }

    /**
     * Store authentication credentials securely in Vault.
     *
     * @param  array  $credentials  The credentials to store (access_token, refresh_token, etc.)
     */
    public function storeCredentials(array $credentials): void
    {
        app('vault')->writes()->store($this->vault_key, $credentials);

        $this->update([
            'vault_credentials_path' => $this->vault_key,
        ]);
    }

    /**
     * Retrieve authentication credentials from Vault.
     *
     * @return array The stored credentials or empty array if none found
     */
    public function getCredentials(): array
    {
        if (empty($this->vault_credentials_path)) {
            return [];
        }

        $result = safe(function () {
            return app('vault')->reads()->secret($this->vault_credentials_path);
        });

        return $result['error'] === null ? $result['data'] : [];
    }

    /**
     * Check if the connection has valid authentication credentials.
     *
     * @return bool True if valid access token exists
     */
    public function hasValidCredentials(): bool
    {
        $credentials = $this->getCredentials();

        return ! empty($credentials) &&
               isset($credentials['access_token']) &&
               ! empty($credentials['access_token']);
    }

    /**
     * Mark the connection as expired (credentials no longer valid).
     */
    public function markAsExpired(): void
    {
        $this->update(['connection_status' => 'expired']);
    }

    /**
     * Mark the connection as active (credentials are valid).
     */
    public function markAsActive(): void
    {
        $this->update(['connection_status' => 'active']);
    }

    /**
     * Mark the connection as revoked (user revoked access).
     */
    public function markAsRevoked(): void
    {
        $this->update(['connection_status' => 'revoked']);
    }

    /**
     * Mark the connection as having an error.
     */
    public function markAsError(): void
    {
        $this->update(['connection_status' => 'error']);
    }

    /**
     * Update the last synchronization timestamp to now.
     */
    public function updateLastSync(): void
    {
        $this->update(['last_sync_at' => now()]);
    }

    /**
     * Scope query to only active connections.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('connection_status', 'active');
    }

    /**
     * Scope query to connections for a specific provider type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForProvider($query, SourceCodeProviderType $provider)
    {
        return $query->where('provider_type', $provider);
    }

    /**
     * Scope query to connections for a specific workspace.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForWorkspace($query, string $workspaceId)
    {
        return $query->where('workspace_id', $workspaceId);
    }
}
