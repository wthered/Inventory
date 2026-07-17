<?php

	namespace Database\Seeders\Stock;

	use App\Models\Inventories\Inventory;
	use App\Models\Product;
	use App\Models\StockTransfer;
	use App\Models\User;
	use App\Models\WarehouseLocation;
	use Database\Seeders\ParentSeeder;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\DB;

	class StockTransferItemSeeder extends ParentSeeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			// 1. Fetch only what we need to minimize memory footprint
			$products = Product::all();

			// Sort the user IDs to comply with department rules and optimize lookups
			$users = User::role(['warehouse_manager', 'admin'])->pluck('id')->sort()->values();
			if ($users->isEmpty()) {
				$users = User::pluck('id')->sort()->values();
			}

			// Master buffer to hold raw data before chunked database insertion
			$now          = now()->format('Y-m-d H:i:s');

			// 2. Iterate through transfers efficiently using a cursor to prevent memory bloat
			foreach (StockTransfer::query()->cursor() as $transfer) {
				$randomProducts = $products->random(mt_rand(2, 8));

				// Fetch locations as lightweight flat arrays
				$sourceLocations = WarehouseLocation::query()->where('warehouse_id', $transfer->source_warehouse_id)->pluck('id');
				$targetLocations = WarehouseLocation::query()->where('warehouse_id', $transfer->target_warehouse_id)->pluck('id');

				if ($sourceLocations->isEmpty() || $targetLocations->isEmpty()) {
					continue;
				}

				foreach ($randomProducts as $product) {
					// Efficient database check using database-level randomness instead of collecting entities
					$sourceInventory = Inventory::query()
						->where('product_id', $product->id)
						->where('warehouse_id', $transfer->source_warehouse_id)
						->inRandomOrder()
						->first();

					if (!$sourceInventory || $sourceInventory->quantity <= 0) {
						continue;
					}

					$qty           = mt_rand(1, $sourceInventory->quantity);
					$currentStatus = $transfer->status_id->value;

					// 3. Collect RAW arrays instead of calling Eloquent $transfer->items()->create()
					$this->list->push([
						'stock_transfer_id'  => $transfer->id,
						'product_id'         => $product->id,
						'batch_number'       => $sourceInventory->batch_number,
						'source_location_id' => $sourceInventory->location_id,
						'target_location_id' => $targetLocations->random(),
						'quantity_requested' => $qty,
						'quantity_delivered' => ($currentStatus >= 2) ? $qty : 0,
						'quantity_received'  => ($currentStatus === 3) ? $qty : 0,
						'processed_by'       => $users->random(),
						'processed_at'       => ($currentStatus >= 2) ? $transfer->created_at->addSeconds(mt_rand(1, 24 * 3600))->format('Y-m-d H:i:s') : null,
						'created_at'         => $now,
						'updated_at'         => $now,
					]);
					// 4. In chunks, into the database we save!
					if ($this->list->count() >= self::BATCH_SIZE) {
						DB::table('stock_transfer_items')->insert($this->list->toArray());
						$this->list = Collection::empty(); // Wipe buffer immediately to free memory
						$this->command->info('Transfer items written into database because '.$this->list->count().' > ' . self::BATCH_SIZE);
					}
				}
			}

			// 5. Save remaining records left in the buffer
			if ($this->list->count() > 0) {
				DB::table('stock_transfer_items')->insert($this->list->toArray());
//				$this->list = Collection::empty();
			}
		}
	}