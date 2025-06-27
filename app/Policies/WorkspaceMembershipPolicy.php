<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkspaceMembership;

class WorkspaceMembershipPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, WorkspaceMembership $workspaceMembership): bool
    {
        return $user->id === $workspaceMembership->workspace->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, WorkspaceMembership $workspaceMembership): bool
    {
        return $user->id === $workspaceMembership->workspace->user_id;
    }

    public function delete(User $user, WorkspaceMembership $workspaceMembership): bool
    {
        return $user->id === $workspaceMembership->workspace->user_id;
    }

    public function restore(User $user, WorkspaceMembership $workspaceMembership): bool
    {
        return $user->id === $workspaceMembership->workspace->user_id;
    }

    public function forceDelete(User $user, WorkspaceMembership $workspaceMembership): bool
    {
        return $user->id === $workspaceMembership->workspace->user_id;
    }
}
