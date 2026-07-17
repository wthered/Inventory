<?php

	namespace App\Traits\Stock;

	use App\Enums\Inventory\StockReturnStatus;
	use Carbon\Carbon;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\DB;
	use Throwable;

	trait HandlesInventoryRestocking {
		/**
		 * Professional Restocking Logic for all items in the Return.
		 *
		 * @throws Throwable
		 */
		public function restockInventory(): bool {
			if ($this->restocked_at || $this->status === StockReturnStatus::COMPLETED) {
				return false;
			}

			return DB::transaction(function () {
				$this->loadMissing('items');

				foreach ($this->items as $item) {
					if (!$item->is_restockable || $item->quantity <= 0 || $item->restocked_at) {
						continue;
					}

					$this->inventoryTransactions()->create([
						'product_id'   => $item->product_id,
						'warehouse_id' => $this->warehouse_id,
						'location_id'  => $item->location_id ?? $this->location_id,
						'type'         => 'in',
						'reason'       => 'return',
						'quantity'     => $item->quantity,
						'notes'        => "Restock item from Return #".$this->return_number,
						'created_by'   => Auth::id(),
					]);

					$item->update([
						'restocked_at' => Carbon::now()->toDateTimeString()
					]);
				}

				return $this->update([
					'restocked_at' => Carbon::now()->toDateTimeString(),
					'status'       => StockReturnStatus::COMPLETED
				]);
			});
		}
	}
