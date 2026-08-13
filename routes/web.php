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

Route::get('/dashboard/project-progress', [DashboardController::class, 'getProjectProgressData'])
    ->middleware(['auth'])
    ->name('dashboard.project-progress');

// Protected routes - requires authentication
Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // AJAX Search for clients - must be defined BEFORE resource route
    Route::get('/clients/search', [\App\Http\Controllers\ClientController::class, 'search'])->name('clients.search');

    // Restore soft-deleted clients
    Route::post('/clients/{id}/restore', [\App\Http\Controllers\ClientController::class, 'restore'])->name('clients.restore');
    Route::delete('/clients/{id}/force-delete', [\App\Http\Controllers\ClientController::class, 'forceDelete'])->name('clients.force-delete');

    // Client Management - available to ALL roles (Admin, Manager, Agent)
    Route::resource('clients', \App\Http\Controllers\ClientController::class);

    // Task Management
    Route::resource('tasks', \App\Http\Controllers\TaskController::class);

    // Simple Project Management
    Route::get('/crm-projects', [\App\Http\Controllers\CrmProjectController::class, 'index'])->name('crm-projects.index');
    Route::get('/crm-projects/{project}/show', [\App\Http\Controllers\CrmProjectController::class, 'show'])->name('crm-projects.show');
    Route::get('/crm-projects/{project}/docs', [\App\Http\Controllers\CrmProjectController::class, 'docsIndex'])->name('crm-projects.docs');
    Route::get('/crm-projects/{project}/docs/create', [\App\Http\Controllers\CrmProjectController::class, 'docsCreate'])->name('crm-projects.docs.create');
    Route::post('/crm-projects/{project}/docs', [\App\Http\Controllers\CrmProjectController::class, 'docsStore'])->name('crm-projects.docs.store');
    Route::get('/crm-projects/{project}/docs/{document}', [\App\Http\Controllers\CrmProjectController::class, 'docsShow'])->name('crm-projects.docs.show');
    Route::get('/crm-projects/{project}/edit', [\App\Http\Controllers\CrmProjectController::class, 'edit'])->name('crm-projects.edit');
    Route::post('/crm-projects/{project}/activities', [\App\Http\Controllers\CrmProjectController::class, 'storeActivity'])->name('crm-projects.activities.store');
    Route::post('/crm-projects/{project}/enhancements', [\App\Http\Controllers\CrmProjectController::class, 'storeEnhancement'])->name('crm-projects.enhancements.store');
    Route::post('/crm-projects/{project}/messages', [\App\Http\Controllers\CrmProjectController::class, 'sendMessage'])->name('crm-projects.messages.store');
    Route::post('/crm-projects/{project}/details', [\App\Http\Controllers\CrmProjectController::class, 'storeOrUpdate'])->name('crm-projects.details.store');

    // General Chat Routes
    Route::get('/chat', [\App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/rooms', [\App\Http\Controllers\ChatController::class, 'storeRoom'])->name('chat.rooms.store');
    Route::post('/chat/rooms/{room}/messages', [\App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.rooms.messages.store');
    Route::get('/chat/messages/{message}/attachment/{filename}', [\App\Http\Controllers\ChatController::class, 'attachment'])->name('chat.messages.attachment');
    Route::get('/chat/messages/{message}/download', [\App\Http\Controllers\ChatController::class, 'downloadAttachment'])->name('chat.messages.download');
    Route::get('/chat/rooms/{room}/members', [\App\Http\Controllers\ChatController::class, 'roomMembers'])->name('chat.rooms.members');
    Route::post('/chat/rooms/{room}/members', [\App\Http\Controllers\ChatController::class, 'addMember'])->name('chat.rooms.members.add');
    Route::delete('/chat/rooms/{room}/members/{user}', [\App\Http\Controllers\ChatController::class, 'removeMember'])->name('chat.rooms.members.remove');
    Route::get('/crm-projects/{project}/daily-updates', [\App\Http\Controllers\CrmProjectController::class, 'dailyUpdatesIndex'])->name('crm-projects.daily-updates');
    Route::post('/crm-projects/{project}/daily-updates', [\App\Http\Controllers\CrmProjectController::class, 'storeDailyUpdate'])->name('crm-projects.daily-updates.store');
    Route::patch('/crm-projects/{project}/daily-updates/{update}', [\App\Http\Controllers\CrmProjectController::class, 'updateDailyUpdate'])->name('crm-projects.daily-updates.update');
    Route::get('/api/attachments/{attachment}', [\App\Http\Controllers\CrmProjectController::class, 'attachmentShow'])->name('api.attachments.show');
    Route::get('/api/daily-updates/{update}/attachment', [\App\Http\Controllers\CrmProjectController::class, 'legacyDailyUpdateAttachmentShow'])->name('api.daily-updates.attachment.show');
    Route::get('/api/activities/{activity}/attachment', [\App\Http\Controllers\CrmProjectController::class, 'activityAttachmentShow'])->name('api.activities.attachment.show');
    Route::get('/api/enhancements/{enhancement}/attachment', [\App\Http\Controllers\CrmProjectController::class, 'enhancementAttachmentShow'])->name('api.enhancements.attachment.show');

    // Project Management - Admin, Manager, and Project Manager
    Route::middleware('role:Admin|Manager|project-manager')->group(function () {
        Route::get('/projects/settings', [\App\Http\Controllers\ProjectController::class, 'settings'])->name('projects.settings');
        Route::post('/projects/settings', [\App\Http\Controllers\ProjectController::class, 'saveSettings'])->name('projects.settings.save');
        Route::get('/projects/invoices', [\App\Http\Controllers\ProjectController::class, 'invoices'])->name('projects.invoices');
        Route::get('/projects/invoices/{id}/download', [\App\Http\Controllers\ProjectController::class, 'downloadInvoice'])->name('projects.invoices.download');
        Route::post('/projects/invoices/generate', [\App\Http\Controllers\ProjectController::class, 'generateInvoice'])->name('projects.invoices.generate');
        Route::post('/projects/payments', [\App\Http\Controllers\ProjectController::class, 'storePayment'])->name('projects.payments.store');
        Route::get('/projects/{project}/ledger', [\App\Http\Controllers\ProjectController::class, 'downloadLedger'])->name('projects.ledger');
        Route::post('/projects/{project}/generate-invoice', [\App\Http\Controllers\ProjectController::class, 'generatePaymentInvoice'])->name('projects.generate-invoice');
        Route::resource('projects', \App\Http\Controllers\ProjectController::class);
    });

    // Call Logs - nested under clients
    Route::get('/clients/{client}/call-logs', [CallLogController::class, 'clientIndex'])->name('clients.call-logs.index');
    Route::get('/clients/{client}/call-logs/create', [CallLogController::class, 'create'])->name('clients.call-logs.create');
    Route::post('/clients/{client}/call-logs', [CallLogController::class, 'store'])->name('clients.call-logs.store');

    // User Management - Admin, Manager, and Project Manager
    Route::middleware('role:Admin|Manager|project-manager')->group(function () {
        Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [\App\Http\Controllers\UserController::class, 'create'])->name('users.create');
        Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [\App\Http\Controllers\UserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [\App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
        Route::patch('/users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');
    });

    // Reports (Controller not implemented yet)
    // Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');

    // Call Logs - All logs view and management - accessible to all authenticated roles
    Route::get('/call-logs', [CallLogController::class, 'index'])->name('call-logs.index');
    Route::get('/call-logs/{callLog}', [CallLogController::class, 'show'])->name('call-logs.show');
    Route::get('/call-logs/{callLog}/edit', [CallLogController::class, 'edit'])->name('call-logs.edit');
    Route::patch('/call-logs/{callLog}', [CallLogController::class, 'update'])->name('call-logs.update');
    Route::delete('/call-logs/{callLog}', [CallLogController::class, 'destroy'])->name('call-logs.destroy');
});

require __DIR__ . '/auth.php';
