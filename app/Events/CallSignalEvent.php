<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallSignalEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $senderId;
    public $senderName;
    public $targetUserId;
    public $roomId;
    public $type;
    public $payload;

    /**
     * Create a new event instance.
     */
    public function __construct($senderId, $senderName, $targetUserId, $roomId, $type, $payload = null)
    {
        $this->senderId = $senderId;
        $this->senderName = $senderName;
        $this->targetUserId = $targetUserId;
        $this->roomId = $roomId;
        $this->type = $type;
        $this->payload = $payload;
    }

    /**
     * Broadcast on the target user's private channel so they receive it anywhere in the CRM.
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('App.Models.User.' . $this->targetUserId),
        ];

        if ($this->roomId) {
            $channels[] = new PrivateChannel('chat-room.' . $this->roomId);
        }

        return $channels;
    }

    /**
     * Event name for Echo listener.
     */
    public function broadcastAs(): string
    {
        return 'CallSignal';
    }

    /**
     * Get the data that should be broadcast with the event.
     */
    public function broadcastWith(): array
    {
        return [
            'senderId' => $this->senderId,
            'senderName' => $this->senderName,
            'targetUserId' => $this->targetUserId,
            'roomId' => $this->roomId,
            'type' => $this->type,
            'payload' => $this->payload,
        ];
    }
}
