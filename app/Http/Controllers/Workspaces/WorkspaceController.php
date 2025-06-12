<?php

namespace App\Http\Controllers\Workspaces;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\Workspaces\CreateWorkspaceRequest;
use App\Http\Requests\Workspaces\UpdateWorkspaceRequest;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceController extends Controller
{
    use AuthorizesRequests;


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Workspace::class);
        
        $workspaces = $request->user()->workspaces()->latest()->get();

        return Inertia::render('Workspaces/Index', [
            'workspaces' => $workspaces,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $this->authorize('create', Workspace::class);
        
        return Inertia::render('Workspaces/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateWorkspaceRequest $request): RedirectResponse
    {
        $this->authorize('create', Workspace::class);
        
        $workspace = $request->user()->workspaces()->create($request->validated());

        return redirect()->route('workspaces.show', $workspace)
            ->with('success', 'Workspace created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Workspace $workspace): Response
    {
        $this->authorize('view', $workspace);
        
        return Inertia::render('Workspaces/Show', [
            'workspace' => $workspace,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Workspace $workspace): Response
    {
        $this->authorize('update', $workspace);
        
        return Inertia::render('Workspaces/Edit', [
            'workspace' => $workspace,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWorkspaceRequest $request, Workspace $workspace): RedirectResponse
    {
        $this->authorize('update', $workspace);
        
        $workspace->update($request->validated());

        return redirect()->route('workspaces.show', $workspace)
            ->with('success', 'Workspace updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Workspace $workspace): RedirectResponse
    {
        $this->authorize('delete', $workspace);
        
        $workspace->delete();

        return redirect()->route('workspaces.index')
            ->with('success', 'Workspace deleted successfully.');
    }
}
