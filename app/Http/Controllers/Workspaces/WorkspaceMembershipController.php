<?php

namespace App\Http\Controllers\Workspaces;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workspaces\CreateWorkspaceMembershipRequest;
use App\Http\Requests\Workspaces\UpdateWorkspaceMembershipRequest;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMembership;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceMembershipController extends Controller
{
    use AuthorizesRequests;

    public function index(Workspace $workspace): Response
    {
        $this->authorize('view', $workspace);

        $memberships = $workspace->memberships()
            ->with(['user', 'projects'])
            ->latest()
            ->get();

        return Inertia::render('Workspaces/Memberships/Index', [
            'workspace' => $workspace,
            'memberships' => $memberships,
        ]);
    }

    public function create(Workspace $workspace): Response
    {
        $this->authorize('update', $workspace);

        $projects = $workspace->projects()->get();

        return Inertia::render('Workspaces/Memberships/Create', [
            'workspace' => $workspace,
            'projects' => $projects,
        ]);
    }

    public function store(CreateWorkspaceMembershipRequest $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validated();
        $emails = $validated['emails'];
        $projectIds = $validated['project_ids'];

        $projects = $workspace->projects()->whereIn('id', $projectIds)->get();

        if ($projects->count() !== count($projectIds)) {
            return response()->json(['error' => 'Some projects do not belong to this workspace'], 422);
        }

        $createdMemberships = [];

        DB::transaction(function () use ($workspace, $emails, $projectIds, &$createdMemberships) {
            foreach ($emails as $email) {
                $user = User::where('email', $email)->first();

                $membership = WorkspaceMembership::updateOrCreate(
                    [
                        'workspace_id' => $workspace->id,
                        'email' => $email,
                    ],
                    [
                        'user_id' => $user?->id,
                    ]
                );

                $membership->projects()->sync($projectIds);
                $createdMemberships[] = $membership->load(['user', 'projects']);
            }
        });

        return response()->json([
            'message' => 'Memberships created successfully',
            'memberships' => $createdMemberships,
        ]);
    }

    public function show(Workspace $workspace, WorkspaceMembership $membership): Response
    {
        $this->authorize('view', $workspace);

        if ($membership->workspace_id !== $workspace->id) {
            abort(404);
        }

        $membership->load(['user', 'projects']);

        return Inertia::render('Workspaces/Memberships/Show', [
            'workspace' => $workspace,
            'membership' => $membership,
        ]);
    }

    public function edit(Workspace $workspace, WorkspaceMembership $membership): Response
    {
        $this->authorize('update', $workspace);

        if ($membership->workspace_id !== $workspace->id) {
            abort(404);
        }

        $projects = $workspace->projects()->get();
        $membership->load(['user', 'projects']);

        return Inertia::render('Workspaces/Memberships/Edit', [
            'workspace' => $workspace,
            'membership' => $membership,
            'projects' => $projects,
        ]);
    }

    public function update(UpdateWorkspaceMembershipRequest $request, Workspace $workspace, WorkspaceMembership $membership): JsonResponse
    {
        $this->authorize('update', $workspace);

        if ($membership->workspace_id !== $workspace->id) {
            abort(404);
        }

        $validated = $request->validated();
        $projectIds = $validated['project_ids'];

        $projects = $workspace->projects()->whereIn('id', $projectIds)->get();

        if ($projects->count() !== count($projectIds)) {
            return response()->json(['error' => 'Some projects do not belong to this workspace'], 422);
        }

        $membership->projects()->sync($projectIds);
        $membership->load(['user', 'projects']);

        return response()->json([
            'message' => 'Membership updated successfully',
            'membership' => $membership,
        ]);
    }

    public function destroy(Workspace $workspace, WorkspaceMembership $membership): JsonResponse
    {
        $this->authorize('update', $workspace);

        if ($membership->workspace_id !== $workspace->id) {
            abort(404);
        }

        $membership->delete();

        return response()->json([
            'message' => 'Membership deleted successfully',
        ]);
    }
}
