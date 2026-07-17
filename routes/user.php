<?php

	use App\Http\Controllers\User\ProfileController;
	use Illuminate\Support\Facades\Route;

	Route::prefix('profile')->name('profile.')->group(function () {
		// Core Profile Routes (Moved from web.php)
		Route::get('/', [ProfileController::class, 'show'])->name('show');
		Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
		Route::post('/update', [ProfileController::class, 'update'])->name('update');

		// Profile Settings Sub-Routes
		Route::prefix('settings')->name('settings.')->group(function () {
			Route::get('/', [ProfileController::class, 'settings'])->name('index');
			Route::get('{setting}/edit', [ProfileController::class, 'editSetting'])->name('edit');
			Route::put('{setting}', [ProfileController::class, 'updateSetting'])->name('update');
		});
	});