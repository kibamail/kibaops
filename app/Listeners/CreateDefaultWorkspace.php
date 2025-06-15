<?php

namespace App\Listeners;

use App\Models\Workspace;
use Illuminate\Auth\Events\Registered;

class CreateDefaultWorkspace
{
    /**
     * Handle user registration events to create a default workspace.
     * This listener automatically creates a workspace with the format
     * "user's name's workspace" for every newly registered user.
     */
    public function handle(Registered $event): void
    {
        /** @var \App\Models\User|null $user */
        $user = $event->user;

        $workspaceName = $user->name."'s workspace";

        $user->workspaces()->create([
            'name' => $workspaceName,
        ]);
    }
}
