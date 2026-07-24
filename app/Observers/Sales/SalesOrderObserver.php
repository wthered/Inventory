<?php

	namespace App\Observers\Sales;

	use App\Enums\Inventory\TransactionType;
	use App\Enums\Sales\SalesOrderStatus;
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
		 *
		 * @throws Throwable
		 */
		public function created(SalesOrder $salesOrder): void {
			// If the initial order status should immediately deduct stock (e.g., auto-confirmed)
			if ($salesOrder->status_id->shouldAffectStock()) {
				// Eager load relationships to prevent N+1 queries during loops
				$salesOrder->loadMissing('items.product');

				foreach ($salesOrder->items as $item) {
					$this->movementService->handleItemMovement($item, TransactionType::OUT->value);
				}
			}
		}

		/**
		 * Handle the SalesOrder "updating" event.
		 */
		public function updating(SalesOrder $salesOrder): void {
			if ($salesOrder->relationLoaded('items')) {
				$salesOrder->grand_total = $salesOrder->items->sum(function ($item) {
					return $item->price * $item->quantity;
				});
			}
		}

		/**
		 * Handle the SalesOrder "updated" event.
		 *
		 * @throws Throwable
		 */
		public function updated(SalesOrder $salesOrder): void {
			if ($salesOrder->wasChanged('status')) {
				$oldStatus = $salesOrder->getOriginal('status');
				$newStatus = $salesOrder->status_id;

				// Load relations efficiently for stock alterations
				$salesOrder->loadMissing('items.product');

				// Σενάριο Α: Από Draft -> Confirmed (Μείωση Stock)
				if (!$oldStatus->shouldAffectStock() && $newStatus->shouldAffectStock()) {
					foreach ($salesOrder->items as $item) {
						$this->movementService->handleItemMovement($item, TransactionType::OUT->value);
					}
				}

				// Σενάριο Β: Από Confirmed -> Cancelled (Επαναφορά Stock)
				if ($oldStatus->shouldAffectStock() && $newStatus === SalesOrderStatus::CANCELLED) {
					foreach ($salesOrder->items as $item) {
						$this->movementService->handleItemMovement($item, TransactionType::IN->value);
					}
				}
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