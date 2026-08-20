<?php
$filepath = 'resources/views/layouts/app.blade.php';
$content = file_get_contents($filepath);

$pattern = '/const audio = new Audio\("{{ asset\(\'notification\.wav\'\) }}"\);.*?const currentUserId = {{ auth\(\)->id\(\) }};/s';

$replacement = '
                    const playNotificationSound = () => {
                        let a = new Audio("{{ asset(\'notification.wav\') }}");
                        a.play().catch(err => {
                            console.log("Audio play failed, trying fallback beep...", err);
                            try {
                                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                                const osc = ctx.createOscillator();
                                const gain = ctx.createGain();
                                osc.connect(gain);
                                gain.connect(ctx.destination);
                                osc.frequency.value = 880;
                                gain.gain.value = 0.1;
                                osc.start(ctx.currentTime);
                                osc.stop(ctx.currentTime + 0.15);
                            } catch (e) {
                                console.log("Fallback beep failed", e);
                            }
                        });
                    };

                    const currentUserId = {{ auth()->id() }};';

$content = preg_replace($pattern, $replacement, $content);
file_put_contents($filepath, $content);
echo "Patched audio initialization.\n";
