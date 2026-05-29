<?php

	namespace Database\Seeders\products;

	use App\Models\Inventories\Inventory;
	use App\Models\Product;
	use App\Models\Warehouse;
	use App\Models\WarehouseLocation;
	use Illuminate\Database\Seeder;

	class InventorySeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			$products = Product::query()->pluck('id');
			$warehouses = Warehouse::query()->pluck('id');
			$locations = WarehouseLocation::query()->pluck('id')->toArray();
			for ($product = 0; $product < $products->count(); $product++) {
				for ($warehouse = 0; $warehouse < $warehouses->count(); $warehouse++) {
					$quantity = fake()->numberBetween(16, 1024);
					Inventory::query()
						->create([
							'product_id'         => fake()->randomElement($products),
							'warehouse_id'       => fake()->randomElement($warehouses),
							'location_id'        => fake()->randomElement($locations),
							'quantity'           => $quantity,
							'reserved_quantity'  => fake()->numberBetween(0, $quantity),
							'batch_number'       => fake()
								->optional()
								->bothify('BATCH-'.date('Y-m', mt_rand(1704067200, time())).'-####-###'),
							'manufacturing_date' => fake()->dateTimeBetween('-1 year', '-1 month'),
							'expiry_date'        => fake()
								->optional()
								->dateTimeBetween('now', '+2 years'),
						]);
				}
			}
		}
	}
