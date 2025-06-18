<?php

namespace App\Http\Controllers;

use App\Models\Workspace;

abstract class Controller
{
    /**
     * Get the active workspace ID from session cookie with fallback
     *
     * Retrieves the active workspace ID using the following priority:
     * 1. From 'active_workspace_id' cookie if present
     * 2. Falls back to user's first workspace if no cookie set
     * 3. Returns null if user has no workspaces
     */
    protected function getActiveWorkspaceId(): ?string
    {
        $activeWorkspaceId = request()->cookie('active_workspace_id');

        if (!$activeWorkspaceId && request()->user()) {
            $firstWorkspace = request()->user()->workspaces()->first();

            $activeWorkspaceId = $firstWorkspace ? $firstWorkspace->id : null;
        }

        return $activeWorkspaceId;
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
     * Create cookie for setting active workspace
     *
     * Generates a session cookie that persists the active workspace
     * selection across requests. Cookie expires when browser closes.
     */
    protected function setActiveWorkspaceCookie(string $workspaceId): \Symfony\Component\HttpFoundation\Cookie
    {
        return cookie('active_workspace_id', $workspaceId, 0, null, null, false, false);
    }
}
