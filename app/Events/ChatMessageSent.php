<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class ChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ChatMessage $message;

    public function __construct(ChatMessage $message)
    {
        // Ensure user relation is always loaded
        $this->message = $message->loadMissing('user');
    }

    /**
     * The Pusher channels to broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat-room.' . $this->message->chat_room_id),
        ];
    }

    /**
     * Explicit payload — ensures user relation is serialized into the Pusher event.
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id'                   => $this->message->id,
                'chat_room_id'         => $this->message->chat_room_id,
                'user_id'              => $this->message->user_id,
                'message'              => $this->message->message,
                'attachment_path'      => $this->message->attachment_path,
                'attachment_name'      => $this->message->attachment_name,
                'attachment_mime_type' => $this->message->attachment_mime_type,
                'created_at'           => $this->message->created_at,
                'updated_at'           => $this->message->updated_at,
                'user' => $this->message->user ? [
                    'id'   => $this->message->user->id,
                    'name' => $this->message->user->name,
                ] : null,
            ],
        ];
    }

    /**
     * Broadcast event name — must match .listen('ChatMessageSent') on the client.
     */
    public function broadcastAs(): string
    {
        return 'ChatMessageSent';
    }
}
