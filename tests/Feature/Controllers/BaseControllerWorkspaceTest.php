<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMembership;
use Tests\Support\TestController;

test('getActiveWorkspace returns owned workspace efficiently', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    Workspace::factory()->count(5)->create();

    $controller = new TestController();

    $this->actingAs($user)
        ->withSession(['active_workspace_id' => $workspace->id])
        ->get('/');

    $activeWorkspace = $controller->testGetActiveWorkspace();
    expect($activeWorkspace)->not->toBeNull();
    expect($activeWorkspace->id)->toBe($workspace->id);
    expect($activeWorkspace->user_id)->toBe($user->id);
});

test('getActiveWorkspace returns invited workspace efficiently', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $owner->id]);

    WorkspaceMembership::factory()->create([
        'user_id' => $member->id,
        'workspace_id' => $workspace->id,
        'role' => 'developer',
    ]);

    Workspace::factory()->count(5)->create();

    $controller = new TestController();

    $this->actingAs($member)
        ->withSession(['active_workspace_id' => $workspace->id])
        ->get('/');

    $activeWorkspace = $controller->testGetActiveWorkspace();
    expect($activeWorkspace)->not->toBeNull();
    expect($activeWorkspace->id)->toBe($workspace->id);
    expect($activeWorkspace->user_id)->toBe($owner->id);
});

test('getActiveWorkspace returns null for unauthorized workspace', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $otherUser->id]);

    $controller = new TestController();

    $this->actingAs($user)
        ->withSession(['active_workspace_id' => $workspace->id])
        ->get('/');

    $activeWorkspace = $controller->testGetActiveWorkspace();
    expect($activeWorkspace)->toBeNull();
});

test('getActiveWorkspace falls back to first workspace when no session value', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $controller = new TestController();

    $this->actingAs($user)->get('/');

    $activeWorkspace = $controller->testGetActiveWorkspace();
    expect($activeWorkspace)->not->toBeNull();
    expect($activeWorkspace->id)->toBe($workspace->id);
});
