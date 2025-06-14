<?php

namespace App\Notifications;

use App\Models\Workspace;
use App\Models\WorkspaceMembership;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkspaceInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance for workspace membership.
     * This notification is sent when a user is added to a workspace
     * and handles both email and database notification channels.
     */
    public function __construct(
        public WorkspaceMembership $membership,
        public Workspace $workspace
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     * The email informs users they've been added to a workspace and
     * provides a link to access their dashboard.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $dashboardUrl = route('dashboard');

        return (new MailMessage)
            ->subject("You've been added to {$this->workspace->name}")
            ->greeting('Hello!')
            ->line("You have been added to the workspace '{$this->workspace->name}' as a {$this->membership->role->value}.")
            ->line('You can now access this workspace and collaborate with your team.')
            ->action('Go to Dashboard', $dashboardUrl)
            ->line('Welcome to the team!');
    }

    /**
     * Get the array representation of the notification for database storage.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'workspace_id' => $this->workspace->id,
            'workspace_name' => $this->workspace->name,
            'membership_id' => $this->membership->id,
            'role' => $this->membership->role->value,
            'added_by' => $this->workspace->user->name,
            'message' => "You've been added to {$this->workspace->name} as a {$this->membership->role->value}.",
        ];
    }
}
