<?php
$content = file_get_contents('resources/views/chat/index.blade.php');

$content = str_replace(
    '<div class="space-y-0.5 mt-1">
                        @foreach($rooms->where(\'is_group\', true) as $r)',
    '<div class="space-y-0.5 mt-1" id="channel-list-container">
                        @foreach($rooms->where(\'is_group\', true) as $r)',
    $content
);

$content = str_replace(
    '<div class="space-y-0.5 mt-1">
                        @foreach($rooms->where(\'is_group\', false) as $r)',
    '<div class="space-y-0.5 mt-1" id="dm-list-container">
                        @foreach($rooms->where(\'is_group\', false) as $r)',
    $content
);

$content = str_replace(
    '<a href="{{ route(\'chat.index\', [\'room_id\' => $r->id]) }}"',
    '<a href="{{ route(\'chat.index\', [\'room_id\' => $r->id]) }}" id="room-link-{{ $r->id }}"',
    $content
);

$content = str_replace('this.playNotificationSound();', '', $content);
$content = str_replace('this.initAudioContext();', '', $content);

file_put_contents('resources/views/chat/index.blade.php', $content);
echo "Done\n";
