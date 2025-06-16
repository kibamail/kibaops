<?php

use App\Enums\CloudProviderType;
use App\Models\CloudProvider;
use App\Models\User;
use App\Models\Workspace;
use App\Jobs\DeleteTestSshKeyJob;
use App\Services\CloudProviders\CloudProviderFactory;
use App\Services\CloudProviders\DigitalOcean\DigitalOceanCloudProvider;
use App\Services\CloudProviders\Hetzner\HetznerCloudProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use LKDev\HetznerCloud\Models\SSHKeys\SSHKey;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Mock HetznerCloud service for all tests to avoid real API calls
    $mockService = mock();

    $mockSshKey = mock(SSHKey::class);
    $mockSshKey->shouldReceive('getAttribute')
        ->with('id')
        ->andReturn('12345');
    $mockSshKey->id = '12345';

    $mockService->shouldReceive('createSshKey')
        ->andReturn($mockSshKey);
    $mockService->shouldReceive('deleteSshKey')
        ->andReturn(true);
    $mockService->shouldReceive('createLabel')
        ->andReturn(true);
    $mockService->shouldReceive('updateLabel')
        ->andReturn(true);
    $mockService->shouldReceive('deleteLabel')
        ->andReturn(true);

    app()->instance('hetzner-cloud-mock', $mockService);
});

