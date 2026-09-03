<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectAssignedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $projectId;
    public int $userId;

    /**
     * Broadcast to the removed user's own private channel.
     * The channel is already authorised in channels.php via App.Models.User.{id}.
     */
    public function __construct(int $projectId, int $userId)
    {
        $this->projectId = $projectId;
        $this->userId    = $userId;
    }

    /**
     * Use a dot-prefixed event name so Echo's .listen() can find it without
     * the default App\Events namespace prefix.
     */
    public function broadcastAs(): string
    {
        return 'ProjectAccessRevoked';
    }

    /**
     * Broadcast on the removed user's personal private channel —
     * the same channel already subscribed in app.blade.php for chat notifications.
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
            'project_id' => $this->projectId,
        ];
    }
}
