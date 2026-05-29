<?php

	use App\Http\Controllers\User\ProfileController;
	use App\Http\Controllers\User\UserController;
	use Illuminate\Support\Facades\Route;

	Route::get('lang/{locale}', function (string $locale) {
		if (! in_array($locale, ['en', 'el', 'fr'])) {
			abort(400);
		}

		session()->put('locale', $locale);

		return redirect()->back();
	})->name('lang.switch');

	require __DIR__ . '/auth.php';
	require __DIR__ . '/user.php';

	Route::middleware(['web', 'auth'])->name('inventory.')->group(function() {
		// Εδώ μέσα μπαίνουν όλα όσα θέλουμε να έχουν το inventory. prefix
		require base_path('routes/management.php');
		require base_path('routes/reports.php');
	});

	// Protected routes (authenticated + verified users)
	Route::middleware(['auth', 'verified'])->group(function () {
		// Dashboard or main page after login
		Route::get('/', [
			UserController::class,
			'index'
		])->name('dashboard');

		// Profile-related routes
		Route::prefix('profile')->name('profile.')->group(function () {
			// View profile
			Route::get('/', [
				ProfileController::class,
				'show'
			])->name('show');

			// Edit profile form
			Route::get('/edit', [
				ProfileController::class,
				'edit'
			])->name('edit');

			// Update profile info
			Route::post('/update', [
				ProfileController::class,
				'update'
			])->name('update');

			// Change password
			Route::post('/password', [
				ProfileController::class,
				'updatePassword'
			])->name('password.update');

			// Delete account
			Route::delete('/delete', [
				ProfileController::class,
				'destroy'
			])->name('delete');
		});
	});