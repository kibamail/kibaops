<?php

namespace App\Http\Controllers\Clusters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clusters\CreateClusterRequest;
use App\Http\Requests\Clusters\DeleteClusterRequest;
use App\Http\Requests\Clusters\UpdateClusterRequest;
use App\Models\Cluster;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

class ClusterController extends Controller
{
    use AuthorizesRequests;

    /**
     * Store a newly created cluster in the workspace.
     * Creates the cluster with the specified configuration and automatically
     * generates SSH keys, stores them in Vault, and creates cluster nodes.
     */
    public function store(CreateClusterRequest $request): RedirectResponse
    {
        $workspace = $this->getActiveWorkspace();
        $validated = $request->validated();
        $workerNodesCount = $validated['worker_nodes_count'];
        $storageNodesCount = $validated['storage_nodes_count'] ?? 0;
        $serverType = $validated['server_type'];

        $cluster = $workspace->createCluster(
            $request->safe()->except(['worker_nodes_count', 'storage_nodes_count', 'server_type']),
            $workerNodesCount,
            $storageNodesCount,
            $serverType
        );

        return redirect()->back()->with('success', 'Cluster created successfully.');
    }

    /**
     * Update the specified cluster with new configuration.
     * Allows updating cluster name and shared storage configuration.
     */
    public function update(UpdateClusterRequest $request, Cluster $cluster): RedirectResponse
    {
        $cluster->update($request->validated());

        return redirect()->back()->with('success', 'Cluster updated successfully.');
    }

    /**
     * Remove the specified cluster from the workspace.
     * This deletes the cluster and all its nodes, and cleans up SSH keys from Vault.
     */
    public function destroy(DeleteClusterRequest $request, Cluster $cluster): RedirectResponse
    {
        $workspace = $this->getActiveWorkspace();

        try {
            $workspace->vault()->writes()->remove($cluster->vault_key);
        } catch (\Exception) {
        }

        $cluster->delete();

        return redirect()->back()->with('success', 'Cluster deleted successfully.');
    }
}
