<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest Authentication Routes
|--------------------------------------------------------------------------
|
| Routes accessible to unauthenticated users for registration, login,
| and password reset functionality. These routes are protected by the
| guest middleware to prevent authenticated users from accessing them.
|
*/

Route::middleware('guest')->group(function () {
    /*
    |----------------------------------------------------------------------
    | User Registration
    |----------------------------------------------------------------------
    |
    | Routes for new user registration including displaying the registration
    | form and processing registration requests.
    |
    */
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    /*
    |----------------------------------------------------------------------
    | User Authentication
    |----------------------------------------------------------------------
    |
    | Routes for user login including displaying the login form and
    | processing authentication requests.
    |
    */
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    /*
    |----------------------------------------------------------------------
    | Password Reset
    |----------------------------------------------------------------------
    |
    | Routes for password reset functionality including requesting password
    | reset links, displaying reset forms, and processing password updates.
    |
    */
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
|
| Routes that require user authentication for email verification, password
| confirmation, password updates, and logout functionality. These routes
| are protected by the auth middleware.
|
*/

Route::middleware('auth')->group(function () {
    /*
    |----------------------------------------------------------------------
    | Email Verification
    |----------------------------------------------------------------------
    |
    | Routes for email verification including displaying verification notices,
    | processing verification links, and resending verification emails.
    |
    */
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    /*
    |----------------------------------------------------------------------
    | Password Management
    |----------------------------------------------------------------------
    |
    | Routes for password confirmation and updates for authenticated users.
    | Includes secure password confirmation for sensitive operations.
    |
    */
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])
        ->name('password.update');

    /*
    |----------------------------------------------------------------------
    | Session Management
    |----------------------------------------------------------------------
    |
    | Routes for user logout and session termination.
    |
    */
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
