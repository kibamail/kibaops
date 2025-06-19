<?php

namespace App\Http\Controllers;

use App\Models\Workspace;

abstract class Controller
{
    /**
     * Get the active workspace ID from session with fallback
     *
     * Retrieves the active workspace ID using the following priority:
     * 1. From session 'active_workspace_id' if present
     * 2. Falls back to user's first workspace if no session value set
     * 3. Returns null if user has no workspaces
     */
    protected function getActiveWorkspaceId(): ?string
    {
        $request = request();

        return $request->session()->get('active_workspace_id', function () use ($request) {
            if (!$request->user()) {
                return null;
            }

            $firstWorkspace = $request->user()->workspaces()->first();

            return $firstWorkspace ? $firstWorkspace->id : null;
        });
    }

    /**
     * Get the active workspace model with full relationship data
     *
     * Returns the complete Workspace model for the active workspace,
     * including both owned and invited workspaces. Uses a single
     * optimized database query instead of loading all workspaces.
     */
    protected function getActiveWorkspace(): ?Workspace
    {
        $activeWorkspaceId = $this->getActiveWorkspaceId();

        if (!$activeWorkspaceId || !request()->user()) {
            return null;
        }

        $user = request()->user();

        return Workspace::where('id', $activeWorkspaceId)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('memberships', function ($membershipQuery) use ($user) {
                          $membershipQuery->where('user_id', $user->id);
                      });
            })
            ->first();
    }

    /**
     * Set the active workspace ID in session
     *
     * Stores the workspace ID in the user's session for persistence
     * across requests. Session data is automatically cleaned up when
     * the session expires.
     */
    protected function setActiveWorkspaceId(string $workspaceId): void
    {
        request()->session()->put('active_workspace_id', $workspaceId);
    }
}
