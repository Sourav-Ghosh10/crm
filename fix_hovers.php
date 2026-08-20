<?php
$files = [
    "resources/views/chat/index.blade.php",
    "resources/views/layouts/app.blade.php"
];

$replacements = [
    "hover:bg-gray-200 dark:bg-[#2a2f37]" => "hover:bg-gray-200 dark:hover:bg-[#2a2f37]",
    "hover:bg-gray-300 dark:bg-[#3f4550]" => "hover:bg-gray-300 dark:hover:bg-[#3f4550]",
];

foreach ($files as $filepath) {
    if (!file_exists($filepath)) {
        continue;
    }
        
    $content = file_get_contents($filepath);
        
    foreach ($replacements as $old => $new) {
        $content = str_replace($old, $new, $content);
    }
        
    // Wait, let's also check if the incoming message bubble was broken:
    // the message bubble should be bg-gray-200 dark:bg-[#2a2f37], and it IS because it's not a hover!
    // order-2 bg-gray-200 dark:bg-[#2a2f37] text-slate-800 dark:text-slate-100
    // But my script would change `order-2 bg-gray-200 dark:hover:bg-[#2a2f37]`? No, it's not a hover.
    
    file_put_contents($filepath, $content);
}

echo "Fixed hover classes.\n";
