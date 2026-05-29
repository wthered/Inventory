<?php

	namespace Database\Seeders\inventories;

	use App\Models\Product;
	use App\Models\Warehouse;
	use Database\Seeders\ParentSeeder;
	use Database\Seeders\inventories\Concerns\CanEnsureLocations;
	use Database\Seeders\inventories\Concerns\CanPopulateInventory;

	class InventorySeeder extends ParentSeeder {

		// Εδώ "φοράμε" τα traits στην κλάση
		use CanEnsureLocations, CanPopulateInventory;

		public function run(): void {
			$products = Product::pluck('id');
			$warehouses = Warehouse::all();

			// 1. Χρησιμοποιεί τη μέθοδο από το CanEnsureLocations.php
			$this->ensureWarehouseLocations($warehouses->shuffle());

			// 2. Χρησιμοποιεί τη μέθοδο από το CanPopulateInventory.php
			$this->seedInventoryRecords($products, $warehouses);

			$this->command->info('✅ Inventory Seeding Completed Successfully.');
		}
	}