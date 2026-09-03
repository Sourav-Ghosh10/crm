<?php
$files = [
    "resources/views/chat/index.blade.php",
    "resources/views/layouts/app.blade.php"
];

$replacements = [
    // Main backgrounds
    "bg-[#17181c]" => "bg-white dark:bg-[#17181c]",
    "bg-[#1f2329]" => "bg-gray-50 dark:bg-[#1f2329]",
    "bg-[#181a20]" => "bg-gray-100 dark:bg-[#181a20]",
    "bg-[#202329]" => "bg-gray-50 dark:bg-[#202329]",
    "bg-[#2a2f37]" => "bg-gray-200 dark:bg-[#2a2f37]",
    
    // Borders
    "border-[#2f343d]" => "border-gray-200 dark:border-[#2f343d]",
    "border-[#3f4550]" => "border-gray-300 dark:border-[#3f4550]",
    "border-slate-700/50" => "border-gray-200 dark:border-slate-700/50",
    "border-slate-700" => "border-gray-200 dark:border-slate-700",
    
    // Text colors
    "text-slate-200" => "text-slate-900 dark:text-slate-200",
    "text-slate-300" => "text-slate-800 dark:text-slate-300",
    "text-slate-400" => "text-slate-500 dark:text-slate-400",
    
    // Hovers
    "hover:bg-[#2a2f37]" => "hover:bg-gray-200 dark:hover:bg-[#2a2f37]",
    "hover:bg-[#3f4550]" => "hover:bg-gray-300 dark:hover:bg-[#3f4550]",
    "hover:text-slate-200" => "hover:text-slate-900 dark:hover:text-slate-200",
    "hover:text-white" => "hover:text-slate-900 dark:hover:text-white",
    
    // Selection/Active states
    "bg-[#3f4550] text-white font-semibold" => "bg-indigo-50 dark:bg-[#3f4550] text-indigo-700 dark:text-white font-semibold",
];

foreach ($files as $filepath) {
    if (!file_exists($filepath)) {
        continue;
    }
        
    $content = file_get_contents($filepath);
        
    foreach ($replacements as $old => $new) {
        $content = str_replace($old, $new, $content);
    }
        
    file_put_contents($filepath, $content);
}

echo "Replacement complete.\n";
