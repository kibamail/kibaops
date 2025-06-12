<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\Projects\CreateProjectRequest;
use App\Http\Requests\Projects\UpdateProjectRequest;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Workspace $workspace): Response
    {
        $this->authorize('view', $workspace);
        
        $projects = $workspace->projects()->latest()->get();

        return Inertia::render('Projects/Index', [
            'workspace' => $workspace,
            'projects' => $projects,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Workspace $workspace): Response
    {
        $this->authorize('update', $workspace);
        
        return Inertia::render('Projects/Create', [
            'workspace' => $workspace,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateProjectRequest $request): RedirectResponse
    {
        $project = Project::create($request->validated());

        return redirect()->route('workspaces.projects.show', [
            'workspace' => $project->workspace_id,
            'project' => $project,
        ])->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Workspace $workspace, Project $project): Response
    {
        $this->authorize('view', $project);
        
        return Inertia::render('Projects/Show', [
            'workspace' => $workspace,
            'project' => $project,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Workspace $workspace, Project $project): Response
    {
        $this->authorize('update', $project);
        
        return Inertia::render('Projects/Edit', [
            'workspace' => $workspace,
            'project' => $project,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Workspace $workspace, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);
        
        $project->update($request->validated());

        return redirect()->route('workspaces.projects.show', [
            'workspace' => $workspace,
            'project' => $project,
        ])->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Workspace $workspace, Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);
        
        $project->delete();

        return redirect()->route('workspaces.projects.index', $workspace)
            ->with('success', 'Project deleted successfully.');
    }
}