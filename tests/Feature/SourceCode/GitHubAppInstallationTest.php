<?php

use App\Models\User;
use App\Models\Workspace;

test('github app installation initiation redirects to github with active workspace in session', function () {
    config(['services.github.app_name' => 'test-app']);

    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->withSession(['active_workspace_id' => $workspace->id])
        ->withHeaders(['referer' => 'http://localhost:8000/projects'])
        ->get('/workspaces/connections/github/connect');

    $response->assertRedirect();
    $redirectUrl = $response->headers->get('Location');

    expect($redirectUrl)->toContain('github.com/apps');
    expect($redirectUrl)->toContain('state=');

    // Extract and decode the state parameter to verify it contains workspace ID and origin URL
    parse_str(parse_url($redirectUrl, PHP_URL_QUERY), $queryParams);
    $stateData = json_decode(base64_decode($queryParams['state']), true);

    expect($stateData)->toHaveKey('workspace_id', $workspace->id);
    expect($stateData)->toHaveKey('origin_url', 'http://localhost:8000/projects');
});

test('github app installation initiation falls back to first workspace when no session value', function () {
    config(['services.github.app_name' => 'test-app']);

    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->get('/workspaces/connections/github/connect');

    $response->assertRedirect();
    $redirectUrl = $response->headers->get('Location');

    expect($redirectUrl)->toContain('github.com/apps');

    // Extract and decode the state parameter to verify it contains workspace ID and default origin URL
    parse_str(parse_url($redirectUrl, PHP_URL_QUERY), $queryParams);
    $stateData = json_decode(base64_decode($queryParams['state']), true);

    expect($stateData)->toHaveKey('workspace_id', $workspace->id);
    expect($stateData)->toHaveKey('origin_url', route('dashboard'));
});

test('github app installation callback logs installation details', function () {
    $response = $this->get('/workspaces/connections/github/callback?installation_id=12345&state=test-state');

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('success', 'Github connection established successfully');
});

test('github app installation callback redirects to origin url when provided', function () {
    $originUrl = 'http://localhost:8000/projects';
    $state = base64_encode(json_encode([
        'workspace_id' => 'test-workspace-id',
        'origin_url' => $originUrl,
    ]));

    $response = $this->get("/workspaces/connections/github/callback?installation_id=12345&state={$state}");

    $response->assertRedirect($originUrl);
    $response->assertSessionHas('success', 'Github connection established successfully');
});

test('github app installation callback defaults to dashboard when no origin url', function () {
    $state = base64_encode(json_encode([
        'workspace_id' => 'test-workspace-id',
    ]));

    $response = $this->get("/workspaces/connections/github/callback?installation_id=12345&state={$state}");

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('success', 'Github connection established successfully');
});

test('github connection service parses state parameter correctly', function () {
    $workspaceId = 'test-workspace-id';
    $originUrl = 'http://localhost:8000/projects';

    $service = new \App\Services\SourceCode\Providers\GitHub\GitHubConnectionService();

    // Test the complete method with encoded state
    $state = base64_encode(json_encode([
        'workspace_id' => $workspaceId,
        'origin_url' => $originUrl,
    ]));

    $response = $service->complete('12345', $state);

    expect($response->success)->toBeTrue();
    expect($response->metadata['installation_id'])->toBe('12345');
    expect($response->metadata['workspace_id'])->toBe($workspaceId);
    expect($response->metadata['origin_url'])->toBe($originUrl);
});

test('github connection service handles invalid state gracefully', function () {
    $service = new \App\Services\SourceCode\Providers\GitHub\GitHubConnectionService();

    // Test with invalid base64
    $response = $service->complete('12345', 'invalid-state');

    expect($response->success)->toBeTrue();
    expect($response->metadata['workspace_id'])->toBeNull();
    expect($response->metadata['origin_url'])->toBeNull();

    // Test with empty state
    $response = $service->complete('12345', '');

    expect($response->success)->toBeTrue();
    expect($response->metadata['workspace_id'])->toBeNull();
    expect($response->metadata['origin_url'])->toBeNull();
});

test('github webhook receives and validates signature', function () {
    config(['services.github.webhook_secret' => 'test-secret']);

    $payload = json_encode(['action' => 'opened', 'repository' => ['id' => 123, 'full_name' => 'test/repo']]);
    $signature = 'sha256=' . hash_hmac('sha256', $payload, 'test-secret');

    $response = $this->withHeaders([
        'X-Hub-Signature-256' => $signature,
        'X-GitHub-Event' => 'pull_request',
        'X-GitHub-Delivery' => 'test-delivery-id',
        'Content-Type' => 'application/json',
    ])->postJson('/workspaces/connections/github/webhooks', json_decode($payload, true));

    $response->assertStatus(200);
    $response->assertJson(['status' => 'received']);
});

test('github webhook rejects invalid signature', function () {
    config(['services.github.webhook_secret' => 'test-secret']);

    $payload = ['action' => 'opened'];
    $invalidSignature = 'sha256=invalid-signature';

    $response = $this->withHeaders([
        'X-Hub-Signature-256' => $invalidSignature,
        'X-GitHub-Event' => 'pull_request',
        'X-GitHub-Delivery' => 'test-delivery-id',
        'Content-Type' => 'application/json',
    ])->postJson('/workspaces/connections/github/webhooks', $payload);

    $response->assertStatus(401);
    $response->assertJson(['error' => 'Invalid signature']);
});

test('github webhook parses payload correctly', function () {
    $payload = [
        'action' => 'opened',
        'repository' => [
            'id' => 123456,
            'full_name' => 'test-org/test-repo',
        ],
        'ref' => 'refs/heads/main',
        'after' => 'abc123def456',
        '_github_event' => 'push',
    ];

    $provider = \App\Services\SourceCode\SourceCodeProviderFactory::create(\App\Enums\SourceCodeProviderType::GITHUB);
    $parsedEvent = $provider->webhooks()->parse(json_encode($payload));

    expect($parsedEvent->eventType)->toBe('push');
    expect($parsedEvent->action)->toBe('opened');
    expect($parsedEvent->repositoryId)->toBe('123456');
    expect($parsedEvent->repositoryName)->toBe('test-org/test-repo');
    expect($parsedEvent->rawPayload)->toBe($payload);
});

test('unsupported provider returns error', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->withCookie('active_workspace_id', $workspace->id)
        ->get('/workspaces/connections/unsupported/connect');

    $response->assertStatus(404);
});

test('webhook handles unsupported provider', function () {
    $response = $this->postJson('/workspaces/connections/unsupported/webhooks', []);

    $response->assertStatus(404);
});
