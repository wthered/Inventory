<?php

	namespace Database\Seeders\inventories\Concerns;

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

					foreach ($locations->shuffle() as $location) {
						// 25% πιθανότητα μια τοποθεσία να έχει απόθεμα
						if (fake()->boolean(25)) {
							$productId = $products->random();

							$batch[] = [
								'product_id'         => $productId,
								'warehouse_id'       => $warehouse->id,
								'location_id'        => $location->id,
								'quantity'           => mt_rand(20, 1000),
								'reserved_quantity'  => mt_rand(0, 10),
								'batch_number'       => 'BATCH-' . Str::padLeft(mt_rand(1, 999), 3, '0'),
								'manufacturing_date' => $now->copy()->subMonths(rand(1, 12))->format('Y-m-d'),
								'expiry_date'        => $now->copy()->addYears(2)->format('Y-m-d'),
								'created_at'         => $now,
								'updated_at'         => $now,
							];
						}

						// Flush batch ανά 512 εγγραφές
						if (count($batch) >= self::BATCH_SIZE) {
							// Αντί για DB::table('inventories')->insert($batch);
							DB::table('inventories')->upsert(
								$batch,
								['product_id', 'warehouse_id', 'location_id', 'batch_number'], // Το μοναδικό κλειδί
								['quantity', 'updated_at'] // Τι να κάνει update αν το βρει
							);
							$batch = [];
						}
						$this->command->getOutput()->write("."); // Progress indicator
					}

					if (!empty($batch)) {
						// Αντί για DB::table('inventories')->insert($batch);
						DB::table('inventories')->upsert(
							$batch,
							['product_id', 'warehouse_id', 'location_id', 'batch_number'], // Το μοναδικό κλειδί
							['quantity', 'updated_at'] // Τι να κάνει update αν το βρει
						);
					}
				});

				$this->command->line(""); // New line after warehouse finish
			}
		}
	}