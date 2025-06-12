<?php

use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('authenticated user receives workspaces and invited workspaces in shared data', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $ownedWorkspace = Workspace::factory()->create(['user_id' => $user->id]);
    $ownedProjects = Project::factory()->count(2)->create(['workspace_id' => $ownedWorkspace->id]);

    $invitedWorkspace = Workspace::factory()->create(['user_id' => $otherUser->id]);
    $invitedProjects = Project::factory()->count(3)->create(['workspace_id' => $invitedWorkspace->id]);

    $membership = WorkspaceMembership::factory()->create([
        'workspace_id' => $invitedWorkspace->id,
        'user_id' => $user->id,
        'email' => $user->email,
    ]);
    $membership->projects()->attach($invitedProjects->pluck('id'));

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('workspaces', 1)
        ->has('invitedWorkspaces', 1)
        ->where('workspaces.0.id', $ownedWorkspace->id)
        ->where('workspaces.0.name', $ownedWorkspace->name)
        ->where('workspaces.0.user_id', $user->id)
        ->has('workspaces.0.projects', 2)
        ->where('workspaces.0.projects.0.id', $ownedProjects->first()->id)
        ->where('workspaces.0.projects.1.id', $ownedProjects->last()->id)
        ->where('invitedWorkspaces.0.id', $invitedWorkspace->id)
        ->where('invitedWorkspaces.0.name', $invitedWorkspace->name)
        ->where('invitedWorkspaces.0.user_id', $otherUser->id)
        ->has('invitedWorkspaces.0.projects', 3)
        ->where('invitedWorkspaces.0.projects.0.id', $invitedProjects->get(0)->id)
        ->where('invitedWorkspaces.0.projects.1.id', $invitedProjects->get(1)->id)
        ->where('invitedWorkspaces.0.projects.2.id', $invitedProjects->get(2)->id)
    );
});

test('authenticated user with only owned workspaces receives empty invited workspaces', function () {
    $user = User::factory()->create();

    $ownedWorkspace = Workspace::factory()->create(['user_id' => $user->id]);
    Project::factory()->count(2)->create(['workspace_id' => $ownedWorkspace->id]);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('workspaces', 1)
        ->has('invitedWorkspaces', 0)
        ->where('workspaces.0.id', $ownedWorkspace->id)
        ->has('workspaces.0.projects', 2)
    );
});

test('authenticated user with only invited workspaces receives empty owned workspaces', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $invitedWorkspace = Workspace::factory()->create(['user_id' => $otherUser->id]);
    $invitedProjects = Project::factory()->count(2)->create(['workspace_id' => $invitedWorkspace->id]);

    $membership = WorkspaceMembership::factory()->create([
        'workspace_id' => $invitedWorkspace->id,
        'user_id' => $user->id,
        'email' => $user->email,
    ]);
    $membership->projects()->attach($invitedProjects->pluck('id'));

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('workspaces', 0)
        ->has('invitedWorkspaces', 1)
        ->where('invitedWorkspaces.0.id', $invitedWorkspace->id)
        ->has('invitedWorkspaces.0.projects', 2)
    );
});

test('authenticated user with no workspaces receives empty arrays', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('workspaces', 0)
        ->has('invitedWorkspaces', 0)
    );
});

test('unauthenticated user receives empty arrays for workspaces', function () {
    $response = $this->get(route('login'));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('workspaces', 0)
        ->has('invitedWorkspaces', 0)
    );
});

test('workspaces are ordered by latest first', function () {
    $user = User::factory()->create();

    $firstWorkspace = Workspace::factory()->create([
        'user_id' => $user->id,
        'created_at' => now()->subDays(2),
    ]);
    $secondWorkspace = Workspace::factory()->create([
        'user_id' => $user->id,
        'created_at' => now()->subDay(),
    ]);
    $thirdWorkspace = Workspace::factory()->create([
        'user_id' => $user->id,
        'created_at' => now(),
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('workspaces', 3)
        ->where('workspaces.0.id', $thirdWorkspace->id)
        ->where('workspaces.1.id', $secondWorkspace->id)
        ->where('workspaces.2.id', $firstWorkspace->id)
    );
});

test('invited workspaces are ordered by latest first', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $firstWorkspace = Workspace::factory()->create([
        'user_id' => $otherUser->id,
        'created_at' => now()->subDays(2),
    ]);
    $secondWorkspace = Workspace::factory()->create([
        'user_id' => $otherUser->id,
        'created_at' => now()->subDay(),
    ]);
    $thirdWorkspace = Workspace::factory()->create([
        'user_id' => $otherUser->id,
        'created_at' => now(),
    ]);

    foreach ([$firstWorkspace, $secondWorkspace, $thirdWorkspace] as $workspace) {
        $membership = WorkspaceMembership::factory()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
        $project = Project::factory()->create(['workspace_id' => $workspace->id]);
        $membership->projects()->attach($project->id);
    }

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('invitedWorkspaces', 3)
        ->where('invitedWorkspaces.0.id', $thirdWorkspace->id)
        ->where('invitedWorkspaces.1.id', $secondWorkspace->id)
        ->where('invitedWorkspaces.2.id', $firstWorkspace->id)
    );
});

test('shared data is available across different inertia routes', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    Project::factory()->count(2)->create(['workspace_id' => $workspace->id]);

    $dashboardResponse = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $dashboardResponse->assertInertia(fn (Assert $page) => $page
        ->has('workspaces', 1)
        ->has('invitedWorkspaces', 0)
    );

    $profileResponse = $this
        ->actingAs($user)
        ->get(route('profile.edit'));

    $profileResponse->assertInertia(fn (Assert $page) => $page
        ->has('workspaces', 1)
        ->has('invitedWorkspaces', 0)
    );
});
