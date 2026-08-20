<?php
$filepath = 'app/Http/Controllers/ChatController.php';
$content = file_get_contents($filepath);

$pattern = '/(ChatRoomMember::create\(\[.*?\]\);)/s';
$replacement = '$1
            // Broadcast to the new user so their UI updates immediately
            broadcast(new \App\Events\UserAddedToGroup($room, $newUserId, $user->name));';

$content = preg_replace($pattern, $replacement, $content, 1);
file_put_contents($filepath, $content);
echo "Updated ChatController via regex\n";
