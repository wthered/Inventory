<?php

	namespace Database\Seeders\Stock;

	use App\Enums\Inventory\AdjustmentReason;
	use App\Enums\Inventory\TransactionType;
	use App\Models\Product;
	use App\Models\StockAdjustment;
	use App\Models\StockAdjustmentItem;
	use Carbon\Carbon;
	use Database\Seeders\ParentSeeder;
	use Illuminate\Database\Seeder;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\DB;

	class StockAdjustmentItemSeeder extends ParentSeeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			DB::connection()->disableQueryLog();

			// 1. Μετατροπή σε Array για ελάχιστη χρήση μνήμης
			$products = Product::select('id', 'current_stock', 'cost_price')->get()->toArray();

			// Χρησιμοποιούμε τοπική μεταβλητή αντί για $this->list
			$batchList = [];

			// 2. Χρήση chunkById για καλύτερο memory management
			StockAdjustment::with(['warehouse.locations' => function($q) {
				$q->select('id', 'warehouse_id'); // Παίρνουμε μόνο τα απαραίτητα
			}])->chunkById(128, function ($adjustments) use (&$products, &$batchList) {
				foreach ($adjustments as $adjustment) {
					$locations = $adjustment->warehouse->locations->pluck('id')->toArray();

					if (empty($locations)) {
						continue;
					}

					// Επιλογή τυχαίων προϊόντων χωρίς τη βαριά μέθοδο ->random() της Collection
					for ($i = 0; $i < mt_rand(4, 12); $i++) {
						$product = $products[array_rand($products)];

						$creation = Carbon::yesterday()->subHours(mt_rand(1, 23));
						$type = fake()->randomElement(TransactionType::cases());
						$quantity = mt_rand(1, 64);
						$after = max($product['current_stock'] ?? 0, 0);
						$before = $after - ($quantity * $type->sign());

						$batchList[] = [
							'stock_adjustment_id' => $adjustment->id,
							'product_id'          => $product['id'],
							'location_id'         => $locations[array_rand($locations)],
							'reason'              => Collection::make($type->validReasons())->random()->value,
							'type'                => $type->value,
							'quantity'            => $quantity,
							'quantity_before'     => $before,
							'quantity_after'      => $after,
							'unit_cost'           => $product['cost_price'] ?? mt_rand(24, 128),
							'notes'               => 'Verified by floor supervisor',
							'created_at'          => $creation,
							'updated_at'          => $creation,
						];

						if (count($batchList) >= self::BATCH_SIZE) {
							DB::table('stock_adjustment_items')->insert($batchList);
							$batchList = []; // Άμεση αποδέσμευση
						}
					}
				}
				// Καθαρισμός των εσωτερικών αντικειμένων του Chunk
				unset($adjustments);
			});

			if (!empty($batchList)) {
				DB::table('stock_adjustment_items')->insert($batchList);
			}
		}
	}
