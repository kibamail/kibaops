<?php

namespace App\Http\Controllers\Workspaces;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workspaces\CreateWorkspaceMembershipRequest;
use App\Http\Requests\Workspaces\DeleteWorkspaceMembershipRequest;
use App\Http\Requests\Workspaces\UpdateWorkspaceMembershipRequest;
use App\Models\Workspace;
use App\Models\WorkspaceMembership;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
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

    public function store(CreateWorkspaceMembershipRequest $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validated();
        $emails = $validated['emails'];
        $projectIds = $validated['project_ids'];
        $role = $validated['role'];

        $createdMemberships = $workspace->createMemberships($emails, $projectIds, $role);

        return response()->json([
            'message' => 'Memberships created successfully',
            'memberships' => $createdMemberships,
        ]);
    }

    public function update(UpdateWorkspaceMembershipRequest $request, Workspace $workspace, WorkspaceMembership $membership): JsonResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validated();
        $projectIds = $validated['project_ids'];

        if (isset($validated['role'])) {
            $membership->update(['role' => $validated['role']]);
        }

        $membership->projects()->sync($projectIds);
        $membership->load(['user', 'projects']);

        return response()->json([
            'message' => 'Membership updated successfully',
            'membership' => $membership,
        ]);
    }

    public function destroy(DeleteWorkspaceMembershipRequest $request, Workspace $workspace, WorkspaceMembership $membership): JsonResponse
    {
        $membership->delete();

        return response()->json([
            'message' => 'Membership deleted successfully',
        ]);
    }
}
