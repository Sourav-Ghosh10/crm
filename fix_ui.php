<?php
$filepath = "resources/views/chat/index.blade.php";
$content = file_get_contents($filepath);

// 1. Search buttons
$oldSearchBtnStyle = 'style="background-color: #334155 !important; border-color: transparent !important; color: #94a3b8 !important;"';
$newSearchBtnStyle = 'class="w-full flex items-center justify-between px-4 py-1.5 rounded-xl text-xs bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors text-left"';
// But it already has a class attribute right after it!
// Let's replace the whole block to be safe.
$oldSearchBtn1 = 'style="background-color: #334155 !important; border-color: transparent !important; color: #94a3b8 !important;"
                                class="w-full flex items-center justify-between px-4 py-1.5 rounded-xl text-xs hover:text-slate-900 dark:hover:text-white transition-colors text-left"';
$newSearchBtn1 = 'class="w-full flex items-center justify-between px-4 py-1.5 rounded-xl text-xs bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors text-left"';
$content = str_replace($oldSearchBtn1, $newSearchBtn1, $content);

$oldSearchBtn2 = 'style="background-color: #334155 !important; border-color: transparent !important; color: #94a3b8 !important;"
                        class="w-full flex items-center justify-between px-4 py-1.5 rounded-xl text-xs hover:text-slate-900 dark:hover:text-white transition-colors text-left"';
$newSearchBtn2 = 'class="w-full flex items-center justify-between px-4 py-1.5 rounded-xl text-xs bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors text-left"';
$content = str_replace($oldSearchBtn2, $newSearchBtn2, $content);

// 2. Search input in modal
$oldSearchInput = 'style="background-color: #334155 !important; border-color: transparent !important; color: #d1d5db !important;"
                        class="w-full pl-4 pr-10 py-1.5 text-xs rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none placeholder-gray-400 transition-colors"';
$newSearchInput = 'class="w-full pl-4 pr-10 py-1.5 text-xs rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none placeholder-gray-500 dark:placeholder-gray-400 transition-colors"';
$content = str_replace($oldSearchInput, $newSearchInput, $content);

// 3. Textarea input in chat
$oldTextarea = 'style="background-color: transparent !important; color: #e2e8f0 !important; border: 0 !important; box-shadow: none !important; resize: none; max-height: 150px; min-height: 44px; outline: none !important;"';
$newTextarea = 'style="resize: none; max-height: 150px; min-height: 44px;"';
$content = str_replace($oldTextarea, $newTextarea, $content);

file_put_contents($filepath, $content);
echo "Fixed UI components.\n";
