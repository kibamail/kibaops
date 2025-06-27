<?php

namespace App\Services\Clusters;

use App\Enums\ClusterNodeType;
use App\Enums\ClusterStatus;
use App\Models\Cluster;
use App\Models\ClusterNode;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * ClusterNodeGenerator
 *
 * Handles the generation and creation of cluster nodes based on cluster
 * configuration. Supports both traditional separate worker/storage nodes
 * and shared storage/worker node configurations.
 */
class ClusterNodeGenerator
{
    /**
     * Create and persist cluster nodes based on the cluster configuration.
     *
     * This method orchestrates the entire node creation process by:
     * 1. Generating worker nodes based on the specified count
     * 2. Conditionally generating storage nodes (only for non-shared clusters)
     * 3. Extracting server specifications from the cloud provider
     * 4. Bulk inserting all nodes for optimal database performance
     *
     * @param  Cluster  $cluster  The cluster to create nodes for
     * @param  int  $workerNodesCount  Number of worker nodes to create (minimum 3)
     * @param  int  $storageNodesCount  Number of storage nodes to create (minimum 3 for non-shared clusters)
     * @param  string  $serverType  Cloud provider server type identifier
     */
    public static function createNodes(Cluster $cluster, int $workerNodesCount, int $storageNodesCount = 0, string $serverType = ''): void
    {
        $nodes = collect()
            ->merge(self::generateWorkerNodes($cluster, $workerNodesCount, $serverType))
            ->when(
                ! $cluster->shared_storage_worker_nodes,
                fn (Collection $collection) => $collection->merge(self::generateStorageNodes($cluster, $storageNodesCount, $serverType))
            );

        ClusterNode::insert($nodes->toArray());
    }

    /**
     * Generate worker node data for the cluster.
     *
     * Worker nodes handle computational workloads and application processing.
     * In shared storage/worker configurations, these nodes also handle storage.
     * Each worker node gets a unique identifier following the pattern:
     * {cluster-slug}-worker-{number}
     *
     * @param  Cluster  $cluster  The cluster these nodes belong to
     * @param  int  $count  Number of worker nodes to generate
     * @param  string  $serverType  Cloud provider server type identifier
     * @return Collection Collection of worker node data arrays
     */
    private static function generateWorkerNodes(Cluster $cluster, int $count, string $serverType): Collection
    {
        return collect(range(1, $count))
            ->map(fn (int $nodeNumber) => self::createNodeData($cluster, 'worker', $nodeNumber, $serverType));
    }

    /**
     * Generate storage node data for the cluster.
     *
     * Storage nodes are dedicated to data persistence and storage operations.
     * These are only created for traditional cluster configurations where
     * storage and worker responsibilities are separated. Each storage node
     * gets a unique identifier following the pattern: {cluster-slug}-storage-{number}
     *
     * @param  Cluster  $cluster  The cluster these nodes belong to
     * @param  int  $count  Number of storage nodes to generate
     * @param  string  $serverType  Cloud provider server type identifier
     * @return Collection Collection of storage node data arrays
     */
    private static function generateStorageNodes(Cluster $cluster, int $count, string $serverType): Collection
    {
        return collect(range(1, $count))
            ->map(fn (int $nodeNumber) => self::createNodeData($cluster, 'storage', $nodeNumber, $serverType));
    }

    /**
     * Create standardized node data structure for database insertion.
     *
     * This method generates the complete data structure required for creating
     * a cluster node, including unique identifiers, relationships, server
     * specifications, and default status values. Server specifications are
     * extracted from the cloud provider's server type configuration.
     *
     * @param  Cluster  $cluster  The parent cluster for this node
     * @param  string  $type  The node type ('worker' or 'storage')
     * @param  int  $number  The sequential number for this node type
     * @param  string  $serverType  Cloud provider server type identifier
     * @return array Complete node data array ready for database insertion
     */
    private static function createNodeData(Cluster $cluster, string $type, int $number, string $serverType): array
    {
        $serverSpecs = $cluster->cloudProvider->type->getServerSpecs($serverType);

        return [
            'id' => Str::uuid(),
            'cluster_id' => $cluster->id,
            'node_id' => "{$cluster->slug}-{$type}-{$number}",
            'type' => ClusterNodeType::from($type),
            'status' => ClusterStatus::PENDING,
            'server_type' => $serverType,
            'cpu_cores' => $serverSpecs['cpu'] ?? 2,
            'ram_gb' => $serverSpecs['ram'] ?? 4,
            'disk_gb' => $serverSpecs['disk'] ?? 40,
            'os' => 'ubuntu-24.04',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
