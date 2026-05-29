<?php

	use App\Http\Controllers\ReportController;
	use App\Http\Controllers\Reports\CustomerController;
	use App\Http\Controllers\Reports\InventoryController;
	use App\Http\Controllers\Reports\PurchaseController;
	use App\Http\Controllers\Reports\SalesController;
	use Illuminate\Support\Facades\Route;

	// Το prefix 'reports' μπαίνει εδώ
	// Το name 'reports.' μπαίνει εδώ για να έχουμε inventory.reports.index κλπ.
	Route::prefix('reports')->name('reports.')->group(function () {

		// inventory.reports.index
		Route::get('/', [ReportController::class, 'index'])->name('index');

		// inventory.reports.sales
		Route::get('/sales', [SalesController::class, 'index'])->name('sales');

		// inventory.reports.inventory
		Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');

		// inventory.reports.purchases
		Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases');

		// inventory.reports.customers
		Route::get('/customers', [CustomerController::class, 'index'])->name('customers');

		// inventory.reports.generate
		Route::post('/generate', [ReportController::class, 'generate'])->name('generate');

		// inventory.reports.warehouse
		Route::get('/warehouse/{warehouse}', [ReportController::class, 'warehouse'])->name('warehouse');

	});