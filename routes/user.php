<?php

	use App\Http\Controllers\User\ProfileController;
	use Illuminate\Support\Facades\Route;

	Route::prefix('profile')->name('profile.')->group(function () {

		// Profile settings routes
		Route::prefix('settings')->name('settings.')->group(function () {

			// Show all profile settings
			Route::get('/', [ProfileController::class, 'settings'])->name('index');

			// Edit a specific setting
			Route::get('{setting}/edit', [ProfileController::class, 'editSetting'])->name('edit');

			// Update a specific setting
			Route::put('{setting}', [ProfileController::class, 'updateSetting'])->name('update');
		});
	})->middleware(['auth']);