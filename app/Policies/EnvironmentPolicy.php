<?php

namespace App\Policies;

use App\Enums\WorkspaceMembershipRole;
use App\Models\Environment;
use App\Models\Project;
use App\Models\User;

class EnvironmentPolicy
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
    public function view(User $user, Environment $environment): bool
    {
        return $this->hasProjectAccess($user, $environment->project);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Project $project): bool
    {
        return $this->isWorkspaceAdminWithProjectAccess($user, $project);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Environment $environment): bool
    {
        return $this->isWorkspaceAdminWithProjectAccess($user, $environment->project);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Environment $environment): bool
    {
        return $this->isWorkspaceAdminWithProjectAccess($user, $environment->project);
    }

    /**
     * Check if user has access to the project (either workspace owner or has membership in the project).
     */
    private function hasProjectAccess(User $user, Project $project): bool
    {
        // Workspace owner has access to all projects
        if ($user->id === $project->workspace->user_id) {
            return true;
        }

        // Check if user has membership in this specific project
        return $project->workspace->memberships()
            ->where('user_id', $user->id)
            ->whereHas('projects', function ($query) use ($project) {
                $query->where('project_id', $project->id);
            })
            ->exists();
    }

    /**
     * Check if user is workspace admin with project access.
     */
    private function isWorkspaceAdminWithProjectAccess(User $user, Project $project): bool
    {
        // Workspace owner is always admin
        if ($user->id === $project->workspace->user_id) {
            return true;
        }

        // Check if user is admin member of workspace with access to this project
        return $project->workspace->memberships()
            ->where('user_id', $user->id)
            ->where('role', WorkspaceMembershipRole::ADMIN)
            ->whereHas('projects', function ($query) use ($project) {
                $query->where('project_id', $project->id);
            })
            ->exists();
    }
}
