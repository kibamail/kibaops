<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\CreateEnvironmentRequest;
use App\Http\Requests\Projects\UpdateEnvironmentRequest;
use App\Models\Environment;
use App\Models\Project;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

class EnvironmentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateEnvironmentRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('create', [Environment::class, $project]);

        $environment = $project->environments()->create($request->validated());

        return redirect()->route('projects.show', [
            'project' => $project,
            'environment' => $environment->slug,
        ])->with('success', 'Environment created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEnvironmentRequest $request, Project $project, Environment $environment): RedirectResponse
    {
        $this->authorize('update', $environment);

        $environment->update($request->validated());

        return redirect()->back()->with('success', 'Environment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Environment $environment): RedirectResponse
    {
        $this->authorize('delete', $environment);

        $environment->delete();

        return redirect()->route('projects.show', $project)
            ->with('success', 'Environment deleted successfully.');
    }
}
