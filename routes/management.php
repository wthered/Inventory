<?php

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
	use App\Http\Controllers\Stock\StockTransferController;
	use Illuminate\Support\Facades\Route;

	/******************************************************************
	 ** If you want modern grouping + prefixes → bootstrap/app.php
	 ** If you want simple inclusion → require in web.php
	 ** I chose the most modern option (bootstrap/app.php)
	 ******************************************************************/

	Route::middleware([
		'auth',
		'verified'
	])->group(function () {

		// Products Resource
		Route::resource('products', ProductController::class);

		Route::prefix('products')->name('products.')->group(function () {
			// Product Image
			Route::post('/{product}/upload-images', [
				ProductImageController::class,
				'store'
			])->name('image.upload');

			// filter products in http://inventory.pliassas.gr/products
			Route::post('/filter', [
				FilterController::class,
				'products'
			]);

			Route::delete('/{product}/remove-image', [
				ProductImageController::class,
				'delete'
			])->name('image.remove');

			// Διαδρομή για την ενέργεια Clone (αντιγραφή)
			Route::get('{product}/clone', [
				ProductController::class,
				'clone'
			])->name('clone');

			// Διαδρομή για την προβολή ιστορικού
			Route::get('{product}/history', [
				ProductController::class,
				'history'
			])->name('history');

			Route::post('{product}/locations', [
				InventoryController::class,
				'getLocations'
			])->name('locations');

			Route::post('{product}/inventories', [
				InventoryController::class,
				'getInventories'
			])->name('inventories');

			Route::post('{product}/information', [
				ProductController::class,
				'getInformation'
			])->name('information');

			Route::resource('categories', CategoryController::class);

			Route::prefix('categories')->name('categories.')->group(function () {
				// Get all level 2 categories
				Route::post('/{category}/filter', [
					CategoryController::class,
					'filter'
				])->name('getSubCategories');
			});
		});

		// Warehouses
		Route::prefix('warehouses')->name('warehouses.')->group(function () {
			Route::resource('warehouse', WarehouseController::class);

			Route::post('/warehouse/{warehouse}/locations', [
				WarehouseController::class,
				'getLocations'
			])->name('locations');

			Route::post('/warehouse/{warehouse}/locations/zones', [
				WarehouseController::class,
				'getZones'
			])->name('locations');

			Route::post('/warehouse/{warehouse}/locations/aisles', [
				WarehouseController::class,
				'getAisles'
			])->name('locations');

			Route::post('/warehouse/{warehouse}/locations/racks', [
				WarehouseController::class,
				'getRacks'
			])->name('locations');

			Route::post('/warehouse/{warehouse}/locations/shelves', [
				WarehouseController::class,
				'getShelves'
			])->name('locations');

			Route::post('/warehouse/{warehouse}/locations/bins', [
				WarehouseController::class,
				'getBins'
			])->name('locations');

			Route::post('/warehouse/list', [
				WarehouseController::class,
				'getWarehouseList'
			])->name('list');

			Route::post('/warehouse/{warehouse}/filter', [
				WarehouseController::class,
				'filter'
			])->name('filter');

			Route::post('/warehouse/{warehouse}/status/toggle', [
				WarehouseController::class,
				'toggleStatus'
			])->name('status');

			// 2. Warehouse activity route
			Route::get('/{warehouse}/activity', [
				WarehouseController::class,
				'activity'
			])->name('warehouse.activity');
		});

		// Suppliers
		Route::prefix('suppliers')->name('suppliers.')->group(function () {
			Route::get('/', [
				SupplierController::class,
				'index'
			])->name('index');
		});

		// Customers
		Route::prefix('customers')->name('customers.')->group(function () {
				Route::get('/', [
					CustomerController::class,
					'index'
				])->name('index');
			});

		// Invoices
		Route::prefix('invoices')->name('invoices.')->group(function () {
			Route::get('/', [
				InvoiceController::class,
				'index'
			])->name('index');
		});

		// Purchases
		Route::prefix('purchases')->name('purchases.')->group(function () {
			Route::get('/', [
				PurchaseController::class,
				'index'
			])->name('index');
		});

		// Inventories
		Route::prefix('inventories')->group(function () {
			Route::resource('inventory', InventoryController::class);

			Route::prefix('inventory')->name('stock.')->group(function () {
				Route::post('{inventory}/transfer', [
					StockTransferController::class,
					'store'
				])->name('transfer');

				// Inside the AdjustmentModal, I don't have access to $inventory
				Route::post('{inventory}/adjust', [
					StockAdjustmentController::class,
					'store'
				])->name('adjust');

				Route::post('adjustment/reasons', [
					StockAdjustmentController::class,
					'getReasons'
				])->name('adjustment.reasons');
			});

			Route::resource('transfers', StockTransferController::class);
		});

		// Transfers
//		Route::prefix('transfers')->group(function () {
//			Route::resource('transfer', StockTransferController::class);
//		});
	});