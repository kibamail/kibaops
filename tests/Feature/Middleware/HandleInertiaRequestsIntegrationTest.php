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

    $response->assertInertia(
        fn (Assert $page) => $page
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
            ->has('projects', 2)
            ->where('activeProject', null)
            ->where('cloudProvidersCount', 0)
            ->where('clustersCount', 0)
    );
});

test('authenticated user with only owned workspaces receives empty invited workspaces', function () {
    $user = User::factory()->create();

    $ownedWorkspace = Workspace::factory()->create(['user_id' => $user->id]);
    Project::factory()->count(2)->create(['workspace_id' => $ownedWorkspace->id]);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertInertia(
        fn (Assert $page) => $page
            ->has('workspaces', 1)
            ->has('invitedWorkspaces', 0)
            ->where('workspaces.0.id', $ownedWorkspace->id)
            ->has('workspaces.0.projects', 2)
            ->has('projects', 2)
            ->where('activeProject', null)
            ->where('cloudProvidersCount', 0)
            ->where('clustersCount', 0)
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

    $response->assertInertia(
        fn (Assert $page) => $page
            ->has('workspaces', 0)
            ->has('invitedWorkspaces', 1)
            ->where('invitedWorkspaces.0.id', $invitedWorkspace->id)
            ->has('invitedWorkspaces.0.projects', 2)
            ->has('projects', 0)
            ->where('activeProject', null)
            ->where('cloudProvidersCount', 0)
            ->where('clustersCount', 0)
    );
});

test('authenticated user with no workspaces receives empty arrays', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertInertia(
        fn (Assert $page) => $page
            ->has('workspaces', 0)
            ->has('invitedWorkspaces', 0)
            ->has('projects', 0)
            ->where('activeProject', null)
            ->where('cloudProvidersCount', 0)
            ->where('clustersCount', 0)
    );
});

test('unauthenticated user receives empty arrays for workspaces', function () {
    $response = $this->get(route('login'));

    $response->assertInertia(
        fn (Assert $page) => $page
            ->has('workspaces', 0)
            ->has('invitedWorkspaces', 0)
            ->has('projects', 0)
            ->where('activeProject', null)
            ->where('cloudProvidersCount', 0)
            ->where('clustersCount', 0)
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

    $response->assertInertia(
        fn (Assert $page) => $page
            ->has('workspaces', 3)
            ->where('workspaces.0.id', $thirdWorkspace->id)
            ->where('workspaces.1.id', $secondWorkspace->id)
            ->where('workspaces.2.id', $firstWorkspace->id)
            ->has('projects', 0)
            ->where('activeProject', null)
            ->where('cloudProvidersCount', 0)
            ->where('clustersCount', 0)
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

    $response->assertInertia(
        fn (Assert $page) => $page
            ->has('invitedWorkspaces', 3)
            ->where('invitedWorkspaces.0.id', $thirdWorkspace->id)
            ->where('invitedWorkspaces.1.id', $secondWorkspace->id)
            ->where('invitedWorkspaces.2.id', $firstWorkspace->id)
            ->has('projects', 0)
            ->where('activeProject', null)
            ->where('cloudProvidersCount', 0)
            ->where('clustersCount', 0)
    );
});

test('shared data is available across different inertia routes', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    Project::factory()->count(2)->create(['workspace_id' => $workspace->id]);

    $dashboardResponse = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $dashboardResponse->assertInertia(
        fn (Assert $page) => $page
            ->has('workspaces', 1)
            ->has('invitedWorkspaces', 0)
            ->has('projects', 2)
            ->where('activeProject', null)
            ->where('cloudProvidersCount', 0)
            ->where('clustersCount', 0)
    );

    $profileResponse = $this
        ->actingAs($user)
        ->get(route('profile.edit'));

    $profileResponse->assertInertia(
        fn (Assert $page) => $page
            ->has('workspaces', 1)
            ->has('invitedWorkspaces', 0)
            ->has('projects', 2)
            ->where('activeProject', null)
            ->where('cloudProvidersCount', 0)
            ->where('clustersCount', 0)
    );
});

test('active workspace id is shared when session value is present', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->withSession(['active_workspace_id' => $workspace->id])
        ->get(route('dashboard'));

    $response->assertInertia(
        fn (Assert $page) => $page
            ->where('activeWorkspaceId', (string) $workspace->id)
    );
});

test('active workspace id is null when no session value is present', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertInertia(
        fn (Assert $page) => $page
            ->where('activeWorkspaceId', null)
    );
});

test('active workspace id is null for unauthenticated users', function () {
    $response = $this->get(route('login'));

    $response->assertInertia(
        fn (Assert $page) => $page
            ->where('activeWorkspaceId', null)
    );
});

test('projects are loaded for active workspace', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $projects = Project::factory()->count(3)->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->withSession(['active_workspace_id' => $workspace->id])
        ->get(route('dashboard'));

    $response->assertInertia(
        fn (Assert $page) => $page
            ->has('projects', 3)
            ->where('projects.0.id', $projects->get(0)->id)
            ->where('projects.1.id', $projects->get(1)->id)
            ->where('projects.2.id', $projects->get(2)->id)
            ->where('activeProject', null)
            ->where('cloudProvidersCount', 0)
            ->where('clustersCount', 0)
    );
});

