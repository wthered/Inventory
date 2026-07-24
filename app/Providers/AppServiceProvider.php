<?php

	namespace App\Providers;

	use App\Models\Brand;
	use App\Models\Category;
	use App\Models\Customer;
	use App\Models\Inventories\Inventory;
	use App\Models\Inventories\InventoryTransaction;
	use App\Models\Product;
	use App\Models\Purchases\PurchaseOrder;
	use App\Models\Purchases\PurchaseOrderItem;
	use App\Models\Sales\SalesOrder;
	use App\Models\Scopes\InventoryTransactionScope;
	use App\Models\Scopes\Products\BrandScope;
	use App\Models\Scopes\Products\CategoryScope;
	use App\Models\Scopes\Products\CustomerScope;
	use App\Models\Scopes\ProductScope;
	use App\Models\Scopes\Stocks\StockAdjustmentScope;
	use App\Models\Scopes\Stocks\StockReturnScope;
	use App\Models\Scopes\SupplierScope;
	use App\Models\Scopes\Warehouses\WarehouseLocationScope;
	use App\Models\Scopes\Warehouses\WarehouseScope;
	use App\Models\StockAdjustment;
	use App\Models\StockAdjustmentItem;
	use App\Models\StockReturn;
	use App\Models\StockReturnItem;
	use App\Models\StockTransfer;
	use App\Models\StockTransferItem;
	use App\Models\Supplier;
	use App\Models\Warehouse;
	use App\Models\WarehouseLocation;
	use App\Observers\Categories\CategoryObserver;
	use App\Observers\Inventory\InventoryTransactionObserver;
	use App\Observers\Products\InventoryObserver;
	use App\Observers\Products\ProductObserver;
	use App\Observers\Purchases\PurchaseOrderItemObserver;
	use App\Observers\Purchases\PurchaseOrderObserver;
	use App\Observers\Sales\SalesOrderObserver;
	use App\Observers\Stock\StockMovementItemObserver;
	use App\Observers\Stock\StockMovementObserver;
	use App\Services\Inventory\InventoryReportService;
	use App\Services\Inventory\LocationOptionsService;
	use App\Services\Inventory\StockMovementService;
	use Illuminate\Support\Facades\Gate;
	use Illuminate\Support\ServiceProvider;

	class AppServiceProvider extends ServiceProvider {
		/**
		 * Register any application services.
		 */
		public function register(): void {
			$this->app->singleton(InventoryReportService::class, function ($app) {
				return new InventoryReportService();
			});

			$this->app->singleton(LocationOptionsService::class, function ($app) {
				return new LocationOptionsService();
			});

			$this->app->singleton(StockMovementService::class, function ($app) {
				return new StockMovementService();
			});
		}

		/**
		 * Bootstrap any application services.
		 */
		public function boot(): void {
			// 📌 Super Admin Bypass: Αν ο χρήστης έχει ρόλο 'admin', παρακάμπτονται όλοι οι έλεγχοι permissions
			Gate::before(function ($user, $ability) {
				return $user->hasRole('admin') ? true : null;
			});


			Product::addGlobalScope(new ProductScope());
			Category::addGlobalScope(new CategoryScope());
			Brand::addGlobalScope(new BrandScope());
			Customer::addGlobalScope(new CustomerScope());
			Supplier::addGlobalScope(new SupplierScope());
			InventoryTransaction::addGlobalScope(new InventoryTransactionScope());
			StockReturn::addGlobalScope(new StockReturnScope());
			StockAdjustment::addGlobalScope(new StockAdjustmentScope());

			// Warehouse related Scopes
			Warehouse::addGlobalScope(new WarehouseScope());
			WarehouseLocation::addGlobalScope(new WarehouseLocationScope());

			// Model Observers
			// AppServiceProvider.php or a dedicated ObserverServiceProvider.php

			// --- Core Catalog ---
			Product::observe(ProductObserver::class);

			// Attach the observer to intercept Category saving lifecycles automatically
			Category::observe(CategoryObserver::class);

			// --- Inventory & Ledger ---
			Inventory::observe(InventoryObserver::class);
			InventoryTransaction::observe(InventoryTransactionObserver::class);

			// --- Procurement (Purchase Orders) ---
			PurchaseOrder::observe(PurchaseOrderObserver::class);
			PurchaseOrderItem::observe(PurchaseOrderItemObserver::class);

			// --- Stock Adjustments  ---
			StockAdjustment::observe(StockMovementObserver::class);
			StockAdjustmentItem::observe(StockMovementItemObserver::class);

			// Stock Returns
			StockReturn::observe(StockMovementObserver::class);
			StockReturnItem::observe(StockMovementItemObserver::class);

			// Stock Transfers
			StockTransfer::observe(StockMovementObserver::class);
			StockTransferItem::observe(StockMovementItemObserver::class);

			// Sales
			SalesOrder::observe(SalesOrderObserver::class);
		}
	}
