<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectAssignedToUserEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $projectId;
    public int $userId;
    public string $projectName;

    /**
     * Broadcast to the newly assigned user's own private channel.
     */
    public function __construct(int $projectId, int $userId, string $projectName)
    {
        $this->projectId   = $projectId;
        $this->userId      = $userId;
        $this->projectName = $projectName;
    }

    /**
     * Dot-prefixed event name so Echo's .listen() can find it.
     */
    public function broadcastAs(): string
    {
        return 'ProjectAssignedToUser';
    }

    /**
     * Broadcast on the newly assigned user's personal private channel.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->userId),
        ];
    }

    /**
     * Payload sent to the frontend.
     */
    public function broadcastWith(): array
    {
        return [
            'project_id'   => $this->projectId,
            'project_name' => $this->projectName,
        ];
    }
}
