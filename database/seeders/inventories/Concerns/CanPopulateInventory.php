<?php

	namespace Database\Seeders\inventories\Concerns;

	use App\Models\Product;
	use App\Models\WarehouseLocation;
	use Carbon\Carbon;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Str;

	trait CanPopulateInventory {
		protected function seedInventoryRecords($products, $warehouses): void {
			$this->command->info('--- Starting Inventory Population ---');

			// 1. Fetch all available locations grouped by warehouse to avoid N+1 queries
			$locationsByWarehouse = WarehouseLocation::query()
			                                         ->whereIn('warehouse_id', $warehouses->pluck('id'))
			                                         ->get()
			                                         ->groupBy('warehouse_id');

			if ($locationsByWarehouse->isEmpty()) {
				$this->command->error('No warehouse locations found. Run WarehouseSeeder first.');
				return;
			}

			$batch = [];
			$affectedProducts = new Collection();
			$warehouseIds = $warehouses->pluck('id');

			// 2. Loop over EVERY product to guarantee at least 1 location assignment
			Product::query()->whereIn('id', $products)->chunk(self::BATCH_SIZE, function ($productChunk) use (
				$locationsByWarehouse,
				$warehouseIds,
				&$batch,
				&$affectedProducts
			) {
				foreach ($productChunk as $product) {
					$stockPool = max(1, (int) $product->current_stock);
					$remainingStock = $stockPool;

					// Distribute this product's stock across 1 to 3 random locations/warehouses
					$locationsToAssign = mt_rand(1, min(3, $locationsByWarehouse->flatten()->count()));

					for ($i = 0; $i < $locationsToAssign && $remainingStock > 0; $i++) {
						$randomWarehouseId = $warehouseIds->random();
						$availableLocations = $locationsByWarehouse->get($randomWarehouseId);

						if (!$availableLocations || $availableLocations->isEmpty()) {
							continue;
						}

						$location = $availableLocations->random();

						// Allocate stock portion
						$quantity = ($i === $locationsToAssign - 1) ? $remainingStock : mt_rand(1, $remainingStock);
						$unitCost = $product->cost_price ?? 10.00;

						$batch[] = [
							'product_id'         => $product->id,
							'warehouse_id'       => $randomWarehouseId,
							'location_id'        => $location->id,
							'quantity'           => $quantity,
							'reserved_quantity'  => mt_rand(0, (int) floor($quantity * 0.3)),
							'unit_cost'          => $unitCost,
							'total_cost'         => round($quantity * $unitCost, 2),
							'batch_number'       => 'BATCH-'.Str::upper(Str::random(4).'-'.Str::random(4)).'-'.Str::padLeft(mt_rand(1, 99999), 5, '0'),
							'manufacturing_date' => Carbon::now()->subMonths(mt_rand(0, 12))->subDays(mt_rand(0, 30)),
							'expiry_date'        => Carbon::now()->addYears(mt_rand(1, 5))->addMonths(mt_rand(0, 11)),
							'created_at'         => Carbon::now()->subHours(mt_rand(0, 23))->subMinutes(mt_rand(0, 59)),
							'updated_at'         => Carbon::now(),
						];

						$remainingStock -= $quantity;
						$affectedProducts->push($product->id);
					}
				}

				// 3. Perform upsert per product chunk
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
					$batch = []; // Reset batch array after insertion
				}
				$this->command->getOutput()->write('.');
			});

			// 4. Recalculate current_stock as SUM(available_quantity)
			$this->command->info("\n🔄 Recalculating current stock totals for affected products...");
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
