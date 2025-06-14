<?php

use App\Enums\CloudProviderType;
use App\Models\CloudProvider;
use App\Models\User;
use App\Models\Workspace;
use App\Services\CloudProviders\CloudProviderFactory;
use App\Services\CloudProviders\DigitalOceanCloudProvider;
use App\Services\CloudProviders\HetznerCloudProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('cloud provider can be created with valid hetzner credentials', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    Http::fake([
        'api.hetzner-cloud.com/v1/servers' => Http::response(['servers' => []], 200),
        'api.hetzner-cloud.com/v1/ssh_keys' => Http::response(['ssh_keys' => []], 200),
    ]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('workspaces.cloud-providers.store', $workspace), [
            'name' => 'My Hetzner Provider',
            'type' => CloudProviderType::HETZNER->value,
            'credentials' => 'valid-hetzner-token',
        ]);

    $response->assertStatus(201);
    $response->assertJson([
        'message' => 'Cloud provider created successfully.',
        'cloud_provider' => [
            'name' => 'My Hetzner Provider',
            'type' => CloudProviderType::HETZNER->value,
            'workspace_id' => $workspace->id,
        ],
    ]);

    $cloudProvider = CloudProvider::first();
    expect($cloudProvider)->not->toBeNull()
        ->and($cloudProvider->name)->toBe('My Hetzner Provider')
        ->and($cloudProvider->type)->toBe(CloudProviderType::HETZNER)
        ->and($cloudProvider->workspace_id)->toBe($workspace->id);

    $storedCredentials = $workspace->vault()->reads()->secret($cloudProvider->vault_key);
    expect($storedCredentials)->toBe('valid-hetzner-token');
});

test('cloud provider can be created with valid digital ocean credentials', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    Http::fake([
        'api.digitalocean.com/v2/droplets' => Http::response(['droplets' => []], 200),
        'api.digitalocean.com/v2/account/keys' => Http::response(['ssh_keys' => []], 200),
    ]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('workspaces.cloud-providers.store', $workspace), [
            'name' => 'My DigitalOcean Provider',
            'type' => CloudProviderType::DIGITAL_OCEAN->value,
            'credentials' => 'valid-do-token',
        ]);

    $response->assertStatus(201);
    $response->assertJson([
        'message' => 'Cloud provider created successfully.',
        'cloud_provider' => [
            'name' => 'My DigitalOcean Provider',
            'type' => CloudProviderType::DIGITAL_OCEAN->value,
            'workspace_id' => $workspace->id,
        ],
    ]);

    $cloudProvider = CloudProvider::first();
    expect($cloudProvider)->not->toBeNull()
        ->and($cloudProvider->name)->toBe('My DigitalOcean Provider')
        ->and($cloudProvider->type)->toBe(CloudProviderType::DIGITAL_OCEAN)
        ->and($cloudProvider->workspace_id)->toBe($workspace->id);

    $storedCredentials = $workspace->vault()->reads()->secret($cloudProvider->vault_key);
    expect($storedCredentials)->toBe('valid-do-token');
});

test('cloud provider creation fails with invalid hetzner credentials', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    Http::fake([
        'api.hetzner-cloud.com/v1/servers' => Http::response(['error' => 'Unauthorized'], 401),
        'api.hetzner-cloud.com/v1/ssh_keys' => Http::response(['error' => 'Unauthorized'], 401),
    ]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('workspaces.cloud-providers.store', $workspace), [
            'name' => 'Invalid Hetzner Provider',
            'type' => CloudProviderType::HETZNER->value,
            'credentials' => 'invalid-hetzner-token',
        ]);

    $response->assertStatus(422);
    $response->assertJson([
        'message' => 'The provided credentials could not be verified. Please check your credentials and try again.',
    ]);

    expect(CloudProvider::count())->toBe(0);
});

test('cloud provider creation fails with invalid digital ocean credentials', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    Http::fake([
        'api.digitalocean.com/v2/droplets' => Http::response(['error' => 'Unauthorized'], 401),
        'api.digitalocean.com/v2/account/keys' => Http::response(['error' => 'Unauthorized'], 401),
    ]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('workspaces.cloud-providers.store', $workspace), [
            'name' => 'Invalid DO Provider',
            'type' => CloudProviderType::DIGITAL_OCEAN->value,
            'credentials' => 'invalid-do-token',
        ]);

    $response->assertStatus(422);
    $response->assertJson([
        'message' => 'The provided credentials could not be verified. Please check your credentials and try again.',
    ]);

    expect(CloudProvider::count())->toBe(0);
});

