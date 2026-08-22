<?php

	namespace Database\Seeders\inventories\Concerns;

	use App\DataTransferObjects\ProductDTO;
	use App\Models\Product;
	use Carbon\Carbon;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Str;

	trait CanPopulateInventory {
		/**
		 * Seed the inventories table using chunking to prevent memory exhaustion.
		 */
		protected function seedInventoryRecords($products, $warehouses): void {
			$this->command->info('--- Starting Inventory Population ---');
			$affectedProducts = Collection::empty();

			foreach ($warehouses->shuffle() as $key => $warehouse) {
				$this->command->comment("Filling Warehouse: ".$warehouse->name."......");

				DB::table('warehouse_locations')
				  ->where('warehouse_id', $warehouse->id)
				  ->chunkById(self::BATCH_SIZE, function ($locations) use ($products, $warehouse, &$affectedProducts) {
					  $batch = [];
					  $generatedInBatch = [];

					  foreach ($locations->shuffle() as $location) {
						  if ($products->isEmpty()) {
							  $products = Product::query()->pluck('id');
						  }
						  $product = new ProductDTO($products->random());

						  // Ensure base stock value is valid
						  $stockPool = max(1, (int) $product->current_stock);

						  $batchNumber = 'BATCH-'.Str::upper(Str::random(4).'-'.Str::random(4)).'-'.Str::padLeft(mt_rand(1, 99999), 5, '0');
						  $signature = $product->id.'-'.$warehouse->id.'-'.$location->id.'-'.$batchNumber;

						  if (isset($generatedInBatch[$signature])) {
							  continue;
						  }

						  $generatedInBatch[$signature] = true;
						  $affectedProducts->push($product->id);

						  $remainingStock = $stockPool;

						  while ($remainingStock > 0) {
							  $quantity = mt_rand(1, $remainingStock);

							  // Calculate reserved stock safely (up to 30% of total physical stock)
							  $reservedQuantity = mt_rand(0, floor($quantity * 0.3));
							  $totalCost = round($quantity * $product->cost_price, 2);

							  $batch[] = [
								  'product_id'         => $product->id,
								  'warehouse_id'       => $warehouse->id,
								  'location_id'        => $location->id,
								  'quantity'           => $quantity,
								  'reserved_quantity'  => $reservedQuantity,
								  'unit_cost'          => $product->cost_price,
								  'total_cost'         => $totalCost,
								  'batch_number'       => $batchNumber,
								  'manufacturing_date' => Carbon::now()->subMonths(mt_rand(0, 12))->subDays(mt_rand(0, 30)),
								  'expiry_date'        => Carbon::now()->addYears(mt_rand(1, 5))->addMonths(mt_rand(0, 11)),
								  'created_at'         => Carbon::now()->subHours(mt_rand(0, 23))->subMinutes(mt_rand(0, 59))->subSeconds(mt_rand(0, 59)),
								  'updated_at'         => Carbon::now(),
							  ];

							  $remainingStock -= $quantity;
						  }

						  $this->command->getOutput()->write('.');
					  }

					  // Perform batch upsert with all relevant updated columns
					  if (!empty($batch)) {
						  DB::table('inventories')->upsert(
							  $batch,
							  ['product_id', 'warehouse_id', 'location_id', 'batch_number'],
							  [
								  'quantity',
								  'reserved_quantity',
								  'unit_cost',
								  'total_cost',
								  'manufacturing_date',
								  'expiry_date',
								  'updated_at'
							  ]
						  );
					  }
				  });

				$warehouses->forget($key);
				$this->command->info("\nEnd of Warehouse #".$warehouse->id." or ".$warehouse->name." with ".$warehouses->count()." left to be done......");
			}

			// ✅ Recalculate current_stock as SUM(available_quantity) to maintain matching values
			$this->command->info('🔄 Recalculating current stock totals for affected products...');

			$uniqueProductIds = $affectedProducts->unique()->values()->all();

			if (!empty($uniqueProductIds)) {
				foreach (array_chunk($uniqueProductIds, self::BATCH_SIZE) as $chunk) {
					DB::statement("
					UPDATE products p
					INNER JOIN (
						SELECT product_id, SUM(available_quantity) as calculated_stock
						FROM inventories
						WHERE product_id IN (".implode(',', array_map('intval', $chunk)).")
						GROUP BY product_id
					) inv ON p.id = inv.product_id
					SET p.current_stock = inv.calculated_stock
				");
				}
			}

			$this->command->info('✅ Inventory seeding and stock sync complete.');
		}
	}