test('active project is extracted from project show route', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->withCookie('active_workspace_id', $workspace->id)
        ->get(route('projects.show', $project));

    $response->assertInertia(
        fn (Assert $page) => $page
            ->has('projects', 1)
            ->where('projects.0.id', $project->id)
            ->where('activeProject.id', $project->id)
            ->where('activeProject.name', $project->name)
            ->where('cloudProvidersCount', 0)
            ->where('clustersCount', 0)
    );
});

test('active project is null when no project id in url', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    Project::factory()->count(2)->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->withCookie('active_workspace_id', $workspace->id)
        ->get(route('dashboard'));

    $response->assertInertia(
        fn (Assert $page) => $page
            ->has('projects', 2)
            ->where('activeProject', null)
            ->where('cloudProvidersCount', 0)
            ->where('clustersCount', 0)
    );
});

test('projects are empty when no active workspace', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    Project::factory()->count(2)->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertInertia(
        fn (Assert $page) => $page
            ->has('projects', 2)
            ->where('activeProject', null)
            ->where('cloudProvidersCount', 0)
            ->where('clustersCount', 0)
    );
});

test('cloud providers count is returned for active workspace', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    // Create some cloud providers for the workspace
    \App\Models\CloudProvider::factory()->count(3)->create([
        'workspace_id' => $workspace->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->withCookie('active_workspace_id', $workspace->id)
        ->get(route('dashboard'));

    $response->assertInertia(
        fn (Assert $page) => $page
            ->where('cloudProvidersCount', 3)
    );
});

test('cloud providers count is zero when no active workspace', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    // Create cloud providers but don't set active workspace
    \App\Models\CloudProvider::factory()->count(2)->create([
        'workspace_id' => $workspace->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertInertia(
        fn (Assert $page) => $page
            ->where('cloudProvidersCount', 2)
            ->where('clustersCount', 0)
    );
});

test('clusters count is returned for active workspace', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $cloudProvider = $workspace->cloudProviders()->create([
        'name' => 'Test Provider',
        'type' => 'hetzner',
    ]);

    // Create some clusters for the workspace
    $workspace->createCluster([
        'name' => 'Test Cluster 1',
        'cloud_provider_id' => $cloudProvider->id,
        'region' => 'fsn1',
        'shared_storage_worker_nodes' => false,
    ], 3, 3, 'cx32');

    $workspace->createCluster([
        'name' => 'Test Cluster 2',
        'cloud_provider_id' => $cloudProvider->id,
        'region' => 'fsn1',
        'shared_storage_worker_nodes' => true,
    ], 5, 0, 'cx42');

    $response = $this
        ->actingAs($user)
        ->withSession(['active_workspace_id' => $workspace->id])
        ->get(route('dashboard'));

    $response->assertInertia(
        fn (Assert $page) => $page
            ->where('clustersCount', 2)
    );
});

test('clusters count is zero when no active workspace', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $cloudProvider = $workspace->cloudProviders()->create([
        'name' => 'Test Provider',
        'type' => 'hetzner',
    ]);

    // Create clusters but don't set active workspace
    $workspace->createCluster([
        'name' => 'Test Cluster',
        'cloud_provider_id' => $cloudProvider->id,
        'region' => 'fsn1',
        'shared_storage_worker_nodes' => false,
    ], 3, 3, 'cx32');

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertInertia(
        fn (Assert $page) => $page
            ->where('clustersCount', 1)
    );
});

test('cloud provider regions are shared with frontend grouped by continent', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertInertia(
        fn (Assert $page) => $page
            ->has('cloudProviderRegions')
            ->has('cloudProviderRegions.aws')
            ->has('cloudProviderRegions.hetzner')
            ->has('cloudProviderRegions.digital_ocean')
            ->has('cloudProviderRegions.google_cloud')
            ->has('cloudProviderRegions.vultr')
            ->has('cloudProviderRegions.linode')
            ->has('cloudProviderRegions.leaseweb')
            ->has('cloudProviderRegions.ovh')
        // Check continent grouping for Hetzner
            ->has('cloudProviderRegions.hetzner.Europe')
            ->has('cloudProviderRegions.hetzner.North America')
            ->has('cloudProviderRegions.hetzner.Asia Pacific')
            ->where('cloudProviderRegions.hetzner.Europe.0.name', 'Falkenstein, Germany')
            ->where('cloudProviderRegions.hetzner.Europe.0.slug', 'fsn1')
            ->has('cloudProviderRegions.hetzner.Europe.0.flag')
        // Check continent grouping for DigitalOcean
            ->has('cloudProviderRegions.digital_ocean.North America')
            ->has('cloudProviderRegions.digital_ocean.Europe')
            ->has('cloudProviderRegions.digital_ocean.Asia Pacific')
            ->where('cloudProviderRegions.digital_ocean.North America.0.name', 'New York 1')
            ->where('cloudProviderRegions.digital_ocean.North America.0.slug', 'nyc1')
            ->has('cloudProviderRegions.digital_ocean.North America.0.flag')
    );
});

