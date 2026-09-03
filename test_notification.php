<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::find(33);
echo json_encode(['notifications' => $user->notifications()->take(5)->get()], JSON_PRETTY_PRINT);
