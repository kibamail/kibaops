<?php

use App\Listeners\CreateDefaultWorkspace;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('listener creates default workspace when user registers', function () {
    $user = User::factory()->create(['name' => 'John Doe']);
    $event = new Registered($user);

    $listener = new CreateDefaultWorkspace;
    $listener->handle($event);

    $workspace = Workspace::where('user_id', $user->id)->first();
    
    expect($workspace)->not->toBeNull()
        ->and($workspace->name)->toBe("John Doe's workspace")
        ->and($workspace->user_id)->toBe($user->id)
        ->and($workspace->slug)->toBe('john-does-workspace');
});

test('listener creates workspace with proper slug for names with special characters', function () {
    $user = User::factory()->create(['name' => "Mary O'Connor"]);
    $event = new Registered($user);

    $listener = new CreateDefaultWorkspace;
    $listener->handle($event);

    $workspace = Workspace::where('user_id', $user->id)->first();
    
    expect($workspace)->not->toBeNull()
        ->and($workspace->name)->toBe("Mary O'Connor's workspace")
        ->and($workspace->slug)->toBe('mary-oconnors-workspace');
});

test('listener creates workspace with unique slug when duplicate names exist', function () {
    $existingUser = User::factory()->create(['name' => 'Jane Smith']);
    $existingUser->workspaces()->create(['name' => "Jane Smith's workspace"]);

    $newUser = User::factory()->create(['name' => 'Jane Smith']);
    $event = new Registered($newUser);

    $listener = new CreateDefaultWorkspace;
    $listener->handle($event);

    $newWorkspace = Workspace::where('user_id', $newUser->id)->first();
    $existingWorkspace = Workspace::where('user_id', $existingUser->id)->first();
    
    expect($newWorkspace)->not->toBeNull()
        ->and($newWorkspace->name)->toBe("Jane Smith's workspace")
        ->and($newWorkspace->slug)->not->toBe($existingWorkspace->slug)
        ->and($newWorkspace->slug)->toMatch('/^jane-smiths-workspace-[a-zA-Z0-9]{6}$/');
});

test('listener executes synchronously', function () {
    $listener = new CreateDefaultWorkspace;

    expect($listener)->not->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class);
});

test('workspace creation through registration endpoint creates default workspace', function () {
    $response = $this->postJson(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));

    $user = User::where('email', 'test@example.com')->first();
    $workspace = Workspace::where('user_id', $user->id)->first();

    expect($workspace)->not->toBeNull()
        ->and($workspace->name)->toBe("Test User's workspace")
        ->and($workspace->user_id)->toBe($user->id);
});
