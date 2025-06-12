<?php

use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('workspace membership index page is displayed', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $memberships = WorkspaceMembership::factory()->count(3)->forWorkspace($workspace)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('workspaces.memberships.index', $workspace));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Workspaces/Memberships/Index')
        ->has('workspace')
        ->has('memberships', 3)
    );
});

test('workspace membership create page is displayed', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $projects = Project::factory()->count(2)->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->get(route('workspaces.memberships.create', $workspace));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Workspaces/Memberships/Create')
        ->has('workspace')
        ->has('projects', 2)
    );
});

test('workspace memberships can be created with bulk emails', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $projects = Project::factory()->count(2)->create(['workspace_id' => $workspace->id]);

    $emails = ['test1@example.com', 'test2@example.com'];
    $projectIds = $projects->pluck('id')->toArray();

    $response = $this
        ->actingAs($user)
        ->postJson(route('workspaces.memberships.store', $workspace), [
            'emails' => $emails,
            'project_ids' => $projectIds,
        ]);

    $response->assertStatus(200);

    foreach ($emails as $email) {
        $membership = WorkspaceMembership::where('email', $email)->first();
        expect($membership)->not->toBeNull()
            ->and($membership->workspace_id)->toBe($workspace->id)
            ->and($membership->user_id)->toBeNull()
            ->and($membership->projects->count())->toBe(2);
    }
});

test('workspace memberships automatically link existing users', function () {
    $owner = User::factory()->create();
    $existingUser = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $owner->id]);
    $projects = Project::factory()->count(2)->create(['workspace_id' => $workspace->id]);

    $emails = [$existingUser->email, 'newuser@example.com'];
    $projectIds = $projects->pluck('id')->toArray();

    $response = $this
        ->actingAs($owner)
        ->postJson(route('workspaces.memberships.store', $workspace), [
            'emails' => $emails,
            'project_ids' => $projectIds,
        ]);

    $response->assertStatus(200);

    $existingUserMembership = WorkspaceMembership::where('email', $existingUser->email)->first();
    expect($existingUserMembership->user_id)->toBe($existingUser->id);

    $newUserMembership = WorkspaceMembership::where('email', 'newuser@example.com')->first();
    expect($newUserMembership->user_id)->toBeNull();
});

test('workspace membership creation validates project ownership', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $otherWorkspace = Workspace::factory()->create();
    $otherProject = Project::factory()->create(['workspace_id' => $otherWorkspace->id]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('workspaces.memberships.store', $workspace), [
            'emails' => ['test@example.com'],
            'project_ids' => [$otherProject->id],
        ]);

    $response->assertStatus(422);
    $response->assertJson(['error' => 'Some projects do not belong to this workspace']);
});

test('workspace membership show page is displayed', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $membership = WorkspaceMembership::factory()->forWorkspace($workspace)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('workspaces.memberships.show', [$workspace, $membership]));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Workspaces/Memberships/Show')
        ->has('workspace')
        ->has('membership')
    );
});

test('workspace membership edit page is displayed', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $membership = WorkspaceMembership::factory()->forWorkspace($workspace)->create();
    $projects = Project::factory()->count(2)->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->get(route('workspaces.memberships.edit', [$workspace, $membership]));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Workspaces/Memberships/Edit')
        ->has('workspace')
        ->has('membership')
        ->has('projects', 2)
    );
});

test('workspace membership can be updated', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $membership = WorkspaceMembership::factory()->forWorkspace($workspace)->create();
    $projects = Project::factory()->count(3)->create(['workspace_id' => $workspace->id]);

    $membership->projects()->attach($projects->take(2)->pluck('id'));

    $newProjectIds = [$projects->last()->id];

    $response = $this
        ->actingAs($user)
        ->putJson(route('workspaces.memberships.update', [$workspace, $membership]), [
            'project_ids' => $newProjectIds,
        ]);

    $response->assertStatus(200);

    $membership->refresh();
    expect($membership->projects->count())->toBe(1)
        ->and($membership->projects->first()->id)->toBe($projects->last()->id);
});

test('workspace membership can be deleted', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $membership = WorkspaceMembership::factory()->forWorkspace($workspace)->create();

    $response = $this
        ->actingAs($user)
        ->deleteJson(route('workspaces.memberships.destroy', [$workspace, $membership]));

    $response->assertStatus(200);
    $this->assertModelMissing($membership);
});

test('user cannot access memberships of other users workspaces', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user1->id]);

    $response = $this
        ->actingAs($user2)
        ->get(route('workspaces.memberships.index', $workspace));

    $response->assertForbidden();
});

test('user cannot create memberships for other users workspaces', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user1->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user2)
        ->postJson(route('workspaces.memberships.store', $workspace), [
            'emails' => ['test@example.com'],
            'project_ids' => [$project->id],
        ]);

    $response->assertForbidden();
});

test('user cannot update memberships for other users workspaces', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user1->id]);
    $membership = WorkspaceMembership::factory()->forWorkspace($workspace)->create();
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user2)
        ->putJson(route('workspaces.memberships.update', [$workspace, $membership]), [
            'project_ids' => [$project->id],
        ]);

    $response->assertForbidden();
});

test('user cannot delete memberships for other users workspaces', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user1->id]);
    $membership = WorkspaceMembership::factory()->forWorkspace($workspace)->create();

    $response = $this
        ->actingAs($user2)
        ->deleteJson(route('workspaces.memberships.destroy', [$workspace, $membership]));

    $response->assertForbidden();
    $this->assertModelExists($membership);
});

test('membership creation requires valid email format', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('workspaces.memberships.store', $workspace), [
            'emails' => ['invalid-email'],
            'project_ids' => [$project->id],
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['emails.0']);
});

test('membership creation requires at least one email', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('workspaces.memberships.store', $workspace), [
            'emails' => [],
            'project_ids' => [$project->id],
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['emails']);
});

test('membership creation requires at least one project', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('workspaces.memberships.store', $workspace), [
            'emails' => ['test@example.com'],
            'project_ids' => [],
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['project_ids']);
});

test('user can access their invited workspaces', function () {
    $owner = User::factory()->create();
    $invitedUser = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $owner->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $membership = WorkspaceMembership::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $invitedUser->id,
        'email' => $invitedUser->email,
    ]);
    $membership->projects()->attach($project->id);

    $invitedWorkspaces = $invitedUser->workspaceMemberships()->with('workspace')->get();

    expect($invitedWorkspaces->count())->toBe(1)
        ->and($invitedWorkspaces->first()->workspace->id)->toBe($workspace->id)
        ->and($invitedWorkspaces->first()->workspace->user_id)->toBe($owner->id);
});