test('cloud provider creation fails for unimplemented provider types', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('workspaces.cloud-providers.store', $workspace), [
            'name' => 'AWS Provider',
            'type' => CloudProviderType::AWS->value,
            'credentials' => 'some-aws-token',
        ]);

    $response->assertStatus(422);
    $response->assertJson([
        'message' => "Cloud provider type 'Amazon Web Services' is not implemented yet.",
    ]);

    expect(CloudProvider::count())->toBe(0);
});

test('cloud provider creation requires valid input', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('workspaces.cloud-providers.store', $workspace), [
            'name' => '',
            'type' => 'invalid-type',
            'credentials' => '',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name', 'type', 'credentials']);
});

test('cloud provider name cannot exceed 32 characters', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('workspaces.cloud-providers.store', $workspace), [
            'name' => str_repeat('a', 33),
            'type' => CloudProviderType::HETZNER->value,
            'credentials' => 'some-token',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name']);
});

test('user cannot create cloud provider for workspace they do not own', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user1->id]);

    $response = $this
        ->actingAs($user2)
        ->postJson(route('workspaces.cloud-providers.store', $workspace), [
            'name' => 'Unauthorized Provider',
            'type' => CloudProviderType::HETZNER->value,
            'credentials' => 'some-token',
        ]);

    $response->assertForbidden();
});

test('hetzner cloud provider verifies both read and write access', function () {
    $http = app(HttpClient::class);
    $provider = new HetznerCloudProvider($http);

    Http::fake([
        'api.hetzner-cloud.com/v1/servers' => Http::response(['servers' => []], 200),
        'api.hetzner-cloud.com/v1/ssh_keys' => Http::response(['ssh_keys' => []], 200),
    ]);

    $result = $provider->verify('valid-token');
    expect($result)->toBeTrue();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.hetzner-cloud.com/v1/servers' &&
               $request->header('Authorization')[0] === 'Bearer valid-token';
    });

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.hetzner-cloud.com/v1/ssh_keys' &&
               $request->header('Authorization')[0] === 'Bearer valid-token';
    });
});

test('digital ocean cloud provider verifies both read and write access', function () {
    $http = app(HttpClient::class);
    $provider = new DigitalOceanCloudProvider($http);

    Http::fake([
        'api.digitalocean.com/v2/droplets' => Http::response(['droplets' => []], 200),
        'api.digitalocean.com/v2/account/keys' => Http::response(['ssh_keys' => []], 200),
    ]);

    $result = $provider->verify('valid-token');
    expect($result)->toBeTrue();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.digitalocean.com/v2/droplets' &&
               $request->header('Authorization')[0] === 'Bearer valid-token';
    });

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.digitalocean.com/v2/account/keys' &&
               $request->header('Authorization')[0] === 'Bearer valid-token';
    });
});

test('cloud provider factory creates correct provider instances', function () {
    $factory = app(CloudProviderFactory::class);

    $hetznerProvider = $factory->create(CloudProviderType::HETZNER);
    expect($hetznerProvider)->toBeInstanceOf(HetznerCloudProvider::class);

    $doProvider = $factory->create(CloudProviderType::DIGITAL_OCEAN);
    expect($doProvider)->toBeInstanceOf(DigitalOceanCloudProvider::class);

    expect(fn () => $factory->create(CloudProviderType::AWS))
        ->toThrow(InvalidArgumentException::class, "Cloud provider type 'aws' is not implemented yet.");
});

test('workspace can retrieve cloud providers through relationship', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $cloudProvider = CloudProvider::create([
        'name' => 'Test Provider',
        'type' => CloudProviderType::HETZNER,
        'workspace_id' => $workspace->id,
    ]);

    $providers = $workspace->cloudProviders;
    expect($providers)->toHaveCount(1)
        ->and($providers->first()->id)->toBe($cloudProvider->id);
});

test('cloud provider vault key is correctly formatted', function () {
    $workspace = Workspace::factory()->create();
    $cloudProvider = CloudProvider::create([
        'name' => 'Test Provider',
        'type' => CloudProviderType::HETZNER,
        'workspace_id' => $workspace->id,
    ]);

    expect($cloudProvider->vault_key)->toBe("providers/{$cloudProvider->id}");
});

test('cloud provider can be updated with new name', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $cloudProvider = CloudProvider::factory()->create([
        'workspace_id' => $workspace->id,
        'type' => CloudProviderType::HETZNER,
    ]);

    $response = $this
        ->actingAs($user)
        ->putJson(route('workspaces.cloud-providers.update', [$workspace, $cloudProvider]), [
            'name' => 'Updated Provider Name',
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'Cloud provider updated successfully.',
        'cloud_provider' => [
            'name' => 'Updated Provider Name',
            'type' => CloudProviderType::HETZNER->value,
        ],
    ]);

    $cloudProvider->refresh();
    expect($cloudProvider->name)->toBe('Updated Provider Name');
});

