<?php

use App\Enums\SourceCodeProviderType;
use App\Models\SourceCode\SourceCodeConnection;
use App\Models\SourceCode\SourceCodeRepository;
use App\Models\SourceCode\SourceCodeWebhookEvent;
use App\Models\Workspace;

test('source code connection can be created', function () {
    $workspace = Workspace::factory()->create();
    
    $connection = SourceCodeConnection::create([
        'workspace_id' => $workspace->id,
        'provider_type' => SourceCodeProviderType::GITHUB,
        'connection_name' => 'My GitHub Organization',
        'external_account_id' => '12345',
        'external_account_name' => 'my-org',
        'external_account_type' => 'organization',
        'avatar_url' => 'https://github.com/my-org.png',
        'permissions_scope' => ['read', 'write'],
        'vault_credentials_path' => 'source-code/github/test-id',
        'connection_status' => 'active',
        'metadata' => ['installation_id' => '67890'],
    ]);
    
    expect($connection->workspace_id)->toBe($workspace->id);
    expect($connection->provider_type)->toBe(SourceCodeProviderType::GITHUB);
    expect($connection->connection_name)->toBe('My GitHub Organization');
    expect($connection->external_account_id)->toBe('12345');
    expect($connection->external_account_name)->toBe('my-org');
    expect($connection->external_account_type)->toBe('organization');
    expect($connection->permissions_scope)->toBe(['read', 'write']);
    expect($connection->metadata)->toBe(['installation_id' => '67890']);
});

test('source code connection has correct vault key', function () {
    $connection = SourceCodeConnection::factory()->create([
        'provider_type' => SourceCodeProviderType::GITHUB,
    ]);
    
    $expectedVaultKey = "source-code/github/{$connection->id}";
    expect($connection->vault_key)->toBe($expectedVaultKey);
});

test('source code connection can store and retrieve credentials', function () {
    $connection = SourceCodeConnection::factory()->create();

    $credentials = [
        'access_token' => 'test-token',
        'refresh_token' => 'test-refresh-token',
        'expires_at' => now()->addHour()->toISOString(),
    ];

    // Mock the vault service for testing
    $mockVault = Mockery::mock();
    $mockVault->shouldReceive('writes->store')->once()->with($connection->vault_key, $credentials);
    $mockVault->shouldReceive('reads->secret')->twice()->with($connection->vault_key)->andReturn($credentials);
    app()->instance('vault', $mockVault);

    $connection->storeCredentials($credentials);

    expect($connection->vault_credentials_path)->toBe($connection->vault_key);
    expect($connection->hasValidCredentials())->toBeTrue();

    $retrievedCredentials = $connection->getCredentials();
    expect($retrievedCredentials['access_token'])->toBe('test-token');
    expect($retrievedCredentials['refresh_token'])->toBe('test-refresh-token');
});

