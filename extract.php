<?php
$file = fopen('C:\Users\nrnst\.gemini\antigravity-ide\brain\065c1574-38c3-4de9-8c42-3df16d8dd232\.system_generated\logs\transcript_full.jsonl', 'r');
while (($line = fgets($file)) !== false) {
    if (strpos($line, 'Restore chat script functionality') !== false) {
        $data = json_decode($line, true);
        if (isset($data['tool_calls'])) {
            foreach ($data['tool_calls'] as $call) {
                if ($call['name'] === 'replace_file_content') {
                    file_put_contents('recovered_script.txt', $call['args']['ReplacementContent']);
                    echo "Recovered script to recovered_script.txt\n";
                    exit;
                }
            }
        }
    }
}
fclose($file);
