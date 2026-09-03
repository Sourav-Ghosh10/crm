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
        // Ensure user and room relation is always loaded
        $this->message = $message->loadMissing(['user', 'room']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('chat-room.' . $this->message->chat_room_id),
        ];

        // Also broadcast to each room member's individual channel
        $memberIds = \App\Models\ChatRoomMember::where('chat_room_id', $this->message->chat_room_id)
            ->pluck('user_id');

        foreach ($memberIds as $userId) {
            $channels[] = new PrivateChannel('App.Models.User.' . $userId);
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'ChatMessageSent';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
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
                'user'         => $this->message->user ? [
                    'id'   => $this->message->user->id,
                    'name' => $this->message->user->name,
                ] : null,
                'room' => $this->message->room ? [
                    'id'       => $this->message->room->id,
                    'name'     => $this->message->room->name,
                    'is_group' => $this->message->room->is_group,
                ] : null,
            ],
        ];
    }

}
