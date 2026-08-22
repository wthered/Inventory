<?php

	namespace Database\Seeders\inventories\Concerns;

	use App\DataTransferObjects\ProductDTO;
	use App\Enums\Inventory\TransactionReason;
	use App\Enums\Inventory\TransactionType;
	use App\Models\Inventories\InventoryTransaction;
	use App\Models\Purchases\PurchaseOrder;
	use Carbon\Carbon;
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

			// 5 years, 7 months, and 8 days ago or less
			$transaction_time = Carbon::today()->subDays($randomLocations->count());

			foreach (Collection::range(1, $randomLocations->count())->shuffle() as $location_index) {
				$product = new ProductDTO($products->random());

				// 2. Διαλέγουμε τυχαία από τη συλλογή που είναι ήδη στη μνήμη (PHP random)
				$location = $randomLocations->random();

				$qty = mt_rand(1, $product->current_stock);
				$cost = $product->cost_price ?? mt_rand(10, 50);

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
					'created_at'      => $transaction_time,
					'updated_at'      => $transaction_time->addHours(mt_rand(1, 23))->addMinutes(mt_rand(1, 59))->addSeconds(mt_rand(1, 59)),
				]);

			}

			$this->flushList();
		}

		/**
		 * Δηλώνουμε ότι το class που θα με χρησιμοποιήσει ΠΡΕΠΕΙ να έχει αυτές τις μεθόδους.
		 * Έτσι το IDE σου θα είναι χαρούμενο και ο κώδικας πιο "αυτοτελής".
		 */
		abstract public function flushList(): void;
	}