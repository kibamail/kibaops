<?php

use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMembership;
use App\Notifications\WorkspaceInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('existing users receive email and database notifications when added to workspace', function () {
    Notification::fake();

    $workspaceOwner = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $workspaceOwner->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $existingUser = User::factory()->create(['email' => 'existing@example.com']);

    $response = $this
        ->actingAs($workspaceOwner)
        ->postJson(route('workspaces.memberships.store', $workspace), [
            'emails' => ['existing@example.com'],
            'project_ids' => [$project->id],
            'role' => 'developer',
        ]);

    $response->assertStatus(200);

    $membership = WorkspaceMembership::where('email', 'existing@example.com')->first();
    expect($membership->user_id)->toBe($existingUser->id);

    Notification::assertSentTo(
        $existingUser,
        WorkspaceInvitation::class,
        function ($notification) use ($workspace, $membership) {
            return $notification->workspace->id === $workspace->id &&
                   $notification->membership->id === $membership->id;
        }
    );
});

test('new users do not receive notifications immediately when invited', function () {
    Notification::fake();

    $workspaceOwner = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $workspaceOwner->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this
        ->actingAs($workspaceOwner)
        ->postJson(route('workspaces.memberships.store', $workspace), [
            'emails' => ['newuser@example.com'],
            'project_ids' => [$project->id],
            'role' => 'developer',
        ]);

    $response->assertStatus(200);

    $membership = WorkspaceMembership::where('email', 'newuser@example.com')->first();
    expect($membership->user_id)->toBeNull();

    Notification::assertNothingSent();
});

test('new users receive notifications when they register with pending memberships', function () {
    Notification::fake();

    $workspaceOwner = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $workspaceOwner->id]);

    WorkspaceMembership::create([
        'workspace_id' => $workspace->id,
        'email' => 'newuser@example.com',
        'user_id' => null,
        'role' => 'developer',
    ]);

    $response = $this->postJson(route('register'), [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));

    $newUser = User::where('email', 'newuser@example.com')->first();
    expect($newUser)->not->toBeNull();

    $membership = WorkspaceMembership::where('email', 'newuser@example.com')->first();
    expect($membership->user_id)->toBe($newUser->id);

    Notification::assertSentTo(
        $newUser,
        WorkspaceInvitation::class,
        function ($notification) use ($workspace, $membership) {
            return $notification->workspace->id === $workspace->id &&
                   $notification->membership->id === $membership->id;
        }
    );
});

test('workspace invitation email contains correct content', function () {
    $workspaceOwner = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $workspaceOwner->id]);
    $user = User::factory()->create();

    $membership = WorkspaceMembership::create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'email' => $user->email,
        'role' => 'developer',
    ]);

    $notification = new WorkspaceInvitation($membership, $workspace);
    $mailMessage = $notification->toMail($user);

    expect($mailMessage->subject)->toBe("You've been added to {$workspace->name}")
        ->and($mailMessage->greeting)->toBe('Hello!')
        ->and($mailMessage->introLines[0])->toContain($workspace->name)
        ->and($mailMessage->introLines[0])->toContain('developer')
        ->and($mailMessage->actionText)->toBe('Go to Dashboard')
        ->and($mailMessage->actionUrl)->toBe(route('dashboard'));
});

test('workspace invitation database notification contains correct data', function () {
    $workspaceOwner = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $workspaceOwner->id]);
    $user = User::factory()->create();

    $membership = WorkspaceMembership::create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'email' => $user->email,
        'role' => 'admin',
    ]);

    $notification = new WorkspaceInvitation($membership, $workspace);
    $data = $notification->toArray($user);

    expect($data)->toHaveKeys([
        'workspace_id',
        'workspace_name',
        'membership_id',
        'role',
        'added_by',
        'message',
    ])
        ->and($data['workspace_id'])->toBe($workspace->id)
        ->and($data['workspace_name'])->toBe($workspace->name)
        ->and($data['membership_id'])->toBe($membership->id)
        ->and($data['role'])->toBe('admin')
        ->and($data['added_by'])->toBe($workspaceOwner->name)
        ->and($data['message'])->toContain($workspace->name)
        ->and($data['message'])->toContain('admin');
});

test('multiple pending memberships are updated when user registers', function () {
    Notification::fake();

    $workspaceOwner1 = User::factory()->create();
    $workspaceOwner2 = User::factory()->create();
    $workspace1 = Workspace::factory()->create(['user_id' => $workspaceOwner1->id]);
    $workspace2 = Workspace::factory()->create(['user_id' => $workspaceOwner2->id]);

    WorkspaceMembership::create([
        'workspace_id' => $workspace1->id,
        'email' => 'multi@example.com',
        'user_id' => null,
        'role' => 'developer',
    ]);

    WorkspaceMembership::create([
        'workspace_id' => $workspace2->id,
        'email' => 'multi@example.com',
        'user_id' => null,
        'role' => 'admin',
    ]);

    $memberships = WorkspaceMembership::where('email', 'multi@example.com')->get();
    expect($memberships)->toHaveCount(2);
    expect($memberships->every(fn ($m) => $m->user_id === null))->toBeTrue();

    $response = $this->postJson(route('register'), [
        'name' => 'Multi User',
        'email' => 'multi@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));

    $newUser = User::where('email', 'multi@example.com')->first();
    $updatedMemberships = WorkspaceMembership::where('email', 'multi@example.com')->get();

    expect($updatedMemberships->every(fn ($m) => $m->user_id === $newUser->id))->toBeTrue();

    Notification::assertSentToTimes($newUser, WorkspaceInvitation::class, 2);
});

test('notification is queued for background processing', function () {
    $workspaceOwner = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $workspaceOwner->id]);
    $user = User::factory()->create();

    $membership = WorkspaceMembership::create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'email' => $user->email,
        'role' => 'developer',
    ]);

    $notification = new WorkspaceInvitation($membership, $workspace);

    expect($notification)->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class);
});
