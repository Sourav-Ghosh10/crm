<?php
$filepath = "resources/views/chat/index.blade.php";
$content = file_get_contents($filepath);

$oldClass = "bg-[#1d2025]': !dragOver";
$newClass = "bg-gray-100 dark:bg-[#1d2025]': !dragOver";
$content = str_replace($oldClass, $newClass, $content);

file_put_contents($filepath, $content);
echo "Fixed input background.\n";
