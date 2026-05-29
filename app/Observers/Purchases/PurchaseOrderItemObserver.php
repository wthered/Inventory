<?php

	namespace App\Observers\Purchases;

	use App\Models\Products\ProductHistory;
	use App\Models\Purchases\PurchaseOrderItem;
	use Exception;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Facades\Log;

	class PurchaseOrderItemObserver {

		/**
		 * Handle the PurchaseOrderItem "created" event.
		 */
		public function created(PurchaseOrderItem $item): void {
			// Log the initial creation of purchase order item
			Log::info('Purchase order item created', [
				'purchase_order_id' => $item->purchase_order_id,
				'product_id'        => $item->product_id,
				'quantity'          => $item->quantity,
			]);
		}

		/**
		 * Χειρίζεται τα συμβάντα 'updated' για το PurchaseOrderItem.
		 * Αυτό είναι κρίσιμο για την ενημέρωση του αποθέματος.
		 *
		 * @throws Exception
		 */
		public function updated(PurchaseOrderItem $item): void {
			// Η ενέργεια 'stock_received' πυροδοτείται όταν αλλάζει το πεδίο 'received_quantity'.
			if ($item->isDirty('received_quantity')) {
				$this->handleStockUpdate($item);
			}

			// Log other important field changes
			$this->logFieldChanges($item);
		}

		/**
		 * @throws Exception
		 */
		private function handleStockUpdate(PurchaseOrderItem $item): void {
			$receivedDelta = $item->received_quantity - $item->getOriginal('received_quantity');

			// Only process positive deltas (actual stock receipts)
			if ($receivedDelta <= 0) {
				return;
			}

			// Optional: Prevent receiving more than ordered
			$totalReceived = $item->received_quantity;
			$ordered       = $item->quantity;

			if ($totalReceived > $ordered) {
				Log::warning('Over-receiving detected', [
					'purchase_order_item_id' => $item->id,
					'ordered'                => $ordered,
					'received'               => $totalReceived,
					'delta'                  => $receivedDelta,
				]);
				// You could throw an exception here if over-receiving is not allowed
				// throw new Exception("Cannot receive more than ordered quantity");
			}

			$product = $item->product;

			if (!$product) {
				Log::error('Product not found for purchase order item', [
					'purchase_order_item_id' => $item->id,
					'product_id'             => $item->product_id,
				]);
				return;
			}

			try {
				DB::transaction(function () use ($product, $receivedDelta, $item) {
					// Get current stock before update for accurate history
					$oldStock = $product->stock;

					// Update stock - this won't trigger ProductObserver due to direct SQL
					$product->increment('stock', $receivedDelta);

					// Refresh to get the updated stock value
					$product->refresh();

					// Log to product history
					ProductHistory::query()
						->create([
							'product_id' => $product->id,
							'user_id'    => Auth::id() ?? 1,
							// Fallback for system actions
							'action'     => 'stock_received',
							'details'    => [
								'quantity_received' => $receivedDelta,
								'old_stock'         => $oldStock,
								'new_stock'         => $product->stock,
								'source'            => 'Purchase Order',
								'purchase_order_id' => $item->purchase_order_id,
								'purchase_item_id'  => $item->id,
								'unit_cost'         => $item->unit_cost,
								'total_cost'        => $receivedDelta * $item->unit_cost,
							],
						]);

					Log::info('Stock updated from purchase order', [
						'product_id'        => $product->id,
						'received_delta'    => $receivedDelta,
						'purchase_order_id' => $item->purchase_order_id,
					]);
				});

			} catch (Exception $e) {
				Log::error('Failed to update stock from purchase order', [
					'purchase_order_item_id' => $item->id,
					'product_id'             => $product->id,
					'error'                  => $e->getMessage(),
				]);

				// Re-throw to maintain data integrity
				throw $e;
			}
		}

		/**
		 * Log changes to other important fields
		 */
		private function logFieldChanges(PurchaseOrderItem $item): void {
			$monitoredFields = [
				'unit_cost',
				'quantity',
				'notes',
			];

			foreach ($monitoredFields as $field) {
				if ($item->isDirty($field)) {
					Log::info("Purchase order item {$field} updated", [
						'purchase_order_item_id' => $item->id,
						'field'                  => $field,
						'old_value'              => $item->getOriginal($field),
						'new_value'              => $item->$field,
						'purchase_order_id'      => $item->purchase_order_id,
					]);
				}
			}
		}

		/**
		 * Handle the PurchaseOrderItem "deleted" event.
		 */
		public function deleted(PurchaseOrderItem $item): void {
			// Log deletion but don't adjust stock (items shouldn't be deleted after receipt)
			Log::warning('Purchase order item deleted', [
				'purchase_order_item_id' => $item->id,
				'purchase_order_id'      => $item->purchase_order_id,
				'product_id'             => $item->product_id,
				'received_quantity'      => $item->received_quantity,
			]);
		}

		/**
		 * Handle the PurchaseOrderItem "force deleted" event.
		 */
		public function forceDeleted(PurchaseOrderItem $item): void {
			Log::critical('Purchase order item force deleted', [
				'purchase_order_item_id' => $item->id,
				'purchase_order_id'      => $item->purchase_order_id,
				'product_id'             => $item->product_id,
			]);
		}

		/**
		 * Handle the PurchaseOrderItem "restored" event.
		 */
		public function restored(PurchaseOrderItem $item): void {
			Log::info('Purchase order item restored', [
				'purchase_order_item_id' => $item->id,
				'purchase_order_id'      => $item->purchase_order_id,
			]);
		}

		/**
		 * Handle the PurchaseOrderItem "creating" event.
		 */
		public function creating(PurchaseOrderItem $item): void {
			// Ensure received_quantity doesn't exceed ordered quantity
			if ($item->received_quantity > $item->quantity) {
				Log::warning('Purchase order item created with over-received quantity', [
					'ordered'  => $item->quantity,
					'received' => $item->received_quantity,
				]);
			}
		}

	}
