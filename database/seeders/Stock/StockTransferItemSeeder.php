<?php

	namespace Database\Seeders\Stock;

	use App\Enums\Inventory\AdjustmentReason;
	use App\Enums\Inventory\AdjustmentType;
	use App\Models\Product;
	use App\Models\StockAdjustment;
	use Carbon\Carbon;
	use Database\Seeders\ParentSeeder;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\DB;

	class StockTransferItemSeeder extends ParentSeeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			DB::connection()->disableQueryLog();

			// 1. Load minimal product data into an array
			$products = Product::query()->select('id', 'current_stock', 'cost_price')->get();

			if ($products->isEmpty()) {
				$this->command->error("No products found. Run ProductSeeder first.");
				return;
			}

			// 2. Use chunkById for safe memory management on large tables
			StockAdjustment::with([
				'warehouse.locations' => function ($q) {
					$q->select('id', 'warehouse_id');
				}
			])->chunkById(64, function ($adjustments) use (&$products) {

				foreach ($adjustments as $adjustment) {
					$locations = $adjustment->warehouse->locations->pluck('id');

					if ($locations->isEmpty()) {
						continue;
					}

					for ($i = 0; $i < mt_rand(4, 12); $i++) {
						$product  = $products->random();
						$creation = Carbon::yesterday();
						$type     = fake()->randomElement(AdjustmentType::cases());
						$reason   = $type == AdjustmentType::INCREASE ? fake()->randomElement(AdjustmentReason::increaseReasons()) : fake()->randomElement(AdjustmentReason::decreaseReasons());
						$quantity = mt_rand(1, 64);
						$after    = max($product['current_stock'] ?? 0, 0);
						$before   = $after - ($quantity * fake()->randomElement([
									1,
									-1
								]));

						$this->list->push([
							'stock_adjustment_id' => $adjustment->id,
							'product_id'          => $product['id'],
							'location_id'         => $locations->random(),
							'reason'              => $reason->value,
							'type'                => $type->value,
							'quantity'            => $quantity,
							'quantity_before'     => $before,
							'quantity_after'      => $after,
							'unit_cost'           => $product['cost_price'] ?? mt_rand(24, 128),
							'notes'               => 'Verified by floor supervisor',
							'created_at'          => $creation->subHours(mt_rand(0, 23))->subMinutes(mt_rand(0, 59))->subSeconds(mt_rand(0, 59)),
							'updated_at'          => $creation->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59)),
						]);

						// Flush batch frequently to keep memory consumption low
						if ($this->list->count() >= self::BATCH_SIZE) {
							DB::table('stock_adjustment_items')->insert($this->list->toArray());
							$this->list = Collection::empty();
						}
					}
				}

				// Insert any remaining items for this chunk
				if ($this->list->isNotEmpty()) {
					DB::table('stock_adjustment_items')->insert($this->list->toArray());
				}

				// Explicitly clear memory cycles
				unset($adjustments, $batchList);
//				gc_collect_cycles();
			});
		}
	}