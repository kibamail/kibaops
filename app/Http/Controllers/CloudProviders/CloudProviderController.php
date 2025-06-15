<?php

namespace App\Http\Controllers\CloudProviders;

use App\Http\Controllers\Controller;
use App\Http\Requests\CloudProviders\CreateCloudProviderRequest;
use App\Http\Requests\CloudProviders\UpdateCloudProviderRequest;
use App\Models\CloudProvider;
use App\Models\Workspace;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

class CloudProviderController extends Controller
{
    use AuthorizesRequests;

    /**
     * Store a newly created cloud provider in the workspace.
     * The request validates the provider type is implemented and verifies
     * the credentials before storing them securely in Vault.
     */
    public function store(CreateCloudProviderRequest $request, Workspace $workspace): RedirectResponse
    {
        $cloudProvider = $workspace->createCloudProvider(
            $request->safe()->except('credentials'),
            $request->validated()['credentials']
        );

        return redirect()->back()->with('success', 'Cloud provider created successfully.');
    }

    /**
     * Update the specified cloud provider with new name and/or credentials.
     * At least one field must be provided. If credentials are updated, they
     * are verified before being stored in Vault.
     */
    public function update(UpdateCloudProviderRequest $request, Workspace $workspace, CloudProvider $cloudProvider): RedirectResponse
    {
        $workspace->updateCloudProvider(
            $cloudProvider,
            $request->safe()->except('credentials'),
            $request->validated()['credentials'] ?? null
        );

        return redirect()->back()->with('success', 'Cloud provider updated successfully.');
    }

    /**
     * Remove the specified cloud provider from the workspace.
     * This soft deletes the database record and removes the credentials
     * from Vault to ensure complete cleanup.
     */
    public function destroy(Workspace $workspace, CloudProvider $cloudProvider): RedirectResponse
    {
        $this->authorize('update', $workspace);

        $workspace->deleteCloudProvider($cloudProvider);

        return redirect()->back()->with('success', 'Cloud provider deleted successfully.');
    }
}
