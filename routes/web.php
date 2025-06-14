<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Projects\ProjectController;
use App\Http\Controllers\Workspaces\WorkspaceController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard/Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('workspaces', WorkspaceController::class)->only(['store', 'update', 'destroy']);
    Route::get('/workspaces/{workspace}/switch', [WorkspaceController::class, 'switch'])->name('workspaces.switch');
    Route::resource('projects', ProjectController::class)->only(['store', 'show', 'update', 'destroy']);
    Route::resource('projects.environments', \App\Http\Controllers\Projects\EnvironmentController::class)->only(['store', 'update', 'destroy']);
    Route::resource('workspaces.memberships', \App\Http\Controllers\Workspaces\WorkspaceMembershipController::class)->except(['create', 'show', 'edit']);
    Route::resource('workspaces.cloud-providers', \App\Http\Controllers\CloudProviders\CloudProviderController::class)->only(['store', 'update', 'destroy']);
});

require __DIR__.'/auth.php';
