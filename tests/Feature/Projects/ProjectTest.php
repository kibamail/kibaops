<?php

use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Inertia\Testing\AssertableInertia as Assert;

test('project index page is displayed', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $projects = Project::factory()->count(3)->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->get(route('workspaces.projects.index', $workspace));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Projects/Index')
        ->has('workspace')
        ->has('projects', 3)
    );
});

test('project create page is displayed', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->get(route('workspaces.projects.create', $workspace));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Projects/Create')
        ->has('workspace')
    );
});

test('project can be created', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->post(route('workspaces.projects.store', $workspace), [
            'name' => 'Test Project',
            'workspace_id' => $workspace->id,
        ]);

    $project = Project::where('name', 'Test Project')->first();

    $this->assertModelExists($project);
    $response->assertRedirect(route('workspaces.projects.show', [
        'workspace' => $workspace->id,
        'project' => $project->id,
    ]));
});

test('project show page is displayed', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->get(route('workspaces.projects.show', [
            'workspace' => $workspace,
            'project' => $project,
        ]));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Projects/Show')
        ->has('workspace')
        ->has('project')
    );
});

test('project edit page is displayed', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->get(route('workspaces.projects.edit', [
            'workspace' => $workspace,
            'project' => $project,
        ]));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Projects/Edit')
        ->has('workspace')
        ->has('project')
    );
});

test('project can be updated', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->patch(route('workspaces.projects.update', [
            'workspace' => $workspace,
            'project' => $project,
        ]), [
            'name' => 'Updated Project',
        ]);

    $project->refresh();

    expect($project->name)->toBe('Updated Project');
    $response->assertRedirect(route('workspaces.projects.show', [
        'workspace' => $workspace,
        'project' => $project,
    ]));
});

test('project can be deleted', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->delete(route('workspaces.projects.destroy', [
            'workspace' => $workspace,
            'project' => $project,
        ]));

    $this->assertModelMissing($project);
    $response->assertRedirect(route('workspaces.projects.index', $workspace));
});

test('user cannot access projects of other users workspaces', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user1->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user2)
        ->get(route('workspaces.projects.show', [
            'workspace' => $workspace,
            'project' => $project,
        ]));

    $response->assertForbidden();
});

test('user cannot update projects of other users workspaces', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user1->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user2)
        ->patch(route('workspaces.projects.update', [
            'workspace' => $workspace,
            'project' => $project,
        ]), [
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
        ->delete(route('workspaces.projects.destroy', [
            'workspace' => $workspace,
            'project' => $project,
        ]));

    $response->assertForbidden();
    $this->assertModelExists($project);
});
