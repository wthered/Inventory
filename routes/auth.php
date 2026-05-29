<?php

	use App\Http\Controllers\Auth\AuthController;
	use App\Http\Controllers\Auth\PasswordController;
	use Illuminate\Support\Facades\Route;

	/*
	|--------------------------------------------------------------------------
	| Authentication Routes
	|--------------------------------------------------------------------------
	| These routes handle user login, logout, and any other
	| authentication-related actions.
	*/

	Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('auth.sign.up');
	Route::post('/register', [AuthController::class, 'register'])->name('register');
	Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
	Route::post('/login', [AuthController::class, 'login'])->name('auth.sign.in');
	Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

	Route::middleware('auth')->group(function () {
		// Settings.........

		// Change Password...
		Route::get('/forgot-password', [PasswordController::class, 'requestPasswordReset'])->name('password.request');

		// Handle sending the reset link email
		Route::post('/forgot-password', [PasswordController::class, 'emailPasswordReset'])->name('password.email');

		// Show the reset password form
		Route::get('/reset-password/{token}', [PasswordController::class, 'resetPassword'])->name('password.reset');

		// Handle the new password submission
		Route::post('/reset-password', [PasswordController::class, 'updatePassword'])->name('password.update');

		// and many more....
	});
