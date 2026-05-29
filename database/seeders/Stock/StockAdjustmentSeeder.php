<?php

	namespace Database\Seeders\Stock;

	use App\Enums\Inventory\AdjustmentReason;
	use App\Models\Product;
	use App\Models\StockAdjustment;
	use App\Models\StockAdjustmentItem;
	use App\Models\User;
	use App\Models\Warehouse;
	use App\Models\WarehouseLocation;
	use Carbon\Carbon;
	use Illuminate\Database\Seeder;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Str;

	class StockAdjustmentSeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			$warehouses = Warehouse::all();
			$users = User::pluck('id');
			$products = Product::all();

			if ($products->isEmpty()) {
				$this->command->error('No products found. Skipping Adjustments.');
				return;
			}

			$warehouses->each(function (Warehouse $warehouse) use ($users, $products) {
				// Περιορίζουμε λίγο τα adjustments ανά αποθήκη για ταχύτητα (π.χ. 5-10)
				Collection::range(1, mt_rand(5, 10))->each(function ($i) use ($users, $warehouse, $products) {

					$createdAt = now()->subDays(rand(1, 30));
					$status = fake()->randomElement(['draft', 'pending', 'approved']);

					$adjustment = StockAdjustment::create([
						'adjustment_number' => 'ADJ-' . Str::upper(Str::random(8)),
						'warehouse_id'      => $warehouse->id, // ✅ Η αποθήκη του loop
						'adjustment_date'   => $createdAt,
						'status'            => $status,
						'created_by'        => $users->random(),
						'approved_by'       => ($status === 'approved') ? $users->random() : null,
						'approved_at'       => ($status === 'approved') ? $createdAt->addHours(2) : null,
						'notes'             => 'Monthly stock reconciliation',
					]);

					// Παίρνουμε τοποθεσίες ΜΟΝΟ για τη συγκεκριμένη αποθήκη
					$warehouseLocations = WarehouseLocation::query()->where('warehouse_id', $warehouse->id)->pluck('id');
					if ($warehouseLocations->isEmpty()) {
						return;
					}

					// 5-15 items ανά adjustment είναι υπεραρκετά
					$itemsCount = mt_rand(5, 15);
					Collection::range(1, $itemsCount)->each(function (int $i) use ($adjustment, $warehouseLocations, $products) {
						$product = $products->random();
						$reason = fake()->randomElement(AdjustmentReason::cases());

						$adjustment->items()->create([
							'product_id'      => $product->id,
							'location_id'     => $warehouseLocations->random(), // ✅ Σωστή τοποθεσία
							'reason'          => $reason->value,
							'quantity'        => mt_rand(1, 20),
							'quantity_before' => $product->quantity ?? 0,
							'unit_cost'       => $product->cost_price ?? rand(10, 100),
							'notes'           => 'Verified by floor supervisor',
						]);
					});

					$this->command->info("Created Adjustment ".$adjustment->adjustment_number." with ".$itemsCount." items for warehouse in " . $warehouse->name . "\r.");
				});
			});
		}
	}
