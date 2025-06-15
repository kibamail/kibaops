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
    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.hetzner.cloud/v1/servers' &&
               $request->header('Authorization')[0] === 'Bearer valid-hetzner-token';
    });

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.hetzner.cloud/v1/ssh_keys' &&
               $request->header('Authorization')[0] === 'Bearer valid-hetzner-token';
    });
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
    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.digitalocean.com/v2/droplets' &&
               $request->header('Authorization')[0] === 'Bearer valid-do-token';
    });

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.digitalocean.com/v2/account/keys' &&
               $request->header('Authorization')[0] === 'Bearer valid-do-token';
    });
});

test('cloud provider creation fails with invalid hetzner credentials', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    Http::fake([
        'https://api.hetzner.cloud/v1/servers' => Http::response(['error' => 'Unauthorized'], 401),
        'https://api.hetzner.cloud/v1/ssh_keys' => Http::response(['error' => 'Unauthorized'], 401),
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('workspaces.cloud-providers.store', $workspace), [
            'name' => 'Invalid Hetzner Provider',
            'type' => CloudProviderType::HETZNER->value,
            'credentials' => ['invalid-hetzner-token'],
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['credentials' => 'The provided credentials could not be verified. Please check your credentials and try again.']);

    expect(CloudProvider::count())->toBe(0);

    // Assert HTTP request was made with correct credentials (even though it failed)
    // Only the first request (servers) should be made since it fails and stops the verification
    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.hetzner.cloud/v1/servers' &&
               $request->header('Authorization')[0] === 'Bearer invalid-hetzner-token';
    });

    // The SSH keys request should NOT be made since the first request failed
    Http::assertNotSent(function ($request) {
        return $request->url() === 'https://api.hetzner.cloud/v1/ssh_keys';
    });
});

test('cloud provider creation fails with invalid digital ocean credentials', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    Http::fake([
        'https://api.digitalocean.com/v2/droplets' => Http::response(['error' => 'Unauthorized'], 401),
        'https://api.digitalocean.com/v2/account/keys' => Http::response(['error' => 'Unauthorized'], 401),
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('workspaces.cloud-providers.store', $workspace), [
            'name' => 'Invalid DO Provider',
            'type' => CloudProviderType::DIGITAL_OCEAN->value,
            'credentials' => ['invalid-do-token'],
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['credentials' => 'The provided credentials could not be verified. Please check your credentials and try again.']);

    expect(CloudProvider::count())->toBe(0);

    // Assert HTTP request was made with correct credentials (even though it failed)
    // Only the first request (droplets) should be made since it fails and stops the verification
    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.digitalocean.com/v2/droplets' &&
               $request->header('Authorization')[0] === 'Bearer invalid-do-token';
    });

    // The account keys request should NOT be made since the first request failed
    Http::assertNotSent(function ($request) {
        return $request->url() === 'https://api.digitalocean.com/v2/account/keys';
    });
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

test('hetzner cloud provider verifies both read and write access', function () {
    $http = app(HttpClient::class);
    $provider = new HetznerCloudProvider($http);

    Http::fake([
        'https://api.hetzner.cloud/v1/servers' => Http::response(['servers' => []], 200),
        'https://api.hetzner.cloud/v1/ssh_keys' => Http::response(['ssh_keys' => []], 200),
    ]);

    $result = $provider->verify(['valid-token']);
    expect($result->success)->toBeTrue();
    expect($result->message)->toBe('Hetzner Cloud credentials verified successfully');
    expect($result->attemptCount)->toBeGreaterThanOrEqual(1);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.hetzner.cloud/v1/servers' &&
               $request->header('Authorization')[0] === 'Bearer valid-token';
    });

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.hetzner.cloud/v1/ssh_keys' &&
               $request->header('Authorization')[0] === 'Bearer valid-token';
    });
});

test('digital ocean cloud provider verifies both read and write access', function () {
    $http = app(HttpClient::class);
    $provider = new DigitalOceanCloudProvider($http);

    Http::fake([
        'https://api.digitalocean.com/v2/droplets' => Http::response(['droplets' => []], 200),
        'https://api.digitalocean.com/v2/account/keys' => Http::response(['ssh_keys' => []], 200),
    ]);

    $result = $provider->verify(['valid-token']);
    expect($result->success)->toBeTrue();
    expect($result->message)->toBe('DigitalOcean credentials verified successfully');
    expect($result->attemptCount)->toBeGreaterThanOrEqual(1);

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

    // Assert HTTP requests were made with correct credentials
    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.hetzner.cloud/v1/servers' &&
               $request->header('Authorization')[0] === 'Bearer new-valid-token';
    });

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.hetzner.cloud/v1/ssh_keys' &&
               $request->header('Authorization')[0] === 'Bearer new-valid-token';
    });
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

    // Assert HTTP requests were made with correct credentials
    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.digitalocean.com/v2/droplets' &&
               $request->header('Authorization')[0] === 'Bearer new-do-token';
    });

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.digitalocean.com/v2/account/keys' &&
               $request->header('Authorization')[0] === 'Bearer new-do-token';
    });
});

