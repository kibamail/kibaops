<?php

namespace App\Http\Controllers\CloudProviders;

use App\Http\Controllers\Controller;
use App\Http\Requests\CloudProviders\CreateCloudProviderRequest;
use App\Http\Requests\CloudProviders\UpdateCloudProviderRequest;
use App\Models\CloudProvider;
use App\Models\Workspace;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class CloudProviderController extends Controller
{
    use AuthorizesRequests;

    /**
     * Store a newly created cloud provider in the workspace.
     * The request validates the provider type is implemented and verifies
     * the credentials before storing them securely in Vault.
     */
    public function store(CreateCloudProviderRequest $request, Workspace $workspace): JsonResponse
    {
        $cloudProvider = $workspace->createCloudProvider(
            $request->safe()->except('credentials'),
            $request->validated()['credentials']
        );

        return response()->json([
            'message' => 'Cloud provider created successfully.',
            'cloud_provider' => $cloudProvider,
        ], 201);
    }

    /**
     * Update the specified cloud provider with new name and/or credentials.
     * At least one field must be provided. If credentials are updated, they
     * are verified before being stored in Vault.
     */
    public function update(UpdateCloudProviderRequest $request, Workspace $workspace, CloudProvider $cloudProvider): JsonResponse
    {
        $updatedProvider = $workspace->updateCloudProvider(
            $cloudProvider,
            $request->safe()->except('credentials'),
            $request->validated()['credentials'] ?? null
        );

        return response()->json([
            'message' => 'Cloud provider updated successfully.',
            'cloud_provider' => $updatedProvider,
        ]);
    }

    /**
     * Remove the specified cloud provider from the workspace.
     * This soft deletes the database record and removes the credentials
     * from Vault to ensure complete cleanup.
     */
    public function destroy(Workspace $workspace, CloudProvider $cloudProvider): JsonResponse
    {
        $this->authorize('update', $workspace);

        $workspace->deleteCloudProvider($cloudProvider);

        return response()->json([
            'message' => 'Cloud provider deleted successfully.',
        ]);
    }
}