test('cloud provider can be created with valid hetzner credentials', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    Http::fake([
        'https://api.hetzner.cloud/v1/servers' => Http::response(['servers' => []], 200),
        'https://api.hetzner.cloud/v1/ssh_keys' => Http::response(['ssh_keys' => []], 200),
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('workspaces.cloud-providers.store', $workspace), [
            'name' => 'My Hetzner Provider',
            'type' => CloudProviderType::HETZNER->value,
            'credentials' => ['valid-hetzner-token'],
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Cloud provider created successfully.');

    $cloudProvider = CloudProvider::first();
    expect($cloudProvider)->not->toBeNull()
        ->and($cloudProvider->name)->toBe('My Hetzner Provider')
        ->and($cloudProvider->type)->toBe(CloudProviderType::HETZNER)
        ->and($cloudProvider->workspace_id)->toBe($workspace->id);

    $storedCredentials = $workspace->vault()->reads()->secret($cloudProvider->vault_key);
    expect($storedCredentials)->toBe(['valid-hetzner-token']);

    // Assert HTTP requests were made with correct credentials

});

test('cloud provider can be created with valid digital ocean credentials', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    Http::fake([
        'https://api.digitalocean.com/v2/droplets' => Http::response(['droplets' => []], 200),
        'https://api.digitalocean.com/v2/account/keys' => Http::response(['ssh_keys' => []], 200),
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('workspaces.cloud-providers.store', $workspace), [
            'name' => 'My DigitalOcean Provider',
            'type' => CloudProviderType::DIGITAL_OCEAN->value,
            'credentials' => ['valid-do-token'],
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Cloud provider created successfully.');

    $cloudProvider = CloudProvider::first();
    expect($cloudProvider)->not->toBeNull()
        ->and($cloudProvider->name)->toBe('My DigitalOcean Provider')
        ->and($cloudProvider->type)->toBe(CloudProviderType::DIGITAL_OCEAN)
        ->and($cloudProvider->workspace_id)->toBe($workspace->id);

    $storedCredentials = $workspace->vault()->reads()->secret($cloudProvider->vault_key);
    expect($storedCredentials)->toBe(['valid-do-token']);

    // Assert HTTP requests were made with correct credentials

});

test('cloud provider creation fails for unimplemented provider types', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->post(route('workspaces.cloud-providers.store', $workspace), [
            'name' => 'AWS Provider',
            'type' => CloudProviderType::AWS->value,
            'credentials' => ['access-key', 'secret-key'],
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['type' => "Cloud provider type 'Amazon web services' is not implemented yet."]);

    expect(CloudProvider::count())->toBe(0);
});

test('cloud provider creation requires valid input', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->post(route('workspaces.cloud-providers.store', $workspace), [
            'name' => '',
            'type' => 'invalid-type',
            'credentials' => [],
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['name', 'type', 'credentials']);
});

test('cloud provider name cannot exceed 32 characters', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->post(route('workspaces.cloud-providers.store', $workspace), [
            'name' => str_repeat('a', 33),
            'type' => CloudProviderType::HETZNER->value,
            'credentials' => ['some-token'],
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['name']);
});

test('user cannot create cloud provider for workspace they do not own', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user1->id]);

    $response = $this
        ->actingAs($user2)
        ->post(route('workspaces.cloud-providers.store', $workspace), [
            'name' => 'Unauthorized Provider',
            'type' => CloudProviderType::HETZNER->value,
            'credentials' => ['some-token'],
        ]);

    $response->assertForbidden();
});

test('hetzner cloud provider verifies credentials successfully', function () {
    $provider = new HetznerCloudProvider;
    $result = $provider->verify(['valid-token']);

    expect($result->success)->toBeTrue();
    expect($result->message)->toBe('Hetzner Cloud credentials verified successfully');
});

test('hetzner cloud provider dispatches job to delete test ssh key', function () {
    Queue::fake();

    $provider = new HetznerCloudProvider;
    $result = $provider->verify(['valid-token']);

    expect($result->success)->toBeTrue();

    Queue::assertPushed(DeleteTestSshKeyJob::class, function ($job) {
        return $job->token === 'valid-token' && $job->keyId === '12345';
    });
});

test('hetzner cloud provider fails verification when ssh key creation fails', function () {
    // Override the global mock to return null for this test
    $mockService = mock();
    $mockService->shouldReceive('createSshKey')
        ->once()
        ->andReturn(null);

    app()->instance('hetzner-cloud-mock', $mockService);

    $provider = new HetznerCloudProvider;
    $result = $provider->verify(['invalid-token']);

    expect($result->success)->toBeFalse();
    expect($result->message)->toBe('Failed to verify Hetzner Cloud credentials');
});

test('digital ocean cloud provider verifies credentials successfully', function () {
    $provider = new DigitalOceanCloudProvider;

    $result = $provider->verify(['valid-token']);
    expect($result->success)->toBeTrue();
    expect($result->message)->toBe('DigitalOcean credentials verified successfully');
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
        ->put(route('workspaces.cloud-providers.update', [$workspace, $cloudProvider]), [
            'name' => 'Updated Provider Name',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Cloud provider updated successfully.');

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
        'https://api.hetzner.cloud/v1/servers' => Http::response(['servers' => []], 200),
        'https://api.hetzner.cloud/v1/ssh_keys' => Http::response(['ssh_keys' => []], 200),
    ]);

    $response = $this
        ->actingAs($user)
        ->put(route('workspaces.cloud-providers.update', [$workspace, $cloudProvider]), [
            'credentials' => ['new-valid-token'],
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Cloud provider updated successfully.');

    $storedCredentials = $workspace->vault()->reads()->secret($cloudProvider->vault_key);
    expect($storedCredentials)->toBe(['new-valid-token']);

});

test('cloud provider can be updated with both name and credentials', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $cloudProvider = CloudProvider::factory()->create([
        'workspace_id' => $workspace->id,
        'type' => CloudProviderType::DIGITAL_OCEAN,
    ]);

    Http::fake([
        'https://api.digitalocean.com/v2/droplets' => Http::response(['droplets' => []], 200),
        'https://api.digitalocean.com/v2/account/keys' => Http::response(['ssh_keys' => []], 200),
    ]);

    $response = $this
        ->actingAs($user)
        ->put(route('workspaces.cloud-providers.update', [$workspace, $cloudProvider]), [
            'name' => 'Updated DO Provider',
            'credentials' => ['new-do-token'],
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Cloud provider updated successfully.');

    $cloudProvider->refresh();
    expect($cloudProvider->name)->toBe('Updated DO Provider');

    $storedCredentials = $workspace->vault()->reads()->secret($cloudProvider->vault_key);
    expect($storedCredentials)->toBe(['new-do-token']);

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
        ->put(route('workspaces.cloud-providers.update', [$workspace, $cloudProvider]), []);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['name']);
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
        ->put(route('workspaces.cloud-providers.update', [$workspace, $cloudProvider]), [
            'name' => 'Unauthorized Update',
        ]);

    $response->assertForbidden();
});

test('cloud provider handles empty credentials', function () {
    $provider = new HetznerCloudProvider;

    $result = $provider->verify([]);
    expect($result->success)->toBeFalse();
    expect($result->message)->toBe('No credentials provided');
});

test('cloud provider handles empty token', function () {
    $provider = new HetznerCloudProvider;

    $result = $provider->verify(['']);
    expect($result->success)->toBeFalse();
    expect($result->message)->toBe('Invalid credentials provided');
});

test('cloud provider can be deleted', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $cloudProvider = CloudProvider::factory()->create([
        'workspace_id' => $workspace->id,
        'type' => CloudProviderType::HETZNER,
    ]);

    $workspace->vault()->writes()->store($cloudProvider->vault_key, ['test-credentials']);

    $response = $this
        ->actingAs($user)
        ->delete(route('workspaces.cloud-providers.destroy', [$workspace, $cloudProvider]));

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Cloud provider deleted successfully.');

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
        ->delete(route('workspaces.cloud-providers.destroy', [$workspace, $cloudProvider]));

    $response->assertForbidden();
});
