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
    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('Workspaces/Memberships/Index')
            ->has('workspace')
            ->has('memberships', 3)
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
        ->post(route('workspaces.memberships.store', $workspace), [
            'emails' => $emails,
            'project_ids' => $projectIds,
            'role' => 'developer',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Memberships created successfully');

    foreach ($emails as $email) {
        $membership = WorkspaceMembership::where('email', $email)->first();
        expect($membership)->not->toBeNull()
            ->and($membership->workspace_id)->toBe($workspace->id)
            ->and($membership->user_id)->toBeNull()
            ->and($membership->role->value)->toBe('developer')
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
        ->post(route('workspaces.memberships.store', $workspace), [
            'emails' => $emails,
            'project_ids' => $projectIds,
            'role' => 'admin',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Memberships created successfully');

    $existingUserMembership = WorkspaceMembership::where('email', $existingUser->email)->first();
    expect($existingUserMembership->user_id)->toBe($existingUser->id)
        ->and($existingUserMembership->role->value)->toBe('admin');

    $newUserMembership = WorkspaceMembership::where('email', 'newuser@example.com')->first();
    expect($newUserMembership->user_id)->toBeNull()
        ->and($newUserMembership->role->value)->toBe('admin');
});

test('workspace membership creation validates project ownership', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $otherWorkspace = Workspace::factory()->create();
    $otherProject = Project::factory()->create(['workspace_id' => $otherWorkspace->id]);

    $response = $this
        ->actingAs($user)
        ->post(route('workspaces.memberships.store', $workspace), [
            'emails' => ['test@example.com'],
            'project_ids' => [$otherProject->id],
            'role' => 'developer',
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['project_ids.0']);
});

test('workspace membership can be updated', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $membership = WorkspaceMembership::factory()->forWorkspace($workspace)->developer()->create();
    $projects = Project::factory()->count(3)->create(['workspace_id' => $workspace->id]);

    $membership->projects()->attach($projects->take(2)->pluck('id'));

    $newProjectIds = [$projects->last()->id];

    $response = $this
        ->actingAs($user)
        ->put(route('workspaces.memberships.update', [$workspace, $membership]), [
            'project_ids' => $newProjectIds,
            'role' => 'admin',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Membership updated successfully');

    $membership->refresh();
    expect($membership->projects->count())->toBe(1)
        ->and($membership->projects->first()->id)->toBe($projects->last()->id)
        ->and($membership->role->value)->toBe('admin');
});

test('workspace membership can be deleted', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $membership = WorkspaceMembership::factory()->forWorkspace($workspace)->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('workspaces.memberships.destroy', [$workspace, $membership]));

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Membership deleted successfully');
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
        ->post(route('workspaces.memberships.store', $workspace), [
            'emails' => ['test@example.com'],
            'project_ids' => [$project->id],
            'role' => 'developer',
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
        ->put(route('workspaces.memberships.update', [$workspace, $membership]), [
            'project_ids' => [$project->id],
            'role' => 'admin',
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
        ->delete(route('workspaces.memberships.destroy', [$workspace, $membership]));

    $response->assertForbidden();
    $this->assertModelExists($membership);
});

test('membership creation requires valid email format', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->post(route('workspaces.memberships.store', $workspace), [
            'emails' => ['invalid-email'],
            'project_ids' => [$project->id],
            'role' => 'developer',
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['emails.0']);
});

test('membership creation requires at least one email', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->post(route('workspaces.memberships.store', $workspace), [
            'emails' => [],
            'project_ids' => [$project->id],
            'role' => 'developer',
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['emails']);
});

test('membership creation requires at least one project', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->post(route('workspaces.memberships.store', $workspace), [
            'emails' => ['test@example.com'],
            'project_ids' => [],
            'role' => 'developer',
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['project_ids']);
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

test('membership creation requires a valid role', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->post(route('workspaces.memberships.store', $workspace), [
            'emails' => ['test@example.com'],
            'project_ids' => [$project->id],
            'role' => 'invalid_role',
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['role']);
});

test('membership creation requires role field', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->post(route('workspaces.memberships.store', $workspace), [
            'emails' => ['test@example.com'],
            'project_ids' => [$project->id],
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['role']);
});

test('membership can be created with admin role', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->post(route('workspaces.memberships.store', $workspace), [
            'emails' => ['test@example.com'],
            'project_ids' => [$project->id],
            'role' => 'admin',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Memberships created successfully');

    $membership = WorkspaceMembership::where('email', 'test@example.com')->first();
    expect($membership->role->value)->toBe('admin');
});

test('membership role can be updated', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $membership = WorkspaceMembership::factory()->forWorkspace($workspace)->developer()->create();
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $membership->projects()->attach($project->id);

    $response = $this
        ->actingAs($user)
        ->put(route('workspaces.memberships.update', [$workspace, $membership]), [
            'project_ids' => [$project->id],
            'role' => 'admin',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Membership updated successfully');

    $membership->refresh();
    expect($membership->role->value)->toBe('admin');
});

test('membership role update is optional', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $membership = WorkspaceMembership::factory()->forWorkspace($workspace)->admin()->create();
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $membership->projects()->attach($project->id);

    $response = $this
        ->actingAs($user)
        ->put(route('workspaces.memberships.update', [$workspace, $membership]), [
            'project_ids' => [$project->id],
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Membership updated successfully');

    $membership->refresh();
    expect($membership->role->value)->toBe('admin');
});
