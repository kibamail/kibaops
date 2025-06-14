<?php

use App\Listeners\UpdatePendingMemberships;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMembership;
use App\Notifications\WorkspaceInvitation;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('listener updates pending memberships when user registers', function () {
    Notification::fake();

    $workspaceOwner = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $workspaceOwner->id]);
    $project = Project::factory()->create(['workspace_id' => $workspace->id]);

    WorkspaceMembership::create([
        'workspace_id' => $workspace->id,
        'email' => 'pending@example.com',
        'user_id' => null,
        'role' => 'developer',
    ]);

    $newUser = User::factory()->create(['email' => 'pending@example.com']);
    $event = new Registered($newUser);

    $listener = new UpdatePendingMemberships();
    $listener->handle($event);

    $membership = WorkspaceMembership::where('email', 'pending@example.com')->first();
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

test('listener handles multiple pending memberships for same email', function () {
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

    $newUser = User::factory()->create(['email' => 'multi@example.com']);
    $event = new Registered($newUser);

    $listener = new UpdatePendingMemberships();
    $listener->handle($event);

    $memberships = WorkspaceMembership::where('email', 'multi@example.com')->get();
    expect($memberships)->toHaveCount(2);
    expect($memberships->every(fn ($m) => $m->user_id === $newUser->id))->toBeTrue();

    Notification::assertSentToTimes($newUser, WorkspaceInvitation::class, 2);
});

test('listener does nothing when no pending memberships exist', function () {
    Notification::fake();

    $newUser = User::factory()->create(['email' => 'nopending@example.com']);
    $event = new Registered($newUser);

    $listener = new UpdatePendingMemberships();
    $listener->handle($event);

    Notification::assertNothingSent();
});

test('listener only updates memberships with matching email', function () {
    Notification::fake();

    $workspaceOwner = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $workspaceOwner->id]);

    $membership1 = WorkspaceMembership::create([
        'workspace_id' => $workspace->id,
        'email' => 'match@example.com',
        'user_id' => null,
        'role' => 'developer',
    ]);

    $membership2 = WorkspaceMembership::create([
        'workspace_id' => $workspace->id,
        'email' => 'nomatch@example.com',
        'user_id' => null,
        'role' => 'developer',
    ]);

    $newUser = User::factory()->create(['email' => 'match@example.com']);
    $event = new Registered($newUser);

    $listener = new UpdatePendingMemberships();
    $listener->handle($event);

    $membership1->refresh();
    $membership2->refresh();

    expect($membership1->user_id)->toBe($newUser->id);
    expect($membership2->user_id)->toBeNull();

    Notification::assertSentToTimes($newUser, WorkspaceInvitation::class, 1);
});

test('listener is queued for background processing', function () {
    $listener = new UpdatePendingMemberships();
    
    expect($listener)->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class);
});
