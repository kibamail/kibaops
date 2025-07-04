<?php

namespace App\Http\Controllers\Workspaces;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workspaces\CreateWorkspaceRequest;
use App\Http\Requests\Workspaces\UpdateWorkspaceRequest;
use App\Models\Workspace;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

class WorkspaceController extends Controller
{
    use AuthorizesRequests;

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateWorkspaceRequest $request): RedirectResponse
    {
        $this->authorize('create', Workspace::class);

        $workspace = $request->user()->workspaces()->create($request->validated());

        $this->setActiveWorkspaceId($workspace->id);

        return redirect()->route('dashboard')
            ->with('success', 'Workspace created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWorkspaceRequest $request, Workspace $workspace): RedirectResponse
    {
        $this->authorize('update', $workspace);

        $workspace->update($request->validated());

        return redirect()->back()->with('success', 'Workspace updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Workspace $workspace): RedirectResponse
    {
        $this->authorize('delete', $workspace);

        $workspace->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Workspace deleted successfully.');
    }

    /**
     * Switch to a different workspace.
     */
    public function switch(Workspace $workspace): RedirectResponse
    {
        $this->authorize('view', $workspace);

        $this->setActiveWorkspaceId($workspace->id);

        return redirect()->route('dashboard')
            ->with('success', "Switched to {$workspace->name} workspace.");
    }
}
