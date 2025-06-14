<?php

namespace App\Http\Middleware;

use App\Enums\CloudProviderType;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $activeWorkspaceId = $request->cookie('active_workspace_id');
        $activeProjectId = $this->extractActiveProjectId($request);

        // Get workspaces with projects
        $workspaces = $request->user()
            ? $request->user()->workspaces()->with('projects')->latest()->get()
            : [];
        $invitedWorkspaces = $request->user()
            ? $request->user()->invitedWorkspaces()->with('projects')->latest()->get()
            : [];

        // Find active workspace and its projects
        $allWorkspaces = collect($workspaces)->concat(collect($invitedWorkspaces));
        $activeWorkspace = $allWorkspaces->firstWhere('id', $activeWorkspaceId);
        $projects = $activeWorkspace ? $activeWorkspace->projects : collect();

        // Find active project
        $activeProject = $activeProjectId ? $projects->firstWhere('id', $activeProjectId) : null;

        // Count cloud providers for the active workspace
        $cloudProvidersCount = $activeWorkspace ? $activeWorkspace->cloudProviders()->count() : 0;

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'workspaces' => $workspaces,
            'invitedWorkspaces' => $invitedWorkspaces,
            'activeWorkspaceId' => $activeWorkspaceId,
            'projects' => $projects,
            'activeProject' => $activeProject,
            'cloudProvidersCount' => $cloudProvidersCount,
            'cloudProviderRegions' => CloudProviderType::allRegions(),
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }

    /**
     * Extract the active project ID from the URL path.
     * Looks for patterns like /projects/{project-id}
     */
    private function extractActiveProjectId(Request $request): ?string
    {
        $path = $request->path();

        // Match pattern: projects/{project-id} or projects/{project-id}/...
        if (preg_match('/^projects\/([a-f0-9\-]{36})/', $path, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
