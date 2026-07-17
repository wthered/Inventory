<?php

	namespace Database\Seeders\inventories\Concerns;

	use App\Enums\Inventory\TransactionReason;
	use App\Enums\Inventory\TransactionType;
	use App\Models\Inventories\InventoryTransaction;
	use App\Models\Purchases\PurchaseOrder;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\DB;

	trait CanSeedPurchases {

		protected function seedPurchases(Collection $products, Collection $users): void {
			$this->command->info('Generating purchase transactions safely...');

			$poIds = PurchaseOrder::query()->pluck('id');

			if ($poIds->isEmpty()) {
				$this->command->warn('No Purchase Orders found.');
				return;
			}

			$randomLocations = DB::table('warehouse_locations')->select('id', 'warehouse_id')->inRandomOrder()->limit(2048)->get();

			if ($randomLocations->isEmpty()) {
				$this->command->error('No warehouse locations found! Please seed locations first.');
				return;
			}

			foreach (Collection::range(1, $randomLocations->count())->shuffle() as $location_index) {
				$product = $products->random();

				// 2. Διαλέγουμε τυχαία από τη συλλογή που είναι ήδη στη μνήμη (PHP random)
				$location = $randomLocations->random();

				$qty  = rand(32, 256);
				$cost = $product->cost_price ?? rand(10, 50);

				$this->list->push([
					'batch_number'    => InventoryTransaction::generateTransactionNumber('PUR'),
					'product_id'      => $product->id,
					'warehouse_id'    => $location->warehouse_id,
					'location_id'     => $location->id,
					'type'            => TransactionType::IN->value,
					'reason'          => TransactionReason::PURCHASE->value,
					'quantity'        => $qty,
					'quantity_before' => 0,
					'quantity_after'  => $qty,
					'unit_cost'       => $cost,
					'total_cost'      => $cost * $qty,
					'reference_type'  => PurchaseOrder::class,
					'reference_id'    => $poIds->random(),
					'created_by'      => $users->random(),
					'created_at'      => now()->subDays(mt_rand(30, 60)),
					'updated_at'      => now()->addDays(mt_rand(30, 60))->setHours(mt_rand(0, 23))->setMinutes(mt_rand(0, 59))->setSeconds(mt_rand(0, 59)),
				]);

				// Χρήση static:: για να βρει το constant του ParentSeeder
//				if ($this->list->count() >= static::BATCH_SIZE) {
//					$this->flushList();
//				}
			}

			$this->flushList();
		}

		/**
		 * Δηλώνουμε ότι το class που θα με χρησιμοποιήσει ΠΡΕΠΕΙ να έχει αυτές τις μεθόδους.
		 * Έτσι το IDE σου θα είναι χαρούμενο και ο κώδικας πιο "αυτοτελής".
		 */
		abstract public function flushList(): void;
	}