test('cloud provider update fails with invalid credentials', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $cloudProvider = CloudProvider::factory()->create([
        'workspace_id' => $workspace->id,
        'type' => CloudProviderType::HETZNER,
    ]);

    Http::fake([
        'https://api.hetzner.cloud/v1/servers' => Http::response(['error' => 'Unauthorized'], 401),
        'https://api.hetzner.cloud/v1/ssh_keys' => Http::response(['error' => 'Unauthorized'], 401),
    ]);

    $response = $this
        ->actingAs($user)
        ->put(route('workspaces.cloud-providers.update', [$workspace, $cloudProvider]), [
            'credentials' => ['invalid-token'],
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['credentials']);

    // Assert HTTP request was made with correct credentials (even though it failed)
    // Only the first request (servers) should be made since it fails and stops the verification
    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.hetzner.cloud/v1/servers' &&
               $request->header('Authorization')[0] === 'Bearer invalid-token';
    });

    // The SSH keys request should NOT be made since the first request failed
    Http::assertNotSent(function ($request) {
        return $request->url() === 'https://api.hetzner.cloud/v1/ssh_keys';
    });
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

test('cloud provider handles rate limiting with retry', function () {
    $http = app(HttpClient::class);
    $provider = new HetznerCloudProvider($http);
    $provider->setRetryConfig(maxRetries: 2, baseDelayMs: 1);

    Http::fake([
        'https://api.hetzner.cloud/v1/servers' => Http::sequence()
            ->push(['error' => 'Rate limit exceeded'], 429)
            ->push(['servers' => []], 200),
        'https://api.hetzner.cloud/v1/ssh_keys' => Http::response(['ssh_keys' => []], 200),
    ]);

    $result = $provider->verify(['valid-token']);
    expect($result->success)->toBeTrue();
    expect($result->attemptCount)->toBeGreaterThan(1);
    expect($result->message)->toBe('Hetzner Cloud credentials verified successfully');
});

test('cloud provider handles server errors with retry', function () {
    $http = app(HttpClient::class);
    $provider = new DigitalOceanCloudProvider($http);
    $provider->setRetryConfig(maxRetries: 2, baseDelayMs: 1);

    Http::fake([
        'https://api.digitalocean.com/v2/droplets' => Http::sequence()
            ->push(['error' => 'Internal server error'], 500)
            ->push(['droplets' => []], 200),
        'https://api.digitalocean.com/v2/account/keys' => Http::response(['ssh_keys' => []], 200),
    ]);

    $result = $provider->verify(['valid-token']);
    expect($result->success)->toBeTrue();
    expect($result->attemptCount)->toBeGreaterThan(1);
});

test('cloud provider fails after max retries', function () {
    $http = app(HttpClient::class);
    $provider = new HetznerCloudProvider($http);
    $provider->setRetryConfig(maxRetries: 2, baseDelayMs: 1);

    Http::fake([
        'https://api.hetzner.cloud/v1/servers' => Http::response(['error' => 'Service unavailable'], 503),
        'https://api.hetzner.cloud/v1/ssh_keys' => Http::response(['ssh_keys' => []], 200),
    ]);

    $result = $provider->verify(['valid-token']);
    expect($result->success)->toBeFalse();
    expect($result->httpStatusCode)->toBe(503);
    expect($result->message)->toBe('Service unavailable - cloud provider maintenance');
    expect($result->attemptCount)->toBeGreaterThan(1);
});

test('cloud provider returns detailed error information', function () {
    $http = app(HttpClient::class);
    $provider = new HetznerCloudProvider($http);

    Http::fake([
        'https://api.hetzner.cloud/v1/servers' => Http::response([
            'error' => [
                'message' => 'Invalid API token provided',
                'code' => 'unauthorized',
            ],
        ], 401),
    ]);

    $result = $provider->verify(['invalid-token']);
    expect($result->success)->toBeFalse();
    expect($result->httpStatusCode)->toBe(401);
    expect($result->message)->toBe('Invalid credentials provided');
    expect($result->providerMessage)->toBe('Invalid API token provided');
    expect($result->getDetailedMessage())->toContain('Invalid API token provided');
    expect($result->getDetailedMessage())->toContain('HTTP 401');
});

test('cloud provider handles empty credentials', function () {
    $http = app(HttpClient::class);
    $provider = new HetznerCloudProvider($http);

    $result = $provider->verify([]);
    expect($result->success)->toBeFalse();
    expect($result->message)->toBe('No credentials provided');
    expect($result->errors)->toBe(['credentials' => 'Token is required for Hetzner Cloud']);
});

test('cloud provider handles empty token', function () {
    $http = app(HttpClient::class);
    $provider = new DigitalOceanCloudProvider($http);

    $result = $provider->verify(['']);
    expect($result->success)->toBeFalse();
    expect($result->message)->toBe('Invalid credentials provided');
    expect($result->errors)->toBe(['credentials' => 'Token cannot be empty']);
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
