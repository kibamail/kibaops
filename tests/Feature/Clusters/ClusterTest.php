<?php

use App\Enums\CloudProviderType;
use App\Jobs\ProvisionCluster;
use App\Models\Cluster;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Queue;

test('user can create a cluster with valid data', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $cloudProvider = $workspace->cloudProviders()->create([
        'name' => 'Test Provider',
        'type' => CloudProviderType::HETZNER,
    ]);

    $validRegion = CloudProviderType::HETZNER->getValidRegionSlugs()[0];

    $response = $this
        ->actingAs($user)
        ->withSession(['active_workspace_id' => $workspace->id])
        ->post(route('clusters.store'), [
            'name' => 'Test Cluster',
            'cloud_provider_id' => $cloudProvider->id,
            'region' => $validRegion,
            'worker_nodes_count' => 3,
            'storage_nodes_count' => 3,
            'shared_storage_worker_nodes' => false,
            'server_type' => 'cx32',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Cluster created successfully.');

    $cluster = Cluster::where('name', 'Test Cluster')->first();
    expect($cluster)->not->toBeNull();
    expect($cluster->workspace_id)->toBe($workspace->id);
    expect($cluster->region)->toBe($validRegion);
    expect($cluster->nodes()->count())->toBe(6);
    expect($cluster->workerNodes()->count())->toBe(3);
    expect($cluster->storageNodes()->count())->toBe(3);

    // Verify server specifications
    $firstNode = $cluster->nodes()->first();
    expect($firstNode->server_type)->toBe('cx32');
    expect($firstNode->cpu_cores)->toBe(4);
    expect($firstNode->ram_gb)->toBe(8);
    expect($firstNode->disk_gb)->toBe(80);
    expect($firstNode->os)->toBe('ubuntu-24.04');
});

test('user can create a shared storage/worker cluster', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $cloudProvider = $workspace->cloudProviders()->create([
        'name' => 'Test Provider',
        'type' => CloudProviderType::HETZNER,
    ]);

    $validRegion = CloudProviderType::HETZNER->getValidRegionSlugs()[0];

    $response = $this
        ->actingAs($user)
        ->withSession(['active_workspace_id' => $workspace->id])
        ->post(route('clusters.store'), [
            'name' => 'Shared Cluster',
            'cloud_provider_id' => $cloudProvider->id,
            'region' => $validRegion,
            'worker_nodes_count' => 5,
            'storage_nodes_count' => 0,
            'shared_storage_worker_nodes' => true,
            'server_type' => 'cx42',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Cluster created successfully.');

    $cluster = Cluster::where('name', 'Shared Cluster')->first();
    expect($cluster)->not->toBeNull();
    expect($cluster->shared_storage_worker_nodes)->toBeTrue();
    expect($cluster->nodes()->count())->toBe(5);
    expect($cluster->workerNodes()->count())->toBe(5);
    expect($cluster->storageNodes()->count())->toBe(0);

    // Verify server specifications for shared cluster
    $firstNode = $cluster->nodes()->first();
    expect($firstNode->server_type)->toBe('cx42');
    expect($firstNode->cpu_cores)->toBe(8);
    expect($firstNode->ram_gb)->toBe(16);
    expect($firstNode->disk_gb)->toBe(160);
});

test('cluster creation fails with invalid region', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $cloudProvider = $workspace->cloudProviders()->create([
        'name' => 'Test Provider',
        'type' => CloudProviderType::HETZNER,
    ]);

    $response = $this
        ->actingAs($user)
        ->withSession(['active_workspace_id' => $workspace->id])
        ->post(route('clusters.store'), [
            'name' => 'Test Cluster',
            'cloud_provider_id' => $cloudProvider->id,
            'region' => 'invalid-region-123',
            'worker_nodes_count' => 3,
            'storage_nodes_count' => 3,
            'shared_storage_worker_nodes' => false,
            'server_type' => 'cx32',
        ]);

    $response->assertSessionHasErrors(['region']);
    expect(Cluster::where('name', 'Test Cluster')->exists())->toBeFalse();
});

test('cluster creation fails with invalid server type', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $cloudProvider = $workspace->cloudProviders()->create([
        'name' => 'Test Provider',
        'type' => CloudProviderType::HETZNER,
    ]);

    $validRegion = CloudProviderType::HETZNER->getValidRegionSlugs()[0];

    $response = $this
        ->actingAs($user)
        ->withSession(['active_workspace_id' => $workspace->id])
        ->post(route('clusters.store'), [
            'name' => 'Test Cluster',
            'cloud_provider_id' => $cloudProvider->id,
            'region' => $validRegion,
            'worker_nodes_count' => 3,
            'storage_nodes_count' => 3,
            'shared_storage_worker_nodes' => false,
            'server_type' => 'invalid-server-type',
        ]);

    $response->assertSessionHasErrors(['server_type']);
    expect(Cluster::where('name', 'Test Cluster')->exists())->toBeFalse();
});

