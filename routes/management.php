<?php

	use App\Http\Controllers\BrandController;
	use App\Http\Controllers\FilterController;
	use App\Http\Controllers\Inventory\CategoryController;
	use App\Http\Controllers\Inventory\CustomerController;
	use App\Http\Controllers\Inventory\ProductController;
	use App\Http\Controllers\Inventory\ProductImageController;
	use App\Http\Controllers\Inventory\PurchaseController;
	use App\Http\Controllers\Inventory\SupplierController;
	use App\Http\Controllers\Inventory\WarehouseController;
	use App\Http\Controllers\InvoiceController;
	use App\Http\Controllers\Reports\InventoryController;
	use App\Http\Controllers\Stock\StockAdjustmentController;
	use App\Http\Controllers\Stock\StockReturnController;
	use App\Http\Controllers\Stock\StockTransferController;
	use Illuminate\Support\Facades\Route;

	/*
	|--------------------------------------------------------------------------
	| Inventory Catalog Management Routes
	|--------------------------------------------------------------------------
	*/

	// Products & Categories Resource Operations
	Route::resource('products', ProductController::class);

	// Search products for adjustments or other dropdown autocompletes
	Route::post('/products/search', [ProductController::class, 'search'])->name('products.search');

	Route::prefix('products')->group(function () {
		Route::post('/{product}/upload-images', [
			ProductImageController::class, 'store'
		])->name('products.image.upload');
		Route::delete('/image/{image}', [ProductImageController::class, 'destroy'])->name('products.image.destroy');

		Route::resource('categories', CategoryController::class);

		Route::post('/{product}/clone', [ProductController::class, 'clone'])->name('product.clone');
		Route::post('/{product}/history', [ProductController::class, 'history'])->name('product.history');
		Route::post('/{product}/information', [
			ProductController::class, 'getInformation'
		])->name('product.information');
	});

	// Suppliers Resource Operations
	Route::resource('suppliers', SupplierController::class);

	// Single-Action Structural View Resources
	Route::resource('customers', CustomerController::class)->only(['index']);
	Route::resource('invoices', InvoiceController::class)->only(['index']);
	Route::resource('purchases', PurchaseController::class);

	/*
	|--------------------------------------------------------------------------
	| Warehouses Layout & Location Architecture Module
	|--------------------------------------------------------------------------
	*/

	// Core RESTful Resource for Warehouse Crud Operations
	Route::resource('warehouses', WarehouseController::class);

	// Advanced Warehouse Structural AJAX Filters and Status Actions
	Route::prefix('warehouses')->name('warehouses.')->controller(WarehouseController::class)->group(function () {
		// Custom Warehouse Node Operational Upstream Toggles
		Route::patch('/{warehouse}/toggle-status', 'toggleStatus')->name('toggle-status');
		Route::get('/{warehouse}/activity', 'activity')->name('activity');
		Route::post('/{warehouse}/filter', 'filter')->name('filter');

		// Cascading Spatial Dropdowns (Organized safely down to Bin level)
		Route::post('/locations', 'getLocations')->name('base');
		Route::post('/location/details', 'getLocationDetails')->name('base');

		Route::post('/zones', 'getZones')->name('zones');
		Route::post('/aisles', 'getAisles')->name('aisles');
		Route::post('/racks', 'getRacks')->name('racks');
		Route::post('/shelves', 'getShelves')->name('shelves');
		Route::post('/bins', 'getBins')->name('bins');
	});

	/*
	|--------------------------------------------------------------------------
	| .....................
	|--------------------------------------------------------------------------
	*/
	Route::prefix('categories')->name('categories.')->controller(CategoryController::class)->group(function () {
		Route::post('/{category}/brands', 'filterBrands')->name('filter.brands');
	});

	/*
	|--------------------------------------------------------------------------
	| .....................
	|--------------------------------------------------------------------------
	*/
	Route::prefix('brands')->name('brands.')->controller(BrandController::class)->group(function () {
		Route::post('/{brand}/products', 'getProducts')->name('products');
	});

	/*
	|--------------------------------------------------------------------------
	| Stock Movements, Adjustments & Ledger Ledger Control
	|--------------------------------------------------------------------------
	*/

	Route::prefix('inventories')->group(function () {
		Route::resource('inventory', InventoryController::class);

		// Core Inline Event Logging Handlers
		Route::post('/{inventory}/transfer', [StockTransferController::class, 'store'])->name('transfer');
		Route::post('/{inventory}/adjust', [StockAdjustmentController::class, 'store'])->name('adjust');

		// Stock Validation & Setup (FIXED: Route names are unique now)
		Route::post('/adjustment/reasons', [StockAdjustmentController::class, 'reasons'])->name('reasons');
		Route::post('/adjustment/validation', [StockAdjustmentController::class, 'check'])->name('validation');
	});

	// Full historical documentation resource workflows over time
	Route::resource('transfers', StockTransferController::class)->except(['store']);

	// Έγκριση Παραστατικού Προσαρμογής
	Route::patch('adjustments/{adjustment}/approve', [
		StockAdjustmentController::class, 'approve'
	])->name('adjustments.approve');

	Route::resource('adjustments', StockAdjustmentController::class)->except(['store']);

	Route::resource('returns', StockReturnController::class);

	/*
	|--------------------------------------------------------------------------
	| Global Framework Utilities
	|--------------------------------------------------------------------------
	*/
	Route::get('/filters', [FilterController::class, 'getFilters'])->name('global');
