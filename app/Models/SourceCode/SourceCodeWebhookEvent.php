<?php

namespace App\Models\SourceCode;

use App\Traits\HasUuid;
use Database\Factories\SourceCodeWebhookEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Source Code Webhook Event Model
 *
 * Represents webhook events received from source code providers in a normalized format.
 * Stores both the original provider payload and a normalized version for consistent
 * processing across different providers. Tracks processing status and retry attempts.
 *
 * @property string $id
 * @property string $source_code_connection_id
 * @property string|null $source_code_repository_id
 * @property string|null $external_event_id
 * @property string $event_type
 * @property string|null $event_action
 * @property string|null $branch_name
 * @property string|null $commit_sha
 * @property array $payload
 * @property array|null $normalized_payload
 * @property \Carbon\Carbon|null $processed_at
 * @property string $processing_status
 * @property int $processing_attempts
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read SourceCodeConnection $sourceCodeConnection
 * @property-read SourceCodeRepository|null $repository
 * @property-read string $provider_type
 * @property-read mixed $workspace
 */
class SourceCodeWebhookEvent extends Model
{
    use HasFactory, HasUuid;

    protected static function newFactory()
    {
        return SourceCodeWebhookEventFactory::new();
    }

    protected $fillable = [
        'source_code_connection_id',
        'source_code_repository_id',
        'external_event_id',
        'event_type',
        'event_action',
        'branch_name',
        'commit_sha',
        'payload',
        'normalized_payload',
        'processed_at',
        'processing_status',
        'processing_attempts',
    ];

    protected $casts = [
        'payload' => 'array',
        'normalized_payload' => 'array',
        'processed_at' => 'datetime',
        'processing_attempts' => 'integer',
    ];

    /**
     * Get the source code connection that received this webhook event.
     */
    public function sourceCodeConnection(): BelongsTo
    {
        return $this->belongsTo(SourceCodeConnection::class, 'source_code_connection_id');
    }

    /**
     * Get the repository associated with this webhook event.
     */
    public function repository(): BelongsTo
    {
        return $this->belongsTo(SourceCodeRepository::class, 'source_code_repository_id');
    }

    /**
     * Get the provider type for this webhook event.
     *
     * @return string The provider type (github, bitbucket, etc.)
     */
    public function getProviderTypeAttribute(): string
    {
        return $this->sourceCodeConnection->provider_type->value;
    }

    /**
     * Get the workspace that owns this webhook event through the connection.
     *
     * @return mixed The workspace model
     */
    public function getWorkspaceAttribute()
    {
        return $this->sourceCodeConnection->workspace;
    }

    /**
     * Check if this webhook event is pending processing.
     *
     * @return bool True if the event is pending
     */
    public function isPending(): bool
    {
        return $this->processing_status === 'pending';
    }

    /**
     * Check if this webhook event is currently being processed.
     *
     * @return bool True if the event is being processed
     */
    public function isProcessing(): bool
    {
        return $this->processing_status === 'processing';
    }

    /**
     * Check if this webhook event has been completed.
     *
     * @return bool True if the event has been completed
     */
    public function isCompleted(): bool
    {
        return $this->processing_status === 'completed';
    }

    /**
     * Check if this webhook event processing has failed.
     *
     * @return bool True if the event processing failed
     */
    public function isFailed(): bool
    {
        return $this->processing_status === 'failed';
    }

    /**
     * Mark this webhook event as currently being processed.
     * Increments the processing attempts counter.
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'processing_status' => 'processing',
            'processing_attempts' => $this->processing_attempts + 1,
        ]);
    }

    /**
     * Mark this webhook event as completed successfully.
     * Sets the processed_at timestamp.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'processing_status' => 'completed',
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark this webhook event as failed.
     * Increments the processing attempts counter.
     */
    public function markAsFailed(): void
    {
        $this->update([
            'processing_status' => 'failed',
            'processing_attempts' => $this->processing_attempts + 1,
        ]);
    }

    /**
     * Increment the processing attempts counter.
     */
    public function incrementAttempts(): void
    {
        $this->increment('processing_attempts');
    }

    /**
     * Check if this webhook event has exceeded the maximum processing attempts.
     *
     * @param  int  $maxAttempts  Maximum allowed attempts (default: 3)
     * @return bool True if max attempts exceeded
     */
    public function hasExceededMaxAttempts(int $maxAttempts = 3): bool
    {
        return $this->processing_attempts >= $maxAttempts;
    }

    /**
     * Set the normalized payload for this webhook event.
     *
     * @param  array  $normalizedPayload  The normalized payload data
     */
    public function setNormalizedPayload(array $normalizedPayload): void
    {
        $this->update(['normalized_payload' => $normalizedPayload]);
    }

    /**
     * Check if this is a push event.
     *
     * @return bool True if this is a push event
     */
    public function isPushEvent(): bool
    {
        return $this->event_type === 'push';
    }

    /**
     * Check if this is a pull/merge request event.
     *
     * @return bool True if this is a pull request or merge request event
     */
    public function isPullRequestEvent(): bool
    {
        return in_array($this->event_type, ['pull_request', 'merge_request']);
    }

    /**
     * Check if this is a release event.
     *
     * @return bool True if this is a release event
     */
    public function isReleaseEvent(): bool
    {
        return $this->event_type === 'release';
    }

    /**
     * Check if this is a tag event.
     *
     * @return bool True if this is a tag or tag_push event
     */
    public function isTagEvent(): bool
    {
        return in_array($this->event_type, ['tag', 'tag_push']);
    }

    /**
     * Scope query to pending webhook events.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('processing_status', 'pending');
    }

    /**
     * Scope query to webhook events currently being processed.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProcessing($query)
    {
        return $query->where('processing_status', 'processing');
    }

    /**
     * Scope query to completed webhook events.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('processing_status', 'completed');
    }

    /**
     * Scope query to failed webhook events.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('processing_status', 'failed');
    }

    /**
     * Scope query to webhook events for a specific connection.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForConnection($query, string $connectionId)
    {
        return $query->where('source_code_connection_id', $connectionId);
    }

    /**
     * Scope query to webhook events for a specific repository.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForRepository($query, string $repositoryId)
    {
        return $query->where('source_code_repository_id', $repositoryId);
    }

    /**
     * Scope query to webhook events by event type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope query to webhook events by branch name.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByBranch($query, string $branchName)
    {
        return $query->where('branch_name', $branchName);
    }

    /**
     * Scope query to recent webhook events.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $hours  Number of hours to look back (default: 24)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecentEvents($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope query to failed webhook events that can be retried.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $maxAttempts  Maximum attempts before giving up (default: 3)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRetryable($query, int $maxAttempts = 3)
    {
        return $query->where('processing_status', 'failed')
            ->where('processing_attempts', '<', $maxAttempts);
    }
}
