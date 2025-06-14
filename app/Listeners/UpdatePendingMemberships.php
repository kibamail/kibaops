<?php

namespace App\Listeners;

use App\Models\WorkspaceMembership;
use App\Notifications\WorkspaceInvitation;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdatePendingMemberships implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle user registration events to update pending memberships.
     * This listener automatically associates new users with any pending workspace
     * memberships that match their email address and sends notifications.
     */
    public function handle(Registered $event): void
    {
        $user = $event->user;

        $pendingMemberships = WorkspaceMembership::where('email', $user->email)
            ->whereNull('user_id')
            ->with('workspace')
            ->get();

        if ($pendingMemberships->isEmpty()) {
            return;
        }

        foreach ($pendingMemberships as $membership) {
            $membership->update(['user_id' => $user->id]);

            $user->notify(new WorkspaceInvitation($membership, $membership->workspace));
        }
    }
}
