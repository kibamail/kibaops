<?php

namespace App\Http\Controllers\Workspaces;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workspaces\CreateWorkspaceRequest;
use App\Http\Requests\Workspaces\UpdateWorkspaceRequest;
use App\Models\Workspace;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
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

        return redirect()->route('dashboard')
            ->withCookie($this->setActiveWorkspaceCookie($workspace->id))
            ->with('success', 'Workspace created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWorkspaceRequest $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('update', $workspace);

        $workspace->update($request->validated());

        return response()->json([
            'message' => 'Workspace updated successfully.',
            'workspace' => $workspace,
        ]);
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

        return redirect()->route('dashboard')
            ->withCookie($this->setActiveWorkspaceCookie($workspace->id))
            ->with('success', "Switched to {$workspace->name} workspace.");
    }

    /**
     * Set the active workspace cookie.
     */
    private function setActiveWorkspaceCookie(int $workspaceId): \Symfony\Component\HttpFoundation\Cookie
    {
        return cookie('active_workspace_id', $workspaceId, 0, null, null, false, false);
    }
}
