<?php

use App\Http\Controllers\CloudProviders\CloudProviderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Projects\EnvironmentController;
use App\Http\Controllers\Projects\ProjectController;
use App\Http\Controllers\SourceCode\SourceCodeConnectionController;
use App\Http\Controllers\SourceCode\SourceCodeWebhookController;
use App\Http\Controllers\Workspaces\WorkspaceController;
use App\Http\Controllers\Workspaces\WorkspaceMembershipController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
|
| Routes that are accessible to all visitors without authentication.
| Includes the welcome page and public information endpoints.
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Main application dashboard accessible to authenticated and verified users.
| Serves as the primary entry point after successful authentication.
|
*/

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
|
| Routes that require user authentication. Includes user profile management,
| workspace operations, project management, and cloud provider integrations.
|
*/

Route::middleware('auth')->group(function () {
    /*
    |----------------------------------------------------------------------
    | User Profile Management
    |----------------------------------------------------------------------
    |
    | Routes for managing user profile information including editing,
    | updating, and deleting user accounts.
    |
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |----------------------------------------------------------------------
    | Workspace Management
    |----------------------------------------------------------------------
    |
    | Routes for creating, updating, deleting, and switching between
    | workspaces. Includes workspace membership management.
    |
    */
    Route::resource('workspaces', WorkspaceController::class)->only(['store', 'update', 'destroy']);
    Route::get('/workspaces/{workspace}/switch', [WorkspaceController::class, 'switch'])->name('workspaces.switch');
    Route::resource('workspaces.memberships', WorkspaceMembershipController::class)->except(['create', 'show', 'edit']);

    /*
    |----------------------------------------------------------------------
    | Project Management
    |----------------------------------------------------------------------
    |
    | Routes for managing projects within workspaces, including project
    | environments and deployment configurations.
    |
    */
    Route::resource('projects', ProjectController::class)->only(['store', 'show', 'update', 'destroy']);
    Route::resource('projects.environments', EnvironmentController::class)->only(['store', 'update', 'destroy']);

    /*
    |----------------------------------------------------------------------
    | Cloud Provider Integration
    |----------------------------------------------------------------------
    |
    | Routes for managing cloud provider connections and configurations
    | within workspaces for deployment and infrastructure management.
    |
    */
    Route::resource('workspaces.cloud-providers', CloudProviderController::class)->only(['store', 'update', 'destroy']);

    /*
    |----------------------------------------------------------------------
    | Cluster Management
    |----------------------------------------------------------------------
    |
    | Routes for managing clusters within workspaces, including cluster
    | creation, configuration updates, and deletion with node management.
    |
    */
    Route::resource('clusters', \App\Http\Controllers\Clusters\ClusterController::class)->only(['store', 'update', 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Source Code Provider Integration
|--------------------------------------------------------------------------
|
| Routes for connecting and managing source code providers (GitHub, GitLab,
| Bitbucket). Includes OAuth flows, webhook handling, and repository
| synchronization for continuous deployment workflows.
|
*/

Route::prefix('workspaces/connections')->group(function () {
    /*
    |----------------------------------------------------------------------
    | Provider Connection Flow
    |----------------------------------------------------------------------
    |
    | Handles the OAuth/App installation flow for source code providers.
    | Initiates connection and processes callbacks from providers.
    |
    */
    Route::get('/{provider}/connect', [SourceCodeConnectionController::class, 'initiate'])
        ->middleware('auth')
        ->name('source-code.connect')
        ->where('provider', 'github|gitlab|bitbucket');

    Route::get('/{provider}/callback', [SourceCodeConnectionController::class, 'callback'])
        ->name('source-code.callback')
        ->where('provider', 'github|gitlab|bitbucket');

    /*
    |----------------------------------------------------------------------
    | Webhook Endpoints
    |----------------------------------------------------------------------
    |
    | Receives and processes webhooks from source code providers for
    | real-time updates on repository changes and events.
    |
    */
    Route::post('/{provider}/webhooks', [SourceCodeWebhookController::class, 'handle'])
        ->name('source-code.webhooks')
        ->where('provider', 'github|gitlab|bitbucket');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| Include Laravel Breeze authentication routes for login, registration,
| password reset, and email verification functionality.
|
*/

require __DIR__ . '/auth.php';
