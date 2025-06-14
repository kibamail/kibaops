<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workspace;

class WorkspacePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Workspace $workspace): bool
    {
        // Workspace owner can always view
        if ($user->id === $workspace->user_id) {
            return true;
        }

        // Check if user has membership in this workspace
        return $workspace->memberships()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Workspace $workspace): bool
    {
        return $user->id === $workspace->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Workspace $workspace): bool
    {
        return $user->id === $workspace->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Workspace $workspace): bool
    {
        return $user->id === $workspace->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Workspace $workspace): bool
    {
        return $user->id === $workspace->user_id;
    }
}
