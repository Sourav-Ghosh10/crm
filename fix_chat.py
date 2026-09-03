import sys
content = open('resources/views/chat/index.blade.php', 'r', encoding='utf-8').read()

# Replace channel container
content = content.replace(
    '<div class="space-y-0.5 mt-1">\n                        @foreach($rooms->where(\'is_group\', true) as $r)',
    '<div class="space-y-0.5 mt-1" id="channel-list-container">\n                        @foreach($rooms->where(\'is_group\', true) as $r)'
)

# Replace dm container
content = content.replace(
    '<div class="space-y-0.5 mt-1">\n                        @foreach($rooms->where(\'is_group\', false) as $r)',
    '<div class="space-y-0.5 mt-1" id="dm-list-container">\n                        @foreach($rooms->where(\'is_group\', false) as $r)'
)

# Replace room links
content = content.replace(
    '<a href="{{ route(\'chat.index\', [\'room_id\' => $r->id]) }}"',
    '<a href="{{ route(\'chat.index\', [\'room_id\' => $r->id]) }}" id="room-link-{{ $r->id }}"'
)

# Remove playNotificationSound()
content = content.replace('this.playNotificationSound();', '')

# Remove initAudioContext from init
content = content.replace('this.initAudioContext();', '')

open('resources/views/chat/index.blade.php', 'w', encoding='utf-8').write(content)
