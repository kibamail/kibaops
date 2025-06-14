<?php

use App\Models\User;
use App\Models\Workspace;
use App\Services\Vault\VaultService;

test('workspaces can create vault services as singletons', function () {
    /** @var \App\Models\Workspace|null $workspace */
    $workspace = Workspace::factory()->create();

    expect($workspace->vault())->toBe($workspace->vault());

    expect($workspace->vault()->reads())->toBe($workspace->vault()->reads());
    expect($workspace->vault()->writes())->toBe($workspace->vault()->writes());
});
