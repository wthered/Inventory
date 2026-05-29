<?php

	namespace Database\Seeders;

	use Database\Seeders\inventories\Audits\InventoryAuditSeeder;
	use Database\Seeders\inventories\InventorySeeder;
	use Database\Seeders\inventories\InventoryTransactionsSeeder;
	use Database\Seeders\products\BrandSeeder;
	use Database\Seeders\products\CategorySeeder;
	use Database\Seeders\Purchases\PurchaseOrderHistorySeeder;
	use Database\Seeders\Purchases\PurchaseOrderItemSeeder;
	use Database\Seeders\Purchases\PurchaseOrderSeeder;
	use Database\Seeders\Stock\StockAdjustmentSeeder;
	use Database\Seeders\Stock\StockReturnItemSeeder;
	use Database\Seeders\Stock\StockReturnSeeder;
	use Database\Seeders\Stock\StockTransferSeeder;
	use Illuminate\Database\Seeder;
	use Illuminate\Support\Facades\Schema;
	use Database\Seeders\Orders\SalesOrderSeeder;
	use Database\Seeders\Stock\StockAdjustmentItemSeeder;
	use Database\Seeders\Stock\StockTransferItemSeeder;

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
				SupplierSeeder::class,
				CustomerSeeder::class,
				WarehouseSeeder::class,
				CategorySeeder::class,
				BrandSeeder::class,
				ProductSeeder::class,
			]);

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
			$this->call([
				SalesOrderSeeder::class,
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

			Schema::enableForeignKeyConstraints();

			// Inventory Audit Seeder (along with items)
			$this->call([
				InventoryAuditSeeder::class,
			]);
		}
	}