test('source code connection has relationships', function () {
    $workspace = Workspace::factory()->create();
    $connection = SourceCodeConnection::factory()->create(['workspace_id' => $workspace->id]);
    
    expect($connection->workspace)->toBeInstanceOf(Workspace::class);
    expect($connection->workspace->id)->toBe($workspace->id);
    
    expect($connection->repositories())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    expect($connection->webhookEvents())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('source code repository can be created', function () {
    $connection = SourceCodeConnection::factory()->create();

    $repository = SourceCodeRepository::create([
        'source_code_connection_id' => $connection->id,
        'external_repository_id' => '123456',
        'name' => 'my-repo',
        'owner_repo' => 'my-org/my-repo',
        'description' => 'A test repository',
        'visibility' => 'private',
        'default_branch' => 'main',
        'clone_urls' => [
            'https' => 'https://github.com/my-org/my-repo.git',
            'ssh' => 'git@github.com:my-org/my-repo.git',
        ],
        'web_url' => 'https://github.com/my-org/my-repo',
        'language' => 'PHP',
        'topics' => ['laravel', 'php'],
        'archived' => false,
        'fork' => false,
        'repository_metadata' => ['stars' => 100],
    ]);

    expect($repository->source_code_connection_id)->toBe($connection->id);
    expect($repository->name)->toBe('my-repo');
    expect($repository->owner_repo)->toBe('my-org/my-repo');
    expect($repository->visibility)->toBe('private');
    expect($repository->clone_urls)->toBe([
        'https' => 'https://github.com/my-org/my-repo.git',
        'ssh' => 'git@github.com:my-org/my-repo.git',
    ]);
    expect($repository->topics)->toBe(['laravel', 'php']);
    expect($repository->repository_metadata)->toBe(['stars' => 100]);

    // Test the relationship
    expect($repository->sourceCodeConnection)->toBeInstanceOf(SourceCodeConnection::class);
    expect($repository->sourceCodeConnection->id)->toBe($connection->id);
});

test('source code repository has helper methods', function () {
    $repository = SourceCodeRepository::factory()->create([
        'visibility' => 'private',
        'archived' => false,
        'fork' => true,
        'webhook_configured' => true,
        'clone_urls' => [
            'https' => 'https://github.com/my-org/my-repo.git',
            'ssh' => 'git@github.com:my-org/my-repo.git',
        ],
    ]);
    
    expect($repository->isPrivate())->toBeTrue();
    expect($repository->isPublic())->toBeFalse();
    expect($repository->isArchived())->toBeFalse();
    expect($repository->isFork())->toBeTrue();
    expect($repository->hasWebhookConfigured())->toBeTrue();
    expect($repository->clone_url)->toBe('https://github.com/my-org/my-repo.git');
    expect($repository->ssh_url)->toBe('git@github.com:my-org/my-repo.git');
});

test('source code webhook event can be created', function () {
    $connection = SourceCodeConnection::factory()->create();
    $repository = SourceCodeRepository::factory()->create(['source_code_connection_id' => $connection->id]);
    
    $webhookEvent = SourceCodeWebhookEvent::create([
        'source_code_connection_id' => $connection->id,
        'source_code_repository_id' => $repository->id,
        'external_event_id' => 'event-123',
        'event_type' => 'push',
        'event_action' => 'created',
        'branch_name' => 'main',
        'commit_sha' => 'abc123',
        'payload' => ['action' => 'push', 'ref' => 'refs/heads/main'],
        'normalized_payload' => ['event' => 'push', 'branch' => 'main'],
        'processing_status' => 'pending',
    ]);
    
    expect($webhookEvent->source_code_connection_id)->toBe($connection->id);
    expect($webhookEvent->source_code_repository_id)->toBe($repository->id);
    expect($webhookEvent->event_type)->toBe('push');
    expect($webhookEvent->event_action)->toBe('created');
    expect($webhookEvent->branch_name)->toBe('main');
    expect($webhookEvent->commit_sha)->toBe('abc123');
    expect($webhookEvent->payload)->toBe(['action' => 'push', 'ref' => 'refs/heads/main']);
    expect($webhookEvent->normalized_payload)->toBe(['event' => 'push', 'branch' => 'main']);
});

test('source code webhook event has status methods', function () {
    $webhookEvent = SourceCodeWebhookEvent::factory()->create([
        'processing_status' => 'pending',
        'processing_attempts' => 0,
    ]);

    expect($webhookEvent->isPending())->toBeTrue();
    expect($webhookEvent->isProcessing())->toBeFalse();
    expect($webhookEvent->isCompleted())->toBeFalse();
    expect($webhookEvent->isFailed())->toBeFalse();

    $webhookEvent->markAsProcessing();
    expect($webhookEvent->isProcessing())->toBeTrue();
    expect($webhookEvent->processing_attempts)->toBe(1);

    $webhookEvent->markAsCompleted();
    expect($webhookEvent->isCompleted())->toBeTrue();
    expect($webhookEvent->processed_at)->not->toBeNull();
});

test('source code provider factory creates correct providers', function () {
    $factory = new \App\Services\SourceCode\SourceCodeProviderFactory();
    
    $githubProvider = $factory->create(SourceCodeProviderType::GITHUB);
    expect($githubProvider)->toBeInstanceOf(\App\Services\SourceCode\Providers\GitHubProvider::class);
    
    expect(fn() => $factory->create(SourceCodeProviderType::BITBUCKET))
        ->toThrow(InvalidArgumentException::class, 'Provider bitbucket not implemented yet');
});
