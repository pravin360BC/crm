<?php

use App\Http\Controllers\SessionController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Middleware\RequireWorkspacePermission;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/workspace');

Route::middleware('guest')->group(function () {
    Route::view('/login', 'login')->name('login');
    Route::post('/login', [SessionController::class, 'store'])->middleware('throttle:crm-login');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
    Route::get('/workspaces', [WorkspaceController::class, 'index'])->name('workspaces');
    Route::post('/workspaces/switch', [WorkspaceController::class, 'switch'])->name('workspaces.switch');
    Route::get('/workspace', [WorkspaceController::class, 'show'])
        ->middleware(RequireWorkspacePermission::class.':workspace.view')->name('workspace');
    Route::get('/workspace/settings', [WorkspaceController::class, 'settings'])
        ->middleware(RequireWorkspacePermission::class.':workspace.view', RequireWorkspacePermission::class.':workspace.manage')
        ->name('workspace.settings');
});
