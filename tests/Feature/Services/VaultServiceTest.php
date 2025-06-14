<?php

use App\Models\Workspace;

test('workspaces can store and retrieve vault secrets', function () {
    /** @var \App\Models\Workspace|null $workspace */
    $workspace = Workspace::factory()->create();

    expect($workspace->vault())->toBe($workspace->vault());

    expect($workspace->vault()->reads())->toBe($workspace->vault()->reads());
    expect($workspace->vault()->writes())->toBe($workspace->vault()->writes());

    $apiKey = [str()->random(64)];
    $key = 'providers/hetzner';

    $workspace->vault()->writes()->store($key, $apiKey);
    $value = $workspace->vault()->reads()->secret($key);

    expect($value)->toBe($apiKey);
});

test('workspaces can store and retrieve vault secrets as arrays', function () {
    /** @var \App\Models\Workspace|null $workspace */
    $workspace = Workspace::factory()->create();

    $apiAccess = [
        'access-key' => str()->random(64),
        'access-secret' => str()->random(64),
    ];

    $key = 'providers/aws';

    $workspace->vault()->writes()->store($key, $apiAccess);

    $value = $workspace->vault()->reads()->secret($key);

    expect($value)->toBe($apiAccess);
});
