<?php
// PHP script to generate a nice beep sound
$sampleRate = 44100;
$duration = 0.2;
$freq = 880;
$numSamples = (int)($sampleRate * $duration);
$data = '';

for ($i = 0; $i < $numSamples; $i++) {
    $t = $i / $sampleRate;
    $env = exp(-15 * $t);
    $val = (int)(32767 * sin(2 * M_PI * $freq * $t) * $env);
    $data .= pack('v', $val);
}

$header = 'RIFF' . pack('V', 36 + strlen($data)) . 'WAVEfmt ' . pack('V', 16) . pack('v', 1) . pack('v', 1) . pack('V', $sampleRate) . pack('V', $sampleRate * 2) . pack('v', 2) . pack('v', 16) . 'data' . pack('V', strlen($data));

file_put_contents(__DIR__ . '/public/notification.wav', $header . $data);
echo "Created notification.wav\n";
