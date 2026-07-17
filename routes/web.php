<?php

	use App\Http\Controllers\User\UserController;
	use Illuminate\Support\Facades\Route;

	// Language Switcher
	Route::get('lang/{locale}', function (string $locale) {
		if (!in_array($locale, ['en', 'el', 'fr'])) {
			abort(400);
		}
		session()->put('locale', $locale);
		return redirect()->back();
	})->name('lang.switch');

	// Include Authentication Routes
	require __DIR__.'/auth.php';

	// Include User Profile Routes (Now handles all profile logic self-contained)
	require __DIR__.'/user.php';

	// Global Dashboard
	Route::middleware(['auth', 'verified'])->group(function () {
		Route::get('/', [UserController::class, 'index'])->name('dashboard');
	});

	// ERP Modules: Inventory Namespace Block
	Route::middleware(['web', 'auth', 'verified'])->name('inventory.')->group(function () {
		require base_path('routes/management.php');
		require base_path('routes/reports.php');
	});

	// Future ERP Modules can cleanly plug right in here:
	Route::middleware(['web', 'auth'])->name('sales.')->prefix('sales')->group(function () {
		require base_path('routes/sales.php');
	});