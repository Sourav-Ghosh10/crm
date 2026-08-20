<?php
$filepath = 'resources/views/layouts/app.blade.php';
$content = file_get_contents($filepath);

$pattern = '/(<span class="font-medium">Chat<\/span>)\s*(<\/x-nav-link>)/';
$replacement = '$1
                                <span class="chat-unread-badge bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full ml-auto shadow-sm" style="display:none;">0</span>
                            $2';

$content = preg_replace($pattern, $replacement, $content);
file_put_contents($filepath, $content);
echo "Added chat-unread-badge to Chat nav link.\n";
