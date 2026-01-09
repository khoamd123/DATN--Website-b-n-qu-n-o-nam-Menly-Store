<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| Routes for user authentication (login, register, logout)
|
*/

// Login and Register - no middleware (public routes)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Logout - requires authentication
Route::middleware(\App\Http\Middleware\SimpleAuth::class)->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Quick login for testing (remove in production) - check if method exists
if (method_exists(AuthController::class, 'quickLoginStudent')) {
    Route::get('/quick-login-student', [AuthController::class, 'quickLoginStudent'])->name('quick.login.student');
}

