<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use App\Models\ChatRoomMember;
use App\Models\ChatMessage;
use App\Models\User;
use App\Events\ChatMessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Fetch all rooms the user is a member of
        $rooms = $user->chatRooms()
            ->with(['members', 'messages' => function($q) {
                $q->latest()->limit(1);
            }])
            ->withCount(['messages as unread_count' => function ($query) use ($user) {
                $query->where('user_id', '!=', $user->id)
                      ->where(function($q) {
                          $q->whereNull('chat_room_members.last_read_at')
                            ->orWhereColumn('chat_messages.created_at', '>', 'chat_room_members.last_read_at');
                      });
            }])
            ->get()
            ->map(function($room) use ($user) {
                // Determine DM name
                if (!$room->is_group) {
                    $otherMember = $room->members->firstWhere('id', '!=', $user->id);
                    $room->display_name = $otherMember ? $otherMember->name : 'Deleted User';
                } else {
                    $room->display_name = $room->name;
                }
                
                // Get latest message
                $room->latest_message = $room->messages->first();
                return $room;
            })
            ->sortByDesc(function ($room) {
                return $room->latest_message ? $room->latest_message->created_at : $room->created_at;
            })
            ->values();

        // Fetch all other users for starting DMs
        $users = User::where('id', '!=', $user->id)
            ->orderBy('name', 'asc')
            ->get();

        $selectedRoomId = $request->get('room_id');
        $selectedRoom = null;
        $messages = collect();

        if ($selectedRoomId) {
            // Retrieve selected room if user is a member
            $selectedRoom = $user->chatRooms()
                ->with('members')
                ->find($selectedRoomId);

            if ($selectedRoom) {
                // Mark as read
                $user->chatRooms()->updateExistingPivot($selectedRoomId, ['last_read_at' => now()]);
                
                // Clear unread count for the active room in the sidebar list too
                if ($sidebarRoom = $rooms->firstWhere('id', $selectedRoomId)) {
                    $sidebarRoom->unread_count = 0;
                }
                if (!$selectedRoom->is_group) {
                    $otherMember = $selectedRoom->members->firstWhere('id', '!=', $user->id);
                    $selectedRoom->display_name = $otherMember ? $otherMember->name : 'Deleted User';
                } else {
                    $selectedRoom->display_name = $selectedRoom->name;
                }
                
                $messages = $selectedRoom->messages()
                    ->with('user')
                    ->orderBy('created_at', 'asc')
                    ->get();
            }
        }

        return view('chat.index', compact('rooms', 'users', 'selectedRoom', 'messages'));
    }

    public function storeRoom(Request $request)
    {
        $user = auth()->user();
        $type = $request->input('type', 'dm');

        if ($type === 'group') {
            $request->validate([
                'name' => 'required|string|max:100',
                'user_ids' => 'required|array',
                'user_ids.*' => 'exists:users,id',
            ]);

            $room = DB::transaction(function() use ($request, $user) {
                $room = ChatRoom::create([
                    'name' => $request->name,
                    'is_group' => true,
                    'created_by' => $user->id,
                ]);

                // Attach members
                $memberIds = array_merge([$user->id], $request->user_ids);
                $room->members()->sync($memberIds);

                return $room;
            });

            return redirect()->route('chat.index', ['room_id' => $room->id]);
        }

        // Direct Message
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $targetUserId = $request->user_id;

        if ((int)$targetUserId === $user->id) {
            return redirect()->back()->withErrors('You cannot start a chat with yourself.');
        }

        // Check if DM room already exists between these two users
        $existingRoom = ChatRoom::where('is_group', false)
            ->whereHas('members', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->whereHas('members', function($q) use ($targetUserId) {
                $q->where('user_id', $targetUserId);
            })
            ->first();

        if ($existingRoom) {
            return redirect()->route('chat.index', ['room_id' => $existingRoom->id]);
        }

        // Create new DM room
        $room = DB::transaction(function() use ($user, $targetUserId) {
            $room = ChatRoom::create([
                'is_group' => false,
            ]);

            $room->members()->sync([$user->id, $targetUserId]);
            return $room;
        });

        return redirect()->route('chat.index', ['room_id' => $room->id]);
    }

    public function sendMessage(Request $request, ChatRoom $room)
    {
        $user = auth()->user();

        // Authorize member access
        if (!$room->members->contains('id', $user->id)) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'message' => 'nullable|string|max:2000|required_without:attachment',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $attachment = $request->file('attachment');

        $message = ChatMessage::create([
            'chat_room_id' => $room->id,
            'user_id' => $user->id,
            'message' => $request->input('message') ?? '',
            'attachment_path' => $attachment ? $attachment->store('chat-attachments', 'public') : null,
            'attachment_name' => $attachment?->getClientOriginalName(),
            'attachment_mime_type' => $attachment?->getClientMimeType(),
        ]);

        $message->load('user');

        broadcast(new ChatMessageSent($message))->toOthers();

        // Send FCM Push Notification
        $otherMembers = $room->members()->where('users.id', '!=', $user->id)->get();
        foreach ($otherMembers as $member) {
            if ($member->fcm_token) {
                \App\Services\FcmService::sendNotification(
                    $member->fcm_token,
                    "New message from " . $user->name,
                    strlen($message->message) > 0 ? $message->message : ($message->attachment_name ?? 'Attachment'),
                    [
                        'room_id' => (string) $room->id,
                        'type' => 'chat'
                    ]
                );
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => $message
        ]);
    }

    public function attachment(ChatMessage $message, string $filename)
    {
        $user = auth()->user();

        if (!$message->room->members()->whereKey($user->id)->exists() || !$message->attachment_path) {
            abort(403, 'Unauthorized access.');
        }

        abort_unless(Storage::disk('public')->exists($message->attachment_path), 404, 'Attachment not found.');

        return Storage::disk('public')->response(
            $message->attachment_path,
            $message->attachment_name,
            ['Content-Type' => $message->attachment_mime_type ?: Storage::disk('public')->mimeType($message->attachment_path)],
            'inline'
        );
    }

    public function downloadAttachment(ChatMessage $message)
    {
        $user = auth()->user();

        if (!$message->room->members()->whereKey($user->id)->exists() || !$message->attachment_path) {
            abort(403, 'Unauthorized access.');
        }

        abort_unless(Storage::disk('public')->exists($message->attachment_path), 404, 'Attachment not found.');

        return Storage::disk('public')->download(
            $message->attachment_path,
            $message->attachment_name,
            ['Content-Type' => $message->attachment_mime_type ?: Storage::disk('public')->mimeType($message->attachment_path)]
        );
    }

    public function roomMembers(ChatRoom $room)
    {
        $user = auth()->user();

        // Ensure the requesting user is a member of this room
        $memberIds = ChatRoomMember::where('chat_room_id', $room->id)->pluck('user_id');

        if (!$memberIds->contains($user->id)) {
            abort(403, 'Unauthorized access.');
        }

        // Fetch users directly — avoids any belongsToMany pivot column conflicts
        $members = User::whereIn('id', $memberIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn($u) => [
                'id'    => $u->id,
                'name'  => $u->name,
                'email' => $u->email,
            ]);

        return response()->json([
            'room_id'   => $room->id,
            'room_name' => $room->name,
            'is_group'  => $room->is_group,
            'members'   => $members,
        ]);
    }

    public function addMember(Request $request, ChatRoom $room)
    {
        $user = auth()->user();

        // Only group rooms support member management
        if (!$room->is_group) {
            return response()->json(['error' => 'Not a group room.'], 422);
        }

        // Requester must be a member
        $currentIds = ChatRoomMember::where('chat_room_id', $room->id)->pluck('user_id');
        if (!$currentIds->contains($user->id)) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate(['user_id' => 'required|exists:users,id']);
        $newUserId = (int) $request->user_id;

        // Avoid duplicate
        if (!$currentIds->contains($newUserId)) {
            ChatRoomMember::create([
                'chat_room_id' => $room->id,
                'user_id'      => $newUserId,
            ]);
            // Broadcast to the new user so their UI updates immediately
            broadcast(new \App\Events\UserAddedToGroup($room, $newUserId, $user->name));
        }

        // Return refreshed member list
        $memberIds = ChatRoomMember::where('chat_room_id', $room->id)->pluck('user_id');
        $members   = User::whereIn('id', $memberIds)->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email]);

        return response()->json(['members' => $members]);
    }

    public function removeMember(ChatRoom $room, User $user)
    {
        $requester = auth()->user();

        // Only group rooms
        if (!$room->is_group) {
            return response()->json(['error' => 'Not a group room.'], 422);
        }

        // Only Admin, Manager, or Project Manager can remove members
        $canRemove = $requester->isAdmin()
            || $requester->isManager()
            || $requester->hasRole('project-manager');

        if (!$canRemove) {
            return response()->json(['error' => 'Only Admins and Managers can remove members.'], 403);
        }

        // Requester must be a member
        $currentIds = ChatRoomMember::where('chat_room_id', $room->id)->pluck('user_id');
        if (!$currentIds->contains($requester->id)) {
            abort(403, 'Unauthorized access.');
        }

        // Cannot remove yourself
        if ($user->id === $requester->id) {
            return response()->json(['error' => 'You cannot remove yourself.'], 422);
        }

        ChatRoomMember::where('chat_room_id', $room->id)
            ->where('user_id', $user->id)
            ->delete();

        // Return refreshed member list
        $memberIds = ChatRoomMember::where('chat_room_id', $room->id)->pluck('user_id');
        $members   = User::whereIn('id', $memberIds)->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email]);

        return response()->json(['members' => $members]);
    }
}
