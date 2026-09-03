<?php
$filepath = 'resources/views/layouts/app.blade.php';
$content = file_get_contents($filepath);

$pattern1 = '/audio\.currentTime = 0;\s*audio\.play\(\)\.catch\(err => console\.log\(\'Audio autoplay blocked:\', err\)\);/s';
$replacement1 = 'playNotificationSound();';

$pattern2 = '/if \(typeof audio !== \'undefined\'\) \{\s*audio\.currentTime = 0;\s*audio\.play\(\)\.catch\(err => console\.log\(\'Audio autoplay blocked:\', err\)\);\s*\}/s';
$replacement2 = 'playNotificationSound();';

$content = preg_replace($pattern1, $replacement1, $content);
$content = preg_replace($pattern2, $replacement2, $content);
file_put_contents($filepath, $content);
echo "Patched audio calls.\n";
