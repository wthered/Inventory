<?php

	namespace Database\Seeders\inventories\Concerns;

	use App\Models\Inventories\Inventory;
	use Carbon\Carbon;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Str;

	trait CanPopulateInventory {
		/**
		 * Γεμίζει τον πίνακα inventories χρησιμοποιώντας chunking στις τοποθεσίες
		 * για να αποφύγουμε το Memory Exhaustion (Killed).
		 */
		protected function seedInventoryRecords($products, $warehouses): void {
			$this->command->info('--- Starting Inventory Population ---');
			$now = Carbon::now();

			foreach ($warehouses->shuffle() as $warehouse) {
				$this->command->comment("Filling Warehouse: ".$warehouse->name."......");

				// ✅ Χρησιμοποιούμε chunkById για να μην φορτώνουμε 1 εκατομμύριο IDs στη μνήμη
				DB::table('warehouse_locations')->where('warehouse_id', $warehouse->id)->chunkById(self::BATCH_SIZE, function ($locations) use ($products, $warehouse, $now) {
					$batch = [];
					// Track combination signatures generated inside this specific chunk execution
					$generatedInBatch = [];

					foreach ($locations->shuffle() as $location) {
						if (fake()->boolean(25)) {
							$productId = $products->random();

							// Generate a more distinct batch number to minimize random collisions
							$batchNumber = 'BATCH-' . Str::padLeft(mt_rand(1, 999), 3, '0');

							// Unique composite signature string matching your database intent
							$signature = "{$productId}-{$warehouse->id}-{$location->id}-{$batchNumber}";

							// 1. Check if we already staged this exact combination in this loop's memory
							if (isset($generatedInBatch[$signature])) {
								continue;
							}

							// 2. Check the database for existing records
							$existsInDatabase = DB::table('inventories')->where([
								'product_id'   => $productId,
								'warehouse_id' => $warehouse->id,
								'location_id'  => $location->id,
								'batch_number' => $batchNumber, // Must include batch_number if it's part of your upsert key
							])->exists();

							if ($existsInDatabase) {
								continue;
							}

							// Track it in memory so another iteration in this chunk doesn't duplicate it
							$generatedInBatch[$signature] = true;

							$quantity = mt_rand(20, 1000);
							$unit_cost = fake()->randomFloat(2, 1, 32 * 1024);
							$batch[] = [
								'product_id'         => $productId,
								'warehouse_id'       => $warehouse->id,
								'location_id'        => $location->id,
								'quantity'           => $quantity,
								'reserved_quantity'  => mt_rand(0, $quantity),
								'batch_number'       => $batchNumber,
								'unit_cost'          => $unit_cost,
								'total_cost'         => $quantity * $unit_cost,
								'manufacturing_date' => $now->copy()->subMonths(mt_rand(0, 12))->subDays(mt_rand(0, 30))->format('Y-m-d'),
								'expiry_date'        => $now->copy()->addYears(mt_rand(0, 8))->addMonths(mt_rand(0, 12))->addDays(mt_rand(0, 30))->format('Y-m-d'),
								'created_at'         => $now->copy()->subHours(mt_rand(0, 23))->subMinutes(mt_rand(0, 59))->subSeconds(mt_rand(0, 59)),
								'updated_at'         => $now->copy()->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59)),
							];
						}

						// Flush batch
						if (count($batch) >= self::BATCH_SIZE) {
							DB::table('inventories')->upsert(
								$batch,
								['product_id', 'warehouse_id', 'location_id', 'batch_number'],
								['quantity', 'updated_at']
							);
							$batch = [];
							$generatedInBatch = []; // Clear the tracking array for the next batch flush
						}
						$this->command->getOutput()->write('.');
					}

					if (!empty($batch)) {
						DB::table('inventories')->upsert(
							$batch,
							['product_id', 'warehouse_id', 'location_id', 'batch_number'],
							['quantity', 'updated_at']
						);
					}
				});

				$this->command->info("End of Warehouse #" . $warehouse->id . ": ".$warehouse->name."......");
			}
		}
	}