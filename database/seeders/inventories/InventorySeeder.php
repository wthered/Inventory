<?php

	namespace Database\Seeders\inventories;

	use App\Models\Product;
	use App\Models\Warehouse;
	use Database\Seeders\ParentSeeder;
	use Database\Seeders\inventories\Concerns\CanPopulateInventory;

	class InventorySeeder extends ParentSeeder {

		// Κρατάμε ΜΟΝΟ το populate
		use CanPopulateInventory;

		public function run(): void {

			// Γεμίζει τα υπάρχοντα slots με stock
			$this->seedInventoryRecords(Product::pluck('id'), Warehouse::all());

			$this->command->info('✅ Inventory Seeding Completed Successfully.');
		}
	}