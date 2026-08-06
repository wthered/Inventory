<?php

	use App\Http\Controllers\BrandController;
	use App\Http\Controllers\CountryController;
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

	// Products Resource Operations
	Route::resource('products', ProductController::class);

	/*
	|--------------------------------------------------------------------------
	| Categories Protected Resource Operations
	|--------------------------------------------------------------------------
	*/
	// 1. Place fixed explicit paths FIRST
	Route::middleware('permission:category.create')->group(function () {
		Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
		Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
	});

	// 2. Place wildcard evaluation parameters LAST
	Route::middleware('permission:category.view')->group(function () {
		Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
		Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
	});

	Route::middleware('permission:category.update')->group(function () {
		Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
		Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
		Route::patch('categories/{category}', [CategoryController::class, 'update']);
	});

	Route::middleware('permission:category.delete')->group(function () {
		Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
	});

	// Search products for adjustments or other dropdown autocompletes
	Route::post('/products/search', [ProductController::class, 'search'])->name('products.search');

	Route::prefix('products')->group(function () {
		Route::post('/{product}/images/attach', [
			ProductImageController::class, 'store'
		])->name('products.image.upload');

		Route::delete('{product}/images/detach', [
			ProductImageController::class, 'destroy'
		])->name('products.image.destroy');

		Route::post('/{product}/clone', [ProductController::class, 'clone'])->name('product.clone');
		Route::post('/{product}/history', [ProductController::class, 'history'])->name('product.history');
		Route::post('/{product}/information', [
			ProductController::class, 'getInformation'
		])->name('product.information');

		Route::post('/{product}/locations', [ProductController::class, 'getInventory'])->name('product.inventories');
	});

	// Suppliers Resource Operations
	Route::resource('suppliers', SupplierController::class);

	// Brands Resource Operations
	Route::resource('brands', BrandController::class)->withTrashed(['edit', 'update']);

	// Single-Action Structural View Resources
	Route::resource('customers', CustomerController::class);
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

		Route::post('/', 'getWarehouseList')->name('getWarehouseList');

		// Custom Warehouse Node Operational Upstream Toggles
		Route::patch('/{warehouse}/toggle-status', 'toggleStatus')->name('toggle-status');
		Route::get('/{warehouse}/activity', 'activity')->name('activity');
		Route::post('/{warehouse}/filter', 'filter')->name('filter');

		// Cascading Spatial Dropdowns (Organized safely down to Bin level)
		Route::post('/locations', 'getLocations')->name('locations');
		Route::post('/location/details', 'getLocationDetails')->name('locations.details');

		Route::post('/locations/resolve', 'resolveLocation')->name('locations.resolve');

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
		Route::post('/{category}/filter', 'filter')->name('children');
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
		Route::post('/{inventory}/adjust', [InventoryController::class, 'adjust'])->name('adjust');

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

	Route::resource('adjustments', StockAdjustmentController::class);

	Route::resource('returns', StockReturnController::class);

	/*
	|--------------------------------------------------------------------------
	| Global Framework Utilities & Geographic Lookup
	|--------------------------------------------------------------------------
	*/
	Route::post('products/filter', [FilterController::class, 'products'])->name('products.filter');
	Route::post('adjustments/filter', [FilterController::class, 'adjustments'])->name('adjustments.filter');

	// Sovereign Countries REST Resource
	Route::resource('countries', CountryController::class);

	// Cascading Geographic Data AJAX Routes
	Route::prefix('countries')->name('countries.')->controller(CountryController::class)->group(function () {
		Route::post('/{country}/cities', 'cities')->name('cities');
		Route::post('/{country}/states', 'states')->name('states');
	});