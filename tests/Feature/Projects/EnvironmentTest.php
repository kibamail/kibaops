<?php

use App\Enums\WorkspaceMembershipRole;
use App\Models\Environment;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('workspace owner can create environment', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->post(route('projects.environments.store', $project), [
            'slug' => 'development',
        ]);

    $environment = Environment::where('slug', 'development')->first();

    $this->assertModelExists($environment);
    expect($environment->project_id)->toBe($project->id);
    expect($environment->slug)->toBe('development');

    $response->assertRedirect(route('projects.show', [
        'project' => $project,
        'environment' => 'development',
    ]));
});

test('workspace admin with project access can create environment', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $owner->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $membership = WorkspaceMembership::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $admin->id,
        'email' => $admin->email,
        'role' => WorkspaceMembershipRole::ADMIN,
    ]);
    $membership->projects()->attach($project->id);

    $response = $this
        ->actingAs($admin)
        ->post(route('projects.environments.store', $project), [
            'slug' => 'staging',
        ]);

    $environment = Environment::where('slug', 'staging')->first();

    $this->assertModelExists($environment);
    expect($environment->project_id)->toBe($project->id);

    $response->assertRedirect(route('projects.show', [
        'project' => $project,
        'environment' => 'staging',
    ]));
});

test('workspace developer cannot create environment', function () {
    $owner = User::factory()->create();
    $developer = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $owner->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $membership = WorkspaceMembership::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $developer->id,
        'email' => $developer->email,
        'role' => WorkspaceMembershipRole::DEVELOPER,
    ]);
    $membership->projects()->attach($project->id);

    $response = $this
        ->actingAs($developer)
        ->post(route('projects.environments.store', $project), [
            'slug' => 'development',
        ]);

    $response->assertForbidden();
});

test('user without project access cannot create environment', function () {
    $owner = User::factory()->create();
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $owner->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->post(route('projects.environments.store', $project), [
            'slug' => 'development',
        ]);

    $response->assertForbidden();
});

test('environment slug must be unique within project', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    Environment::factory()->create([
        'project_id' => $project->id,
        'slug' => 'development',
    ]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('projects.environments.store', $project), [
            'slug' => 'development',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['slug']);
});

test('environment slug can be same across different projects', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project1 = Project::factory()->create(['workspace_id' => $workspace->id]);
    $project2 = Project::factory()->create(['workspace_id' => $workspace->id]);

    Environment::factory()->create([
        'project_id' => $project1->id,
        'slug' => 'development',
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('projects.environments.store', $project2), [
            'slug' => 'development',
        ]);

    $environment = Environment::where('slug', 'development')
        ->where('project_id', $project2->id)
        ->first();

    $this->assertModelExists($environment);
    expect($environment->project_id)->toBe($project2->id);

    $response->assertRedirect(route('projects.show', [
        'project' => $project2,
        'environment' => 'development',
    ]));
});

test('workspace admin can update environment', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);
    $environment = Environment::factory()->create(['project_id' => $project->id]);

    $response = $this
        ->actingAs($user)
        ->patch(route('projects.environments.update', [$project, $environment]), [
            'slug' => 'updated-environment',
        ]);

    $environment->refresh();

    expect($environment->slug)->toBe('updated-environment');
    $response->assertJson([
        'message' => 'Environment updated successfully.',
        'environment' => [
            'id' => $environment->id,
            'slug' => 'updated-environment',
        ],
    ]);
});

test('workspace developer cannot update environment', function () {
    $owner = User::factory()->create();
    $developer = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $owner->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);
    $environment = Environment::factory()->create(['project_id' => $project->id]);

    $membership = WorkspaceMembership::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $developer->id,
        'email' => $developer->email,
        'role' => WorkspaceMembershipRole::DEVELOPER,
    ]);
    $membership->projects()->attach($project->id);

    $response = $this
        ->actingAs($developer)
        ->patch(route('projects.environments.update', [$project, $environment]), [
            'slug' => 'updated-environment',
        ]);

    $response->assertForbidden();
});

test('workspace admin can delete environment', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);
    $environment = Environment::factory()->create(['project_id' => $project->id]);

    $response = $this
        ->actingAs($user)
        ->delete(route('projects.environments.destroy', [$project, $environment]));

    $this->assertModelMissing($environment);
    $response->assertRedirect(route('projects.show', $project));
});

test('workspace developer cannot delete environment', function () {
    $owner = User::factory()->create();
    $developer = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $owner->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);
    $environment = Environment::factory()->create(['project_id' => $project->id]);

    $membership = WorkspaceMembership::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $developer->id,
        'email' => $developer->email,
        'role' => WorkspaceMembershipRole::DEVELOPER,
    ]);
    $membership->projects()->attach($project->id);

    $response = $this
        ->actingAs($developer)
        ->delete(route('projects.environments.destroy', [$project, $environment]));

    $response->assertForbidden();
    $this->assertModelExists($environment);
});

test('environment slug is required', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('projects.environments.store', $project), []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['slug']);
});

test('environment slug must be valid format', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('projects.environments.store', $project), [
            'slug' => 'Invalid Slug!',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['slug']);
});

test('environment creation redirects to project show with environment query parameter', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->post(route('projects.environments.store', $project), [
            'slug' => 'production',
        ]);

    $environment = Environment::where('slug', 'production')->first();

    $response->assertRedirect(route('projects.show', [
        'project' => $project,
        'environment' => $environment->slug,
    ]));

    // Verify the redirect URL contains the environment query parameter
    $redirectUrl = $response->headers->get('Location');
    expect($redirectUrl)->toContain('environment=production');
});
