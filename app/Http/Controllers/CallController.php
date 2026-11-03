<?php

namespace App\Http\Controllers;

use App\Events\CallSignalEvent;
use App\Events\ChatMessageSent;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\User;
use Illuminate\Http\Request;

class CallController extends Controller
{
    /**
     * Relay WebRTC signaling events between peers via Pusher.
     */
    public function signal(Request $request)
    {
        $data = $request->validate([
            'target_user_id' => 'required|integer|exists:users,id',
            'room_id'        => 'nullable|integer',
            'type'           => 'required|string',
            'payload'        => 'nullable',
        ]);

        $currentUser = auth()->user();

        \Illuminate\Support\Facades\Log::info('[CallController@signal] Received signal request:', [
            'sender_id'      => $currentUser->id,
            'sender_name'    => $currentUser->name,
            'target_user_id' => $data['target_user_id'],
            'room_id'        => $data['room_id'] ?? null,
            'type'           => $data['type'],
            'payload'        => $data['payload'] ?? null,
        ]);

        $signalEvent = new CallSignalEvent(
            $currentUser->id,
            $currentUser->name,
            $data['target_user_id'],
            $data['room_id'] ?? null,
            $data['type'],
            $data['payload'] ?? null
        );

        event($signalEvent);

        \Illuminate\Support\Facades\Log::info('[CallController@signal] CallSignalEvent dispatched successfully.');

        return response()->json(['success' => true]);
    }

    /**
     * Record completed/missed call into the chat message history.
     */
    public function logCall(Request $request)
    {
        $data = $request->validate([
            'room_id'          => 'required|integer|exists:chat_rooms,id',
            'call_type'        => 'required|string|in:audio,video',
            'duration_seconds' => 'nullable|integer|min:0',
            'status'           => 'required|string|in:completed,missed,declined,busy',
        ]);

        $user = auth()->user();
        $room = ChatRoom::findOrFail($data['room_id']);

        // Only members of the room can log messages
        if (!$room->members()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $callTypeLabel = ucfirst($data['call_type']);
        $icon = $data['call_type'] === 'video' ? '📹' : '📞';

        if ($data['status'] === 'completed') {
            $duration = (int) ($data['duration_seconds'] ?? 0);
            $mins = floor($duration / 60);
            $secs = $duration % 60;
            $durationFormatted = ($mins > 0 ? "{$mins}m " : "") . "{$secs}s";
            $messageText = "{$icon} {$callTypeLabel} call • {$durationFormatted}";
        } elseif ($data['status'] === 'missed') {
            $messageText = "{$icon} Missed {$data['call_type']} call";
        } elseif ($data['status'] === 'declined') {
            $messageText = "{$icon} Declined {$data['call_type']} call";
        } else {
            $messageText = "{$icon} {$callTypeLabel} call unavailable";
        }

        $chatMessage = ChatMessage::create([
            'chat_room_id' => $room->id,
            'user_id'      => $user->id,
            'message'      => $messageText,
        ]);

        // Broadcast to chat room so thread updates in real-time
        broadcast(new ChatMessageSent($chatMessage->load('user')))->toOthers();

        return response()->json(['success' => true, 'message' => $chatMessage]);
    }
}
