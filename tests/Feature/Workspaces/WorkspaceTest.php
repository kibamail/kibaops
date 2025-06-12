<?php

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('workspace index page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('workspaces.index'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Workspaces/Index')
        ->has('workspaces')
    );
});

test('workspace create page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('workspaces.create'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Workspaces/Create')
    );
});

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

    $response->assertRedirect(route('workspaces.show', $workspace));
});

test('workspace show page is displayed', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->get(route('workspaces.show', $workspace));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Workspaces/Show')
        ->has('workspace')
    );
});

test('workspace edit page is displayed', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->get(route('workspaces.edit', $workspace));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Workspaces/Edit')
        ->has('workspace')
    );
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
    $response->assertRedirect(route('workspaces.show', $workspace));
});

test('workspace can be deleted', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->delete(route('workspaces.destroy', $workspace));

    $this->assertModelMissing($workspace);
    $response->assertRedirect(route('workspaces.index'));
});

test('user cannot access workspaces of other users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user1->id]);

    $response = $this
        ->actingAs($user2)
        ->get(route('workspaces.show', $workspace));

    $response->assertForbidden();
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