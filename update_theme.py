
import os
import re

files = [
    "resources/views/chat/index.blade.php",
    "resources/views/layouts/app.blade.php"
]

replacements = {
    # Main backgrounds
    "bg-[#17181c]": "bg-white dark:bg-[#17181c]",
    "bg-[#1f2329]": "bg-gray-50 dark:bg-[#1f2329]",
    "bg-[#181a20]": "bg-gray-100 dark:bg-[#181a20]",
    "bg-[#202329]": "bg-gray-50 dark:bg-[#202329]",
    "bg-[#2a2f37]": "bg-gray-200 dark:bg-[#2a2f37]",
    
    # Borders
    "border-[#2f343d]": "border-gray-200 dark:border-[#2f343d]",
    "border-[#3f4550]": "border-gray-300 dark:border-[#3f4550]",
    "border-slate-700/50": "border-gray-200 dark:border-slate-700/50",
    "border-slate-700": "border-gray-200 dark:border-slate-700",
    
    # Text colors
    "text-slate-200": "text-slate-900 dark:text-slate-200",
    "text-slate-300": "text-slate-800 dark:text-slate-300",
    "text-slate-400": "text-slate-500 dark:text-slate-400",
    "text-white": "text-slate-900 dark:text-white", # Be careful, might break explicitly white things
    
    # Hovers
    "hover:bg-[#2a2f37]": "hover:bg-gray-200 dark:hover:bg-[#2a2f37]",
    "hover:bg-[#3f4550]": "hover:bg-gray-300 dark:hover:bg-[#3f4550]",
    "hover:text-slate-200": "hover:text-slate-900 dark:hover:text-slate-200",
    "hover:text-white": "hover:text-slate-900 dark:hover:text-white",
    
    # Selection/Active states
    "bg-[#3f4550] text-white": "bg-indigo-50 dark:bg-[#3f4550] text-indigo-700 dark:text-white",
}

for filepath in files:
    if not os.path.exists(filepath):
        continue
        
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()
        
    for old, new in replacements.items():
        # Avoid double replacing if it already contains the new string
        # A simple string replace for now. We can handle text-white carefully.
        if old == "text-white":
            # We only replace text-white in specific chat/index.blade.php if we are sure it is text color
            # Actually replacing text-white globally is dangerous (badges, avatars use it)
            continue
            
        content = content.replace(old, new)
        
    with open(filepath, "w", encoding="utf-8") as f:
        f.write(content)

print("Replacement complete.")

