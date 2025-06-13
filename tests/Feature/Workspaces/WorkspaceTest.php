<?php

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('workspace can be created', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('workspaces.store'), [
            'name' => 'Test Workspace',
        ]);

    $workspace = Workspace::first();

    expect($workspace)->not->toBeNull()
        ->and($workspace->name)->toBe('Test Workspace')
        ->and($workspace->user_id)->toBe($user->id);

    $response->assertRedirect(route('dashboard'));
    $response->assertCookie('active_workspace_id', $workspace->id);
});

test('workspace can be updated', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->put(route('workspaces.update', $workspace), [
            'name' => 'Updated Workspace',
            'slug' => $workspace->slug,
        ]);

    $workspace->refresh();

    expect($workspace->name)->toBe('Updated Workspace');
    $response->assertJson([
        'message' => 'Workspace updated successfully.',
        'workspace' => [
            'id' => $workspace->id,
            'name' => 'Updated Workspace',
            'slug' => $workspace->slug,
        ],
    ]);
});

test('workspace can be deleted', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->delete(route('workspaces.destroy', $workspace));

    $this->assertModelMissing($workspace);
    $response->assertRedirect(route('dashboard'));
});

test('user cannot update workspaces of other users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user1->id]);

    $response = $this
        ->actingAs($user2)
        ->put(route('workspaces.update', $workspace), [
            'name' => 'Updated Workspace',
            'slug' => $workspace->slug,
        ]);

    $response->assertForbidden();
});

test('user cannot delete workspaces of other users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user1->id]);

    $response = $this
        ->actingAs($user2)
        ->delete(route('workspaces.destroy', $workspace));

    $response->assertForbidden();
    $this->assertModelExists($workspace);
});

test('creating workspace sets active workspace cookie', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('workspaces.store'), [
            'name' => 'Active Workspace Test',
        ]);

    $workspace = Workspace::where('name', 'Active Workspace Test')->first();

    $response->assertCookie('active_workspace_id', $workspace->id);
    $response->assertRedirect(route('dashboard'));
});
