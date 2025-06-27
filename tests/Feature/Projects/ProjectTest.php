<?php

use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Inertia\Testing\AssertableInertia as Assert;

test('project can be created', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->withSession(['active_workspace_id' => $workspace->id])
        ->post(route('projects.store'), [
            'name' => 'Test Project',
        ]);

    $project = Project::where('name', 'Test Project')->first();

    $this->assertModelExists($project);
    expect($project->workspace_id)->toBe($workspace->id);
    $response->assertRedirect(route('projects.show', $project));

    expect($project->environments)->toHaveCount(2);
    expect($project->environments->pluck('slug')->sort()->values()->toArray())->toEqual(['production', 'staging']);
});

test('project show page is displayed', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->get(route('projects.show', $project));

    $response->assertStatus(200);
    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('Projects/Show')
            ->has('project')
    );
});

test('project can be updated', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->patch(route('projects.update', $project), [
            'name' => 'Updated Project',
        ]);

    $project->refresh();

    expect($project->name)->toBe('Updated Project');
    $response->assertRedirect();
    $response->assertSessionHas('success', 'Project updated successfully.');
});

test('project can be deleted', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->delete(route('projects.destroy', $project));

    $this->assertModelMissing($project);
    $response->assertRedirect(route('dashboard'));
});

test('user cannot access projects of other users workspaces', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user1->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user2)
        ->get(route('projects.show', $project));

    $response->assertForbidden();
});

test('user cannot update projects of other users workspaces', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user1->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user2)
        ->patch(route('projects.update', $project), [
            'name' => 'Updated Project',
        ]);

    $response->assertForbidden();
});

test('user cannot delete projects of other users workspaces', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user1->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user2)
        ->delete(route('projects.destroy', $project));

    $response->assertForbidden();
    $this->assertModelExists($project);
});

test('project creation automatically creates staging and production environments', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $this
        ->actingAs($user)
        ->withSession(['active_workspace_id' => $workspace->id])
        ->post(route('projects.store'), [
            'name' => 'Auto Environment Project',
        ]);

    $project = Project::where('name', 'Auto Environment Project')->first();

    expect($project->environments)->toHaveCount(2);

    $environmentSlugs = $project->environments->pluck('slug')->sort()->values()->toArray();
    expect($environmentSlugs)->toBe(['production', 'staging']);

    $stagingEnvironment = $project->environments->where('slug', 'staging')->first();
    $productionEnvironment = $project->environments->where('slug', 'production')->first();

    expect($stagingEnvironment->project_id)->toBe($project->id);
    expect($productionEnvironment->project_id)->toBe($project->id);
});

test('project creation requires valid data', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->withSession(['active_workspace_id' => $workspace->id])
        ->post(route('projects.store'), [
            'name' => '',
        ]);

    $response->assertSessionHasErrors(['name']);
});

test('project creation requires active workspace in session', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('projects.store'), [
            'name' => 'Test Project',
        ]);

    $response->assertForbidden();
});