test('cloud provider can be updated with new credentials', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $cloudProvider = CloudProvider::factory()->create([
        'workspace_id' => $workspace->id,
        'type' => CloudProviderType::HETZNER,
    ]);

    Http::fake([
        'api.hetzner-cloud.com/v1/servers' => Http::response(['servers' => []], 200),
        'api.hetzner-cloud.com/v1/ssh_keys' => Http::response(['ssh_keys' => []], 200),
    ]);

    $response = $this
        ->actingAs($user)
        ->putJson(route('workspaces.cloud-providers.update', [$workspace, $cloudProvider]), [
            'credentials' => 'new-valid-token',
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'Cloud provider updated successfully.',
    ]);

    $storedCredentials = $workspace->vault()->reads()->secret($cloudProvider->vault_key);
    expect($storedCredentials)->toBe('new-valid-token');
});

test('cloud provider can be updated with both name and credentials', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $cloudProvider = CloudProvider::factory()->create([
        'workspace_id' => $workspace->id,
        'type' => CloudProviderType::DIGITAL_OCEAN,
    ]);

    Http::fake([
        'api.digitalocean.com/v2/droplets' => Http::response(['droplets' => []], 200),
        'api.digitalocean.com/v2/account/keys' => Http::response(['ssh_keys' => []], 200),
    ]);

    $response = $this
        ->actingAs($user)
        ->putJson(route('workspaces.cloud-providers.update', [$workspace, $cloudProvider]), [
            'name' => 'Updated DO Provider',
            'credentials' => 'new-do-token',
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'Cloud provider updated successfully.',
        'cloud_provider' => [
            'name' => 'Updated DO Provider',
            'type' => CloudProviderType::DIGITAL_OCEAN->value,
        ],
    ]);

    $cloudProvider->refresh();
    expect($cloudProvider->name)->toBe('Updated DO Provider');

    $storedCredentials = $workspace->vault()->reads()->secret($cloudProvider->vault_key);
    expect($storedCredentials)->toBe('new-do-token');
});

test('cloud provider update fails with invalid credentials', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $cloudProvider = CloudProvider::factory()->create([
        'workspace_id' => $workspace->id,
        'type' => CloudProviderType::HETZNER,
    ]);

    Http::fake([
        'api.hetzner-cloud.com/v1/servers' => Http::response(['error' => 'Unauthorized'], 401),
        'api.hetzner-cloud.com/v1/ssh_keys' => Http::response(['error' => 'Unauthorized'], 401),
    ]);

    $response = $this
        ->actingAs($user)
        ->putJson(route('workspaces.cloud-providers.update', [$workspace, $cloudProvider]), [
            'credentials' => 'invalid-token',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['credentials']);
});

test('cloud provider update requires at least one field', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $cloudProvider = CloudProvider::factory()->create([
        'workspace_id' => $workspace->id,
        'type' => CloudProviderType::HETZNER,
    ]);

    $response = $this
        ->actingAs($user)
        ->putJson(route('workspaces.cloud-providers.update', [$workspace, $cloudProvider]), []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name']);
});

test('user cannot update cloud provider for workspace they do not own', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user1->id]);
    $cloudProvider = CloudProvider::factory()->create([
        'workspace_id' => $workspace->id,
        'type' => CloudProviderType::HETZNER,
    ]);

    $response = $this
        ->actingAs($user2)
        ->putJson(route('workspaces.cloud-providers.update', [$workspace, $cloudProvider]), [
            'name' => 'Unauthorized Update',
        ]);

    $response->assertForbidden();
});

test('cloud provider can be deleted', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $cloudProvider = CloudProvider::factory()->create([
        'workspace_id' => $workspace->id,
        'type' => CloudProviderType::HETZNER,
    ]);

    $workspace->vault()->writes()->store($cloudProvider->vault_key, 'test-credentials');

    $response = $this
        ->actingAs($user)
        ->deleteJson(route('workspaces.cloud-providers.destroy', [$workspace, $cloudProvider]));

    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'Cloud provider deleted successfully.',
    ]);

    expect($cloudProvider->fresh()->trashed())->toBeTrue();
});

test('user cannot delete cloud provider for workspace they do not own', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user1->id]);
    $cloudProvider = CloudProvider::factory()->create([
        'workspace_id' => $workspace->id,
        'type' => CloudProviderType::HETZNER,
    ]);

    $response = $this
        ->actingAs($user2)
        ->deleteJson(route('workspaces.cloud-providers.destroy', [$workspace, $cloudProvider]));

    $response->assertForbidden();
});
