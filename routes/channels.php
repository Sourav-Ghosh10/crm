<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('project.{projectId}', function ($user, $projectId) {
    $project = \App\Models\Project::with('assignees')->find($projectId);
    
    if (!$project) {
        return false;
    }

    $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager');
    $isAssigned = $project->assignees->contains('id', $user->id);

    return $hasGlobalAccess || $isAssigned;
});

Broadcast::channel('chat-room.{roomId}', function ($user, $roomId) {
    return \App\Models\ChatRoomMember::where('chat_room_id', $roomId)
        ->where('user_id', $user->id)
        ->exists();
});
