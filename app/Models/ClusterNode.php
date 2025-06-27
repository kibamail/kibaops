<?php

namespace App\Models;

use App\Enums\ClusterNodeType;
use App\Enums\ClusterStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ClusterNode Model
 *
 * Represents an individual node within a Nomad cluster. Nodes are the
 * fundamental compute units that run workloads and/or handle storage operations.
 *
 * Node Types:
 * - Worker: Handles computational workloads and application processing
 * - Storage: Dedicated to data persistence (only in non-shared configurations)
 *
 * Key Features:
 * - Unique node identifiers following {cluster-slug}-{type}-{number} pattern
 * - Support for both IPv4 and IPv6 addressing
 * - Status tracking for health monitoring
 * - Flexible IP assignment (populated during provisioning)
 *
 * @property string $id UUID primary key
 * @property string $cluster_id Parent cluster UUID
 * @property string $node_id Unique human-readable identifier within cluster
 * @property ClusterNodeType $type Node role (worker or storage)
 * @property ClusterStatus $status Current node health status
 * @property string|null $public_ip Public IPv4 address (assigned during provisioning)
 * @property string|null $private_ip Private IPv4 address (assigned during provisioning)
 * @property string|null $public_ipv6 Public IPv6 address (assigned during provisioning)
 * @property string|null $private_ipv6 Private IPv6 address (assigned during provisioning)
 * @property string|null $server_type Cloud provider server type identifier (e.g., 'cx32', 't3.medium')
 * @property int $cpu_cores Number of CPU cores allocated to this node
 * @property int $ram_gb Amount of RAM in gigabytes allocated to this node
 * @property int $disk_gb Amount of disk storage in gigabytes allocated to this node
 * @property string $os Operating system identifier (default: 'ubuntu-24.04')
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read Cluster $cluster
 */
class ClusterNode extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'cluster_id',
        'node_id',
        'type',
        'status',
        'public_ip',
        'private_ip',
        'public_ipv6',
        'private_ipv6',
        'server_type',
        'cpu_cores',
        'ram_gb',
        'disk_gb',
        'os',
    ];

    protected $casts = [
        'type' => ClusterNodeType::class,
        'status' => ClusterStatus::class,
        'cpu_cores' => 'integer',
        'ram_gb' => 'integer',
        'disk_gb' => 'integer',
    ];

    /**
     * Get the cluster that owns this node.
     *
     * Provides access to the parent cluster for relationship queries
     * and cluster-wide operations.
     */
    public function cluster(): BelongsTo
    {
        return $this->belongsTo(Cluster::class);
    }

    /**
     * Check if this node is a worker node.
     *
     * Worker nodes handle computational workloads and application processing.
     * In shared configurations, they also handle storage responsibilities.
     */
    public function isWorker(): bool
    {
        return $this->type === ClusterNodeType::WORKER;
    }

    /**
     * Check if this node is a storage node.
     *
     * Storage nodes are dedicated to data persistence and storage operations.
     * Only present in traditional (non-shared) cluster configurations.
     */
    public function isStorage(): bool
    {
        return $this->type === ClusterNodeType::STORAGE;
    }

    /**
     * Check if this node is currently healthy.
     *
     * Healthy nodes are fully operational and ready to handle workloads.
     * This status is updated during cluster monitoring and health checks.
     */
    public function isHealthy(): bool
    {
        return $this->status === ClusterStatus::HEALTHY;
    }

    /**
     * Check if this node is in pending status.
     *
     * Pending nodes are being provisioned or are waiting for configuration.
     * This is the default status for newly created nodes.
     */
    public function isPending(): bool
    {
        return $this->status === ClusterStatus::PENDING;
    }

    /**
     * Check if this node is unhealthy.
     *
     * Unhealthy nodes have failed health checks or are experiencing issues.
     * These nodes may need intervention or replacement.
     */
    public function isUnhealthy(): bool
    {
        return $this->status === ClusterStatus::UNHEALTHY;
    }

    /**
     * Get a human-readable description of the node's specifications.
     *
     * Returns a formatted string describing the node's CPU, RAM, and disk
     * specifications for display purposes.
     */
    public function getSpecsDescription(): string
    {
        return "{$this->cpu_cores} vCPU, {$this->ram_gb}GB RAM, {$this->disk_gb}GB Disk";
    }

    /**
     * Get the total resource capacity for this node.
     *
     * Returns an array with the node's resource specifications for
     * capacity planning and resource allocation calculations.
     */
    public function getResourceCapacity(): array
    {
        return [
            'cpu_cores' => $this->cpu_cores,
            'ram_gb' => $this->ram_gb,
            'disk_gb' => $this->disk_gb,
        ];
    }

    /**
     * Check if this node has sufficient resources for a workload.
     *
     * Compares the node's specifications against required resources
     * to determine if it can handle a specific workload.
     */
    public function canHandleWorkload(int $requiredCpu, int $requiredRam, int $requiredDisk): bool
    {
        return $this->cpu_cores >= $requiredCpu &&
               $this->ram_gb >= $requiredRam &&
               $this->disk_gb >= $requiredDisk;
    }
}
