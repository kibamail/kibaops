<?php

namespace App\Models;

use App\Enums\ClusterNodeType;
use App\Enums\ClusterStatus;
use App\Services\Clusters\ClusterNodeGenerator;
use App\Services\SshKeyGenerator;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Cluster Model
 *
 * Represents a Nomad cluster within a workspace. Clusters are the primary
 * infrastructure units that contain multiple nodes for running workloads.
 *
 * Key Features:
 * - Auto-generates unique slugs within workspace scope
 * - Creates and manages SSH key pairs stored in Vault
 * - Supports both traditional (separate worker/storage) and shared node configurations
 * - Automatically creates cluster nodes based on configuration
 * - Tracks cluster health and status
 *
 * Relationships:
 * - Belongs to a workspace (multi-tenant isolation)
 * - Belongs to a cloud provider (for provisioning)
 * - Has many cluster nodes (worker and/or storage)
 *
 * @property string $id UUID primary key
 * @property string $name Human-readable cluster name
 * @property string $slug URL-friendly unique identifier within workspace
 * @property ClusterStatus $status Current cluster health status
 * @property string $workspace_id Parent workspace UUID
 * @property string $cloud_provider_id Target cloud provider UUID
 * @property string $region Cloud provider region for deployment
 * @property bool $shared_storage_worker_nodes Whether nodes handle both storage and worker roles
 * @property string|null $vault_ssh_key_path Path to SSH keys in Vault
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read Workspace $workspace
 * @property-read CloudProvider $cloudProvider
 * @property-read \Illuminate\Database\Eloquent\Collection|ClusterNode[] $nodes
 * @property-read \Illuminate\Database\Eloquent\Collection|ClusterNode[] $workerNodes
 * @property-read \Illuminate\Database\Eloquent\Collection|ClusterNode[] $storageNodes
 * @property-read int $worker_nodes_count
 * @property-read int $storage_nodes_count
 * @property-read int $total_nodes_count
 * @property-read string $vault_key
 */
class Cluster extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name',
        'slug',
        'status',
        'workspace_id',
        'cloud_provider_id',
        'region',
        'shared_storage_worker_nodes',
        'vault_ssh_key_path',
    ];

    protected $casts = [
        'status' => ClusterStatus::class,
        'shared_storage_worker_nodes' => 'boolean',
    ];

    /**
     * Boot the model and register event listeners.
     *
     * Automatically handles cluster initialization during creation:
     * - Generates unique slug within workspace scope
     * - Creates and stores SSH key pairs in Vault
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cluster) {
            if (! $cluster->slug) {
                $baseSlug = Str::slug($cluster->name);
                $count = static::where('workspace_id', $cluster->workspace_id)
                    ->where('slug', 'like', "{$baseSlug}%")
                    ->count();

                if ($count > 0) {
                    $cluster->slug = $baseSlug . '-' . Str::random(4);
                } else {
                    $cluster->slug = $baseSlug;
                }
            }

            $cluster->generateAndStoreSSHKeys();
        });
    }

    /**
     * Get the workspace that owns this cluster.
     *
     * Provides multi-tenant isolation ensuring clusters belong to specific workspaces.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get the cloud provider used for this cluster.
     *
     * Defines the target infrastructure provider (AWS, Hetzner, etc.) where
     * this cluster will be provisioned and managed.
     */
    public function cloudProvider(): BelongsTo
    {
        return $this->belongsTo(CloudProvider::class);
    }

    /**
     * Get all nodes belonging to this cluster.
     *
     * Returns both worker and storage nodes regardless of cluster configuration.
     * Useful for operations that need to work with all cluster infrastructure.
     */
    public function nodes(): HasMany
    {
        return $this->hasMany(ClusterNode::class);
    }

    /**
     * Get only the worker nodes for this cluster.
     *
     * Worker nodes handle computational workloads and application processing.
     * In shared configurations, these nodes also handle storage responsibilities.
     */
    public function workerNodes(): HasMany
    {
        return $this->hasMany(ClusterNode::class)->where('type', ClusterNodeType::WORKER);
    }

    /**
     * Get only the storage nodes for this cluster.
     *
     * Storage nodes are dedicated to data persistence and storage operations.
     * Only present in traditional (non-shared) cluster configurations.
     */
    public function storageNodes(): HasMany
    {
        return $this->hasMany(ClusterNode::class)->where('type', ClusterNodeType::STORAGE);
    }

    /**
     * Get the current count of worker nodes.
     *
     * Dynamically calculates the number of worker nodes by querying the
     * relationship. This ensures the count is always accurate even if
     * nodes are added or removed outside of the standard workflow.
     */
    public function getWorkerNodesCountAttribute(): int
    {
        return $this->workerNodes()->count();
    }

    /**
     * Get the current count of storage nodes.
     *
     * Dynamically calculates the number of storage nodes. Will return 0
     * for shared storage/worker clusters since they don't have dedicated
     * storage nodes.
     */
    public function getStorageNodesCountAttribute(): int
    {
        return $this->storageNodes()->count();
    }

    /**
     * Get the total count of all nodes in this cluster.
     *
     * Provides a quick way to get the complete node count regardless
     * of node types or cluster configuration.
     */
    public function getTotalNodesCountAttribute(): int
    {
        return $this->nodes()->count();
    }

    /**
     * Get the Vault path for this cluster's SSH keys.
     *
     * Generates the standardized Vault path where SSH keys are stored
     * for secure access to cluster nodes during provisioning and management.
     */
    public function getVaultKeyAttribute(): string
    {
        return "clusters/{$this->id}/ssh-keys";
    }

    /**
     * Generate and store SSH key pairs for cluster access.
     *
     * Creates ED25519 SSH key pairs and securely stores them in Vault for
     * later use during cluster provisioning and node management. The keys
     * are generated with a cluster-specific identifier for easy identification.
     */
    public function generateAndStoreSSHKeys(): void
    {
        $sshKeys = SshKeyGenerator::ed25519pair("cluster-{$this->slug}@kibaops.com");

        $this->workspace->vault()->writes()->store($this->vault_key, $sshKeys);

        $this->vault_ssh_key_path = $this->vault_key;
    }

    /**
     * Create cluster nodes based on the specified configuration.
     *
     * Delegates node creation to the ClusterNodeGenerator service for clean
     * separation of concerns. Handles both traditional (separate worker/storage)
     * and shared storage/worker node configurations automatically.
     *
     * @param  int  $workerNodesCount  Number of worker nodes to create (minimum 3)
     * @param  int  $storageNodesCount  Number of storage nodes to create (minimum 3 for non-shared clusters)
     * @param  string  $serverType  Cloud provider server type identifier
     */
    public function createNodes(int $workerNodesCount, int $storageNodesCount = 0, string $serverType = ''): void
    {
        ClusterNodeGenerator::createNodes($this, $workerNodesCount, $storageNodesCount, $serverType);
    }
}
