<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\CreateProjectRequest;
use App\Http\Requests\Projects\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateProjectRequest $request): RedirectResponse
    {
        $activeWorkspaceId = $this->getActiveWorkspaceId();

        if (! $activeWorkspaceId) {
            return redirect()->route('dashboard')
                ->with('error', 'No active workspace found.');
        }

        $project = Project::create([
            ...$request->validated(),
            'workspace_id' => $activeWorkspaceId,
        ]);

        $project->environments()->createMany([
            ['slug' => 'staging'],
            ['slug' => 'production'],
        ]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project): Response
    {
        $this->authorize('view', $project);

        return Inertia::render('Projects/Show', [
            'project' => $project,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $project->update($request->validated());

        return redirect()->back()->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Project deleted successfully.');
    }
}
