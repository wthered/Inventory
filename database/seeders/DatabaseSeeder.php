<?php

	namespace Database\Seeders;

	use App\Models\Brand;
	use App\Models\Category;
	use App\Models\Inventories\Inventory;
	use App\Models\Product;
	use App\Models\Sales\SalesOrder;
	use App\Models\Sales\SalesOrderItem;
	use Database\Seeders\HumanResources\DepartmentSeeder;
	use Database\Seeders\HumanResources\EmployeeSeeder;
	use Database\Seeders\HumanResources\LeaveRequestSeeder;
	use Database\Seeders\HumanResources\PositionSeeder;
	use Database\Seeders\inventories\Audits\InventoryAuditSeeder;
	use Database\Seeders\inventories\InventoryMovementLogSeeder;
	use Database\Seeders\inventories\InventorySeeder;
	use Database\Seeders\inventories\InventoryTransactionsSeeder;
	use Database\Seeders\Orders\SalesOrderHistorySeeder;
	use Database\Seeders\Orders\SalesOrderItemSeeder;
	use Database\Seeders\Orders\SalesOrderSeeder;
	use Database\Seeders\products\BrandSeeder;
	use Database\Seeders\products\CategorySeeder;
	use Database\Seeders\Purchases\PurchaseOrderHistorySeeder;
	use Database\Seeders\Purchases\PurchaseOrderItemSeeder;
	use Database\Seeders\Purchases\PurchaseOrderSeeder;
	use Database\Seeders\Stock\StockAdjustmentItemSeeder;
	use Database\Seeders\Stock\StockAdjustmentSeeder;
	use Database\Seeders\Stock\StockReturnItemSeeder;
	use Database\Seeders\Stock\StockReturnSeeder;
	use Database\Seeders\Stock\StockTransferItemSeeder;
	use Database\Seeders\Stock\StockTransferSeeder;
	use Illuminate\Database\Seeder;
	use Illuminate\Support\Facades\Schema;

	class DatabaseSeeder extends Seeder {
		/**
		 * Seed the application's database.
		 */
		public function run(): void {
			Schema::disableForeignKeyConstraints();

			// --- 1. Υποδομή ---
			$this->call([
				RoleAndPermissionSeeder::class,
				UserSeeder::class,
				CountrySeeder::class,
				CitySeeder::class,
				CustomerSeeder::class,
				WarehouseSeeder::class,
				CategorySeeder::class,
				BrandSeeder::class,
				ProductSeeder::class,

				// This seeder also does product_supplier seeding
				SupplierSeeder::class,
			]);

			$this->command->info("Have seeded ".Product::query()->count()." in ".Category::query()->count()." categories and ".Brand::query()->count()." brands");

			// --- 2. Locations & Αρχικό Απόθεμα ---
			$this->call([
				InventorySeeder::class,
			]);

			// --- 3. Purchasing Workflow ---
			$this->call([
				PurchaseOrderSeeder::class,
				PurchaseOrderItemSeeder::class,
				PurchaseOrderHistorySeeder::class,
			]);

			// --- 4. Sales Workflow (Προαιρετικό αλλά καλό) ---
//			$this->call([
//				SalesOrderSeeder::class,
//				SalesOrderItemSeeder::class,
//				SalesOrderHistorySeeder::class,
//			]);
			SalesOrder::unsetEventDispatcher();
			SalesOrderItem::unsetEventDispatcher();

			$this->call([
				SalesOrderSeeder::class,
				SalesOrderItemSeeder::class,
				SalesOrderHistorySeeder::class,
			]);

			// --- 5. Ιστορικό Κινήσεων & Operations ---
			$this->call([
				InventoryTransactionsSeeder::class,

				StockTransferSeeder::class,
				StockTransferItemSeeder::class,

				StockAdjustmentSeeder::class,
				StockAdjustmentItemSeeder::class,

				StockReturnSeeder::class,
				StockReturnItemSeeder::class,
			]);

			// Human Resources
			$this->call([
				DepartmentSeeder::class,
				PositionSeeder::class,
				EmployeeSeeder::class,
				LeaveRequestSeeder::class,
				//				AttendanceSeeder::class,
			]);

			Schema::enableForeignKeyConstraints();

			// Inventory Audit Seeder (along with items)
			$this->call([
				InventoryAuditSeeder::class,
				InventoryMovementLogSeeder::class,
			]);

			// Fix negative inventory quantities post-seeding
			Inventory::query()->where('available_quantity', '<', 0)
			         ->get()
			         ->each(function (Inventory $inventory) {
				         $absAvailable = abs($inventory->available_quantity);
				         $last_update = $inventory->updated_at;

				         $inventory->update([
					         'quantity'   => round($absAvailable + (mt_rand(0, 500) / 100) * $absAvailable),
					         'updated_at' => $last_update,
				         ]);
			         });
		}
	}
