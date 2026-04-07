<?php

use App\Http\Controllers\CallLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Public login page
Route::get('/', function () {
    return view('auth.login');
})->middleware('guest');

// Dashboard with role-based redirect
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/dashboard/call-activity', [DashboardController::class, 'getCallActivityData'])
    ->middleware(['auth'])
    ->name('dashboard.call-activity');

// Protected routes - requires authentication
Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // AJAX Search for clients - must be defined BEFORE resource route
    Route::get('/clients/search', [\App\Http\Controllers\ClientController::class, 'search'])->name('clients.search');

    // Client Management - available to ALL roles (Admin, Manager, Agent)
    Route::resource('clients', \App\Http\Controllers\ClientController::class);

    // Task Management
    Route::resource('tasks', \App\Http\Controllers\TaskController::class);

    // Call Logs - nested under clients
    Route::get('/clients/{client}/call-logs', [CallLogController::class, 'clientIndex'])->name('clients.call-logs.index');
    Route::get('/clients/{client}/call-logs/create', [CallLogController::class, 'create'])->name('clients.call-logs.create');
    Route::post('/clients/{client}/call-logs', [CallLogController::class, 'store'])->name('clients.call-logs.store');

    // Admin and Manager can access user management and reports
    Route::middleware('role:Admin|Manager')->group(function () {
        // User Management
        Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [\App\Http\Controllers\UserController::class, 'create'])->name('users.create');
        Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [\App\Http\Controllers\UserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [\App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
        Route::patch('/users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');

        // Reports (Controller not implemented yet)
        // Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');

        // Call Logs - All logs view and management
        Route::get('/call-logs', [CallLogController::class, 'index'])->name('call-logs.index');
        Route::get('/call-logs/{callLog}', [CallLogController::class, 'show'])->name('call-logs.show');
        Route::get('/call-logs/{callLog}/edit', [CallLogController::class, 'edit'])->name('call-logs.edit');
        Route::patch('/call-logs/{callLog}', [CallLogController::class, 'update'])->name('call-logs.update');
        Route::delete('/call-logs/{callLog}', [CallLogController::class, 'destroy'])->name('call-logs.destroy');
    });
});

require __DIR__ . '/auth.php';