test('cluster creation fails with cloud provider from different workspace', function () {
    $user = User::factory()->create();
    $workspace1 = Workspace::factory()->create(['user_id' => $user->id]);
    $workspace2 = Workspace::factory()->create(['user_id' => $user->id]);

    $cloudProvider = $workspace2->cloudProviders()->create([
        'name' => 'Other Workspace Provider',
        'type' => CloudProviderType::HETZNER,
    ]);

    $validRegion = CloudProviderType::HETZNER->getValidRegionSlugs()[0];

    $response = $this
        ->actingAs($user)
        ->withSession(['active_workspace_id' => $workspace1->id])
        ->post(route('clusters.store'), [
            'name' => 'Test Cluster',
            'cloud_provider_id' => $cloudProvider->id,
            'region' => $validRegion,
            'worker_nodes_count' => 3,
            'storage_nodes_count' => 3,
            'shared_storage_worker_nodes' => false,
            'server_type' => 'cx32',
        ]);

    $response->assertSessionHasErrors(['cloud_provider_id']);
    expect(Cluster::where('name', 'Test Cluster')->exists())->toBeFalse();
});

test('cluster creation fails with insufficient worker nodes', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $cloudProvider = $workspace->cloudProviders()->create([
        'name' => 'Test Provider',
        'type' => CloudProviderType::HETZNER,
    ]);

    $validRegion = CloudProviderType::HETZNER->getValidRegionSlugs()[0];

    $response = $this
        ->actingAs($user)
        ->withSession(['active_workspace_id' => $workspace->id])
        ->post(route('clusters.store'), [
            'name' => 'Test Cluster',
            'cloud_provider_id' => $cloudProvider->id,
            'region' => $validRegion,
            'worker_nodes_count' => 2,
            'storage_nodes_count' => 3,
            'shared_storage_worker_nodes' => false,
            'server_type' => 'cx32',
        ]);

    $response->assertSessionHasErrors(['worker_nodes_count']);
    expect(Cluster::where('name', 'Test Cluster')->exists())->toBeFalse();
});

test('user can update a cluster', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $cloudProvider = $workspace->cloudProviders()->create([
        'name' => 'Test Provider',
        'type' => CloudProviderType::HETZNER,
    ]);

    $cluster = $workspace->createCluster([
        'name' => 'Original Cluster',
        'cloud_provider_id' => $cloudProvider->id,
        'region' => CloudProviderType::HETZNER->getValidRegionSlugs()[0],
        'shared_storage_worker_nodes' => false,
    ], 3, 3, 'cx32');

    $response = $this
        ->actingAs($user)
        ->withSession(['active_workspace_id' => $workspace->id])
        ->put(route('clusters.update', $cluster), [
            'name' => 'Updated Cluster',
            'shared_storage_worker_nodes' => true,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Cluster updated successfully.');

    $cluster->refresh();
    expect($cluster->name)->toBe('Updated Cluster');
    expect($cluster->shared_storage_worker_nodes)->toBeTrue();
});

test('user can delete a cluster', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $cloudProvider = $workspace->cloudProviders()->create([
        'name' => 'Test Provider',
        'type' => CloudProviderType::HETZNER,
    ]);

    $cluster = $workspace->createCluster([
        'name' => 'Cluster to Delete',
        'cloud_provider_id' => $cloudProvider->id,
        'region' => CloudProviderType::HETZNER->getValidRegionSlugs()[0],
        'shared_storage_worker_nodes' => false,
    ], 3, 3, 'cx32');

    $clusterId = $cluster->id;

    $response = $this
        ->actingAs($user)
        ->withSession(['active_workspace_id' => $workspace->id])
        ->delete(route('clusters.destroy', $cluster));

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Cluster deleted successfully.');

    expect(Cluster::find($clusterId))->toBeNull();
});

test('unauthorized user cannot access cluster operations', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user1->id]);
    $cloudProvider = $workspace->cloudProviders()->create([
        'name' => 'Test Provider',
        'type' => CloudProviderType::HETZNER,
    ]);

    $cluster = $workspace->createCluster([
        'name' => 'Protected Cluster',
        'cloud_provider_id' => $cloudProvider->id,
        'region' => CloudProviderType::HETZNER->getValidRegionSlugs()[0],
        'shared_storage_worker_nodes' => false,
    ], 3, 3, 'cx32');

    $response = $this
        ->actingAs($user2)
        ->withSession(['active_workspace_id' => $workspace->id])
        ->put(route('clusters.update', $cluster), [
            'name' => 'Hacked Cluster',
        ]);

    $response->assertStatus(403);
});

test('provision cluster job is dispatched when cluster is created', function () {
    Queue::fake();

    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $cloudProvider = $workspace->cloudProviders()->create([
        'name' => 'Test Provider',
        'type' => CloudProviderType::HETZNER,
    ]);

    $validRegion = CloudProviderType::HETZNER->getValidRegionSlugs()[0];

    $this
        ->actingAs($user)
        ->withSession(['active_workspace_id' => $workspace->id])
        ->post(route('clusters.store'), [
            'name' => 'Test Cluster',
            'cloud_provider_id' => $cloudProvider->id,
            'region' => $validRegion,
            'worker_nodes_count' => 3,
            'storage_nodes_count' => 3,
            'shared_storage_worker_nodes' => false,
            'server_type' => 'cx32',
        ]);

    $cluster = Cluster::where('name', 'Test Cluster')->first();
    expect($cluster)->not->toBeNull();

    Queue::assertPushed(ProvisionCluster::class, function ($job) use ($cluster) {
        return $job->cluster->id === $cluster->id;
    });
});
