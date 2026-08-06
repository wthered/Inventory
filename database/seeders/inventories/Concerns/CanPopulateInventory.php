<?php

	namespace Database\Seeders\inventories\Concerns;

	use App\DataTransferObjects\ProductDTO;
	use Carbon\Carbon;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Str;

	trait CanPopulateInventory {
		/**
		 * Γεμίζει τον πίνακα inventories χρησιμοποιώντας chunking στις τοποθεσίες
		 * για να αποφύγουμε το Memory Exhaustion (Killed).
		 */
		/**
		 * Γεμίζει τον πίνακα inventories χρησιμοποιώντας chunking στις τοποθεσίες
		 * για να αποφύγουμε το Memory Exhaustion (Killed).
		 */
		protected function seedInventoryRecords($products, $warehouses): void {
			$this->command->info('--- Starting Inventory Population ---');
			$now = Carbon::now();
			$affectedProducts = Collection::empty(); // Track modified products for stock recalculation

			foreach ($warehouses->shuffle() as $warehouse) {
				$this->command->comment("Filling Warehouse: ".$warehouse->name."......");

				DB::table('warehouse_locations')
				  ->where('warehouse_id', $warehouse->id)
				  ->chunkById(self::BATCH_SIZE, function ($locations) use ($products, $warehouse, &$affectedProducts) {
					  $batch = [];
					  $generatedInBatch = [];

					  foreach ($locations->shuffle() as $location) {
//						  if (fake()->boolean(25)) {
						  $product = new ProductDTO($products->random());

						  // Generate wider batch numbers to prevent unexpected upsert overwrites
						  $batchNumber = 'BATCH-'.Str::upper(Str::random(4).'-'.Str::random(4))."-".Str::padLeft(mt_rand(1, 99999), 5, '0');
						  $signature = $product->id."-".$warehouse->id."-".$location->id."-".$batchNumber;

						  // Guard against in-memory batch collisions
						  if (isset($generatedInBatch[$signature])) {
							  continue;
						  }

						  $generatedInBatch[$signature] = true;
						  $affectedProducts->push($product->id);

						  $unit_cost = fake()->randomFloat(2, 1, 1000);

						  $initial_quantity = $product->current_stock;
						  $available = $initial_quantity;

						  while ($available > 0) {
							  // 1. Pick a random quantity between 1 and the remaining available stock
							  $quantity = mt_rand(1, $available);

							  // 2. Add to your batch array
							  $batch[] = [
								  'product_id'         => $product->id,
								  'warehouse_id'       => $warehouse->id,
								  'location_id'        => $location->id,
								  'quantity'           => $quantity,
								  'reserved_quantity'  => $available - $quantity,
								  'unit_cost'          => $unit_cost,
								  'total_cost'         => $quantity * $unit_cost,
								  'batch_number'       => $batchNumber,
								  'manufacturing_date' => Carbon::now()->copy()->subMonths(mt_rand(0, 12))->subDays(mt_rand(0, 30))->format('Y-m-d'),
								  'expiry_date'        => Carbon::now()->copy()->addYears(mt_rand(0, 8))->addMonths(mt_rand(0, 12))->addDays(mt_rand(0, 30))->format('Y-m-d'),
								  'created_at'         => Carbon::now()->copy()->subHours(mt_rand(0, 23))->subMinutes(mt_rand(0, 59))->subSeconds(mt_rand(0, 59)),
								  'updated_at'         => Carbon::now()->copy()->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59)),
							  ];

							  // 3. Subtract from available stock
							  $available -= $quantity;
						  }

						  $this->command->getOutput()->write('.');
					  }

					  // Perform batch upsert at the end of each location chunk
					  if (!empty($batch)) {
						  DB::table('inventories')->upsert(
							  $batch,
							  ['product_id', 'warehouse_id', 'location_id', 'batch_number'],
							  ['quantity', 'updated_at']
						  );
					  }
				  });

				$this->command->info("\nEnd of Warehouse #".$warehouse->id.": ".$warehouse->name."......");
			}

			// ✅ Recalculate stock once in bulk after all inventory seeding completes
			$this->command->info('🔄 Recalculating current stock totals for affected products...');

			$affectedProducts->unique()->chunk(128)->each(function ($chunk) {
				foreach ($chunk as $productId) {
					$totalStock = DB::table('inventories')->where('product_id', $productId)->sum('quantity');
					DB::table('products')->where('id', $productId)->update(['current_stock' => $totalStock]);
				}
			});

			$this->command->info('✅ Inventory seeding and stock sync complete.');
		}
	}