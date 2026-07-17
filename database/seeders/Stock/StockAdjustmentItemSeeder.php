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

	class StockAdjustmentItemSeeder extends ParentSeeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			DB::connection()
				->disableQueryLog();

			// 1. Μετατροπή σε Array για ελάχιστη χρήση μνήμης
			$products = Product::select('id', 'current_stock', 'cost_price')->get()->toArray();

			// Χρησιμοποιούμε τοπική μεταβλητή αντί για $this->list
			$batchList = [];

			// 2. Χρήση chunkById για καλύτερο memory management
			StockAdjustment::with([
				'warehouse.locations' => function ($q) {
					$q->select('id', 'warehouse_id'); // Παίρνουμε μόνο τα απαραίτητα
				}
			])->chunk(self::BATCH_SIZE, function ($adjustments) use (&$products, &$batchList) {
				foreach ($adjustments as $adjustment) {
					$locations = $adjustment->warehouse->locations->pluck('id')->toArray();

					if (empty($locations)) {
						continue;
					}

					// Επιλογή τυχαίων προϊόντων χωρίς τη βαριά μέθοδο ->random() της Collection
					for ($i = 0; $i < mt_rand(4, 12); $i++) {
						$product = $products[array_rand($products)];

						$creation = Carbon::yesterday();
						$type     = fake()->randomElement(AdjustmentType::cases());
						$reason   = $type == AdjustmentType::INCREASE ? fake()->randomElement(AdjustmentReason::increaseReasons()) : fake()->randomElement(AdjustmentReason::decreaseReasons());
						$quantity = mt_rand(1, 64);
						$after    = max($product['current_stock'] ?? 0, 0);
						$before   = $after - ($quantity * fake()->randomElement([1, -1]));

						$batchList[] = [
							'stock_adjustment_id' => $adjustment->id,
							'product_id'          => $product['id'],
							'location_id'         => $locations[array_rand($locations)],
							'reason'              => $reason->value,
							'type'                => $type->value,
							'quantity'            => $quantity,
							'quantity_before'     => $before,
							'quantity_after'      => $after,
							'unit_cost'           => $product['cost_price'] ?? mt_rand(24, 128),
							'notes'               => 'Verified by floor supervisor',
							'created_at'          => $creation->subHours(mt_rand(0, 23))->subMinutes(mt_rand(0, 59))->subSeconds(mt_rand(0, 59)),
							'updated_at'          => $creation->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59)),
						];

						if (count($batchList) >= __LINE__) {
							DB::table('stock_adjustment_items')->insert($batchList);
							$batchList = []; // Άμεση αποδέσμευση
						}
					}
				}
				// Καθαρισμός των εσωτερικών αντικειμένων του Chunk
				unset($adjustments);
			});

			if (!empty($batchList)) {
				DB::table('stock_adjustment_items')
					->insert($batchList);
			}
		}
	}