test('cloud provider regions contain correct continent-grouped structure for all providers', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertInertia(function (Assert $page) {
        $page->has('cloudProviderRegions')
            ->has('cloudProviderRegions.aws')
            ->has('cloudProviderRegions.hetzner')
            ->has('cloudProviderRegions.digital_ocean')
            ->has('cloudProviderRegions.google_cloud')
            ->has('cloudProviderRegions.vultr')
            ->has('cloudProviderRegions.linode')
            ->has('cloudProviderRegions.leaseweb')
            ->has('cloudProviderRegions.ovh');

        // Check continent-grouped structure for Hetzner
        $page->has('cloudProviderRegions.hetzner.Europe')
            ->has('cloudProviderRegions.hetzner.Europe.0.name')
            ->has('cloudProviderRegions.hetzner.Europe.0.slug')
            ->has('cloudProviderRegions.hetzner.Europe.0.flag')
            ->where('cloudProviderRegions.hetzner.Europe.0.name', 'Falkenstein, Germany')
            ->where('cloudProviderRegions.hetzner.Europe.0.slug', 'fsn1')
            ->where('cloudProviderRegions.hetzner.Europe.0.flag', '/flags/de.svg');

        // Check continent-grouped structure for DigitalOcean
        $page->has('cloudProviderRegions.digital_ocean.North America')
            ->has('cloudProviderRegions.digital_ocean.North America.0.name')
            ->has('cloudProviderRegions.digital_ocean.North America.0.slug')
            ->has('cloudProviderRegions.digital_ocean.North America.0.flag')
            ->where('cloudProviderRegions.digital_ocean.North America.0.name', 'New York 1')
            ->where('cloudProviderRegions.digital_ocean.North America.0.slug', 'nyc1')
            ->where('cloudProviderRegions.digital_ocean.North America.0.flag', '/flags/us.svg');

        // Check that AWS has multiple continents
        $page->has('cloudProviderRegions.aws.North America')
            ->has('cloudProviderRegions.aws.Europe')
            ->has('cloudProviderRegions.aws.Asia Pacific')
            ->has('cloudProviderRegions.aws.Middle East')
            ->has('cloudProviderRegions.aws.Africa')
            ->has('cloudProviderRegions.aws.South America');
    });
});

test('cloud providers data is shared with frontend with correct structure', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertInertia(
        fn (Assert $page) => $page
            ->has('cloudProviders')
            ->has('cloudProviders.0.type')
            ->has('cloudProviders.0.name')
            ->has('cloudProviders.0.implemented')
            ->has('cloudProviders.0.description')
            ->has('cloudProviders.0.documentationLink')
            ->has('cloudProviders.0.credentialFields')
        // Check that all expected providers are present
            ->where('cloudProviders.0.type', 'aws')
            ->where('cloudProviders.0.name', 'Amazon web services')
            ->where('cloudProviders.0.implemented', false)
        // Check that implemented providers are marked correctly
            ->whereContains(
                'cloudProviders',
                fn ($provider) => $provider['type'] === 'hetzner' && $provider['implemented'] === true
            )
            ->whereContains(
                'cloudProviders',
                fn ($provider) => $provider['type'] === 'digital_ocean' && $provider['implemented'] === true
            )
        // Check that OVH is included
            ->whereContains(
                'cloudProviders',
                fn ($provider) => $provider['type'] === 'ovh' && $provider['name'] === 'OVH' && $provider['implemented'] === false
            )
        // Check that documentation links are properly structured
            ->whereContains(
                'cloudProviders',
                fn ($provider) => $provider['type'] === 'aws' && $provider['documentationLink'] === 'https://kibaops.com/docs/providers/aws'
            )
            ->whereContains(
                'cloudProviders',
                fn ($provider) => $provider['type'] === 'hetzner' && $provider['documentationLink'] === 'https://kibaops.com/docs/providers/hetzner'
            )
        // Check that descriptions are clean without links
            ->whereContains(
                'cloudProviders',
                fn ($provider) => $provider['type'] === 'aws' && ! str_contains($provider['description'], 'kibaops.com')
            )
        // Check credential fields structure for AWS (multiple fields)
            ->whereContains(
                'cloudProviders',
                fn ($provider) => $provider['type'] === 'aws' &&
                count($provider['credentialFields']) === 2 &&
                $provider['credentialFields'][0]['name'] === 'access_key' &&
                $provider['credentialFields'][1]['name'] === 'secret_key'
            )
        // Check credential fields structure for Hetzner (single field)
            ->whereContains(
                'cloudProviders',
                fn ($provider) => $provider['type'] === 'hetzner' &&
                count($provider['credentialFields']) === 1 &&
                $provider['credentialFields'][0]['name'] === 'token'
            )
    );
});
