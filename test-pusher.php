<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
$project = \App\Models\Project::first();
$user->notify(new \App\Notifications\ProjectAssignmentNotification($project, $user));
echo "Notification dispatched!\n";
