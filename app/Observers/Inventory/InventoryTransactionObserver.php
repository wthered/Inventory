<?php

	namespace App\Observers\Inventory;

	use App\Enums\Inventory\TransactionType;
	use App\Models\Inventories\Inventory;
	use App\Models\Inventories\InventoryTransaction;
	use Carbon\Carbon;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Str;
	use Throwable;

	class InventoryTransactionObserver {
		/**
		 * Handle the InventoryTransaction "creating" event.
		 * Logic for generating unique transaction numbers.
		 */
		public function creating(InventoryTransaction $transaction): void {
			// Αν δεν έχουμε ορίσει χειροκίνητα batch_number (π.χ. από κάποιο service)
			// φτιάξε ένα αυτόματο.
			if (empty($transaction->batch_number)) {
				$prefix = match($transaction->type) {
					TransactionType::ADJUSTMENT => 'ADJ',
					TransactionType::IN => 'IN',
					TransactionType::OUT => 'OUT',
					default => 'TRX'
				};

				$transaction_time = Carbon::now(config('app.timezone'))->format('Ymd');
				$transaction->batch_number = $prefix. '-' . $transaction_time . '-' . Str::upper(Str::random(6));
			}
		}

		/**
		 * Handle the InventoryTransaction "saving" event.
		 * Logic for financial math consistency.
		 */
		public function saving(InventoryTransaction $transaction): void {
			// Automatically keep total cost in sync
			if ($transaction->unit_cost && $transaction->quantity) {
				$transaction->total_cost = abs($transaction->unit_cost * $transaction->quantity);
			}
		}

		/**
		 * @throws Throwable
		 */
		public function created(InventoryTransaction $transaction): void {
			DB::transaction(function () use ($transaction) {
				// Χρήση lockForUpdate για να "κλειδώσει" την εγγραφή μέχρι να τελειώσει το transaction
				$inventory = Inventory::where([
					'product_id'   => $transaction->product_id,
					'warehouse_id' => $transaction->warehouse_id,
					'location_id'  => $transaction->location_id,
					'batch_number' => $transaction->batch_number,
				])->lockForUpdate()->first();

				if (!$inventory) {
					$inventory = Inventory::create([
						'product_id'   => $transaction->product_id,
						'warehouse_id' => $transaction->warehouse_id,
						'location_id'  => $transaction->location_id,
						'batch_number' => $transaction->batch_number,
						'quantity'     => 0
					]);
				}

				// Snapshots για το audit
				$transaction->quantity_before = $inventory->quantity;
				$transaction->quantity_after  = $inventory->quantity + $transaction->quantity;
				$transaction->saveQuietly();

				// Atomic updates
				$inventory->increment('quantity', $transaction->quantity);
				$transaction->product->increment('current_stock', $transaction->quantity);
			});
		}
	}
