<?php

	namespace App\Observers\Sales;

	use App\Enums\Inventory\TransactionType;
	use App\Enums\Sales\SalesOrderStatus;
	use App\Models\Inventories\Inventory;
	use App\Models\Product;
	use App\Models\Sales\SalesOrder;
	use App\Services\Inventory\StockMovementService;
	use Throwable;

	class SalesOrderObserver {
		protected StockMovementService $movementService;

		public function __construct(StockMovementService $movementService) {
			$this->movementService = $movementService;
		}

		/**
		 * Handle the SalesOrder "created" event.
		 * Create Flow: saving -> creating -> created -> saved
		 *
		 * @throws Throwable
		 */
		public function created(SalesOrder $salesOrder): void {
			// If the initial order status should immediately deduct stock (e.g., auto-confirmed)
			if ($salesOrder->status_id->shouldAffectStock()) {
				// Eager load relationships to prevent N+1 queries during loops
				$salesOrder->loadMissing('items.product');

				foreach ($salesOrder->items as $item) {
					dump("[".__FILE__."::".__LINE__."] SaleOrder Item: ".$item);
					$this->movementService->handleItemMovement($item, TransactionType::OUT->value);
				}
			}
		}

		/**
		 * Handle the SalesOrder "updating" event.
		 *  Update Flow: saving -> updating -> updated -> saved
		 */
		public function updating(SalesOrder $salesOrder): void {
			if ($salesOrder->relationLoaded('items')) {
				$salesOrder->grand_total = $salesOrder->items->sum(function ($item) {
//					dd($item);
					return $item->unit_price * $item->quantity_ordered;
				});
			}
		}

		/**
		 * Handle the SalesOrder "updated" event.
		 *  Update Flow: saving -> updating -> updated -> saved
		 *
		 * @throws Throwable
		 */
		public function updated(SalesOrder $salesOrder): void {
//			dd(__METHOD__);
			if ($salesOrder->wasChanged('status_id')) {
				$oldStatus = $salesOrder->getOriginal('status_id');

				// If Enum casting didn't auto-cast the original raw value:
				if (is_numeric($oldStatus) || is_string($oldStatus)) {
					$oldStatus = SalesOrderStatus::from((int) $oldStatus);
				}

				$newStatus = $salesOrder->status_id;

				// Load relations efficiently for stock alterations
				$salesOrder->loadMissing('items.product');

				// Scenario A: From Draft -> Confirmed (Stock OUT)
				if ($oldStatus && !$oldStatus->shouldAffectStock()) {
//					dd($salesOrder->items);
					foreach ($salesOrder->items as $item) {
//						dd($item);
						$this->movementService->handleItemMovement($item, TransactionType::OUT->value);
					}
				}

				// Scenario B: From Confirmed -> Canceled (Stock IN)
				if ($oldStatus && $oldStatus->shouldAffectStock() && $newStatus === SalesOrderStatus::CANCELLED) {
					foreach ($salesOrder->items as $item) {
						$this->movementService->handleItemMovement($item, TransactionType::IN->value);
					}
				}
			}
		}

		/**
		 * Handle the SalesOrder "saved" event.
		 * Runs automatically after both "created" and "updated" events.
		 */
		public function saved(SalesOrder $salesOrder): void {
			// Ensure items relationship is loaded
			$salesOrder->loadMissing('items');

			foreach ($salesOrder->items as $item) {
				if (!$item->product_id) {
					continue;
				}

				// Calculate total physical stock across all warehouses/locations
				$inventory = Inventory::query()->where('product_id', $item->product_id);

				// SalesOrderSeeder creates inconsistencies between inventories.warehouse_id and sales_order.warehouse_id
				// Deleted because created infinite loop and server crashed by too many database updates
//				$salesOrder->update([
//					'warehouse_id' => $inventory->pluck('warehouse_id')->random(),
//				]);

				// Update current_stock on the product
				Product::query()->where('id', $item->product_id)->update([
					'current_stock' => $inventory->sum('quantity'),
				]);
			}
		}

		/**
		 * Handle the SalesOrder "deleting" event.
		 *
		 * @throws Throwable
		 */
		public function deleting(SalesOrder $salesOrder): void {
			$salesOrder->loadMissing('items.product');

			foreach ($salesOrder->items as $item) {
				// Only restore stock if the current status actually holds inventory
				// and the order wasn't already canceled (prevents double increments)
				if ($salesOrder->status->shouldAffectStock() && $salesOrder->status !== SalesOrderStatus::CANCELLED) {
					$this->movementService->handleItemMovement($item, TransactionType::IN->value);
				}

				// Safe cascading delete on the order item
				$item->delete();
			}
		}
	}
