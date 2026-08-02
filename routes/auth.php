<?php

	use App\Http\Controllers\Auth\AuthController;
	use App\Http\Controllers\Auth\PasswordController;
	use Illuminate\Support\Facades\Route;

	Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('auth.sign.up');
	Route::post('/register', [AuthController::class, 'register'])->name('register');
	Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
	Route::post('/login', [AuthController::class, 'login'])->name('auth.sign.in');

	Route::get('/forgot-password', [PasswordController::class, 'requestPasswordReset'])->name('password.request');
	Route::post('/forgot-password', [PasswordController::class, 'emailPasswordReset'])->name('password.email');
	Route::get('/reset-password/{token}', [PasswordController::class, 'resetPassword'])->name('password.reset');
	Route::post('/reset-password', [PasswordController::class, 'updatePassword'])->name('password.update');

	Route::post('/logout', [AuthController::class, 'logout'])->name('auth.sign.out');