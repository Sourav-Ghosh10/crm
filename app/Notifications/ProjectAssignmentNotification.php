<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ProjectAssignmentNotification extends Notification implements ShouldBroadcastNow
{
    // NOTE: Queueable trait is intentionally NOT used here.
    // ShouldBroadcastNow fires the broadcast immediately (no queue worker needed).
    // The database channel also runs synchronously without the Queueable trait.

    public $project;
    public $assignedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct($project, $assignedBy)
    {
        $this->project = $project;
        $this->assignedBy = $assignedBy;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', \App\Broadcasting\FcmChannel::class];
    }

    /**
     * Get the user's formatted role name.
     */
    protected function getRoleName(object $notifiable): string
    {
        $roleName = $notifiable->roles->first() ? $notifiable->roles->first()->display_name : $notifiable->role;
        return ucwords(str_replace('-', ' ', $roleName ?? 'Member'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $roleName = $this->getRoleName($notifiable);

        return [
            'project_id' => $this->project->id,
            'project_name' => $this->project->project_name,
            'assigned_by_id' => $this->assignedBy->id,
            'assigned_by_name' => $this->assignedBy->name,
            'title' => 'New Project Assigned',
            'message' => 'You have been assigned a new project: ' . $this->project->project_name,
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     * This fires immediately via Pusher (no queue worker needed).
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $roleName = $this->getRoleName($notifiable);

        return new BroadcastMessage([
            'project_id' => $this->project->id,
            'project_name' => $this->project->project_name,
            'assigned_by_id' => $this->assignedBy->id,
            'assigned_by_name' => $this->assignedBy->name,
            'title' => 'New Project Assigned',
            'message' => 'You have been assigned a new project: ' . $this->project->project_name,
        ]);
    }

    /**
     * Get the FCM push notification representation of the notification.
     */
    public function toFcm(object $notifiable): array
    {
        $roleName = $this->getRoleName($notifiable);

        return [
            'title' => 'New Project Assigned',
            'body' => "You have been assigned to:\n" . $this->project->project_name,
            'url' => route('crm-projects.show', ['project' => $this->project->id])
        ];
    }
}
