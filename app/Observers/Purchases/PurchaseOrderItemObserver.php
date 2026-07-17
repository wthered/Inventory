<?php

	namespace App\Observers\Purchases;

	use App\Models\Products\ProductHistory;
	use App\Models\Purchases\PurchaseOrderItem;
	use Exception;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Facades\Log;
	use Throwable;

	class PurchaseOrderItemObserver {

		public function created(PurchaseOrderItem $item): void {
			Log::info('Purchase order item created', [
				'purchase_order_id' => $item->purchase_order_id,
				'product_id'        => $item->product_id,
				'quantity'          => $item->quantity_ordered,
			]);
		}

		public function updated(PurchaseOrderItem $item): void {
			if ($item->isDirty('quantity_received')) {
				$this->handleStockUpdate($item);
			}
			$this->logFieldChanges($item);
		}

		/**
		 * @throws Throwable
		 */
		private function handleStockUpdate(PurchaseOrderItem $item): void {
			$receivedDelta = $item->quantity_received - $item->getOriginal('quantity_received');

			if ($receivedDelta <= 0) {
				return;
			}

			$totalReceived = $item->quantity_received;
			$ordered       = $item->quantity_ordered;

			if ($totalReceived > $ordered) {
				Log::warning('Over-receiving detected', [
					'purchase_order_item_id' => $item->id,
					'ordered'                => $ordered,
					'received'               => $totalReceived,
				]);
			}

			$product = $item->product;
			if (!$product) return;

			try {
				DB::transaction(function () use ($product, $receivedDelta, $item) {
					$oldStock = $product->stock;
					$product->increment('stock', $receivedDelta);
					$product->refresh();

					ProductHistory::query()->create([
						'product_id' => $product->id,
						'user_id'    => Auth::id() ?? 1,
						'action'     => 'stock_received',
						'details'    => [
							'quantity_received' => $receivedDelta,
							'old_stock'         => $oldStock,
							'new_stock'         => $product->stock,
							'source'            => 'Purchase Order',
							'purchase_order_id' => $item->purchase_order_id,
							'purchase_item_id'  => $item->id,
							'unit_cost'         => $item->unit_price,
							'total_cost'        => $receivedDelta * $item->unit_price,
						],
					]);
				});
			} catch (Exception|Throwable $e) {
				Log::error('Failed to update stock: ' . $e->getMessage());
				throw $e;
			}
		}

		private function logFieldChanges(PurchaseOrderItem $item): void {
			$monitoredFields = ['unit_price', 'quantity_ordered', 'notes'];

			foreach ($monitoredFields as $field) {
				if ($item->isDirty($field)) {
					Log::info("Purchase order item {$field} updated", [
						'purchase_order_item_id' => $item->id,
						'old_value'              => $item->getOriginal($field),
						'new_value'              => $item->$field,
					]);
				}
			}
		}

		public function deleted(PurchaseOrderItem $item): void {
			Log::warning('Purchase order item deleted', ['id' => $item->id]);
		}

		public function forceDeleted(PurchaseOrderItem $item): void {
			Log::critical('Purchase order item force deleted', ['id' => $item->id]);
		}

		public function restored(PurchaseOrderItem $item): void {
			Log::info('Purchase order item restored', ['id' => $item->id]);
		}

		public function creating(PurchaseOrderItem $item): void {
			if ($item->quantity_received > $item->quantity_ordered) {
				Log::warning('Created with over-received quantity');
			}
		}
	}