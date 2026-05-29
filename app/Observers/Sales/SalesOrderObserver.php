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
		 * Handle the SalesOrder "updated" event.
		 *
		 * @throws Throwable
		 */
		public function updated(SalesOrder $salesOrder): void {
			// Ελέγχουμε αν άλλαξε το status
			if ($salesOrder->wasChanged('status')) {
				$oldStatus = $salesOrder->getOriginal('status');
				$newStatus = $salesOrder->status;

				// Σενάριο Α: Από Draft -> Confirmed (Μείωση Stock)
				if (!$oldStatus->shouldAffectStock() && $newStatus->shouldAffectStock()) {
					foreach ($salesOrder->items as $item) {
						$this->movementService->handleItemMovement($item, TransactionType::OUT->value);
					}
				}

				// Σενάριο Β: Από Confirmed -> Cancelled (Επαναφορά Stock)
				if ($oldStatus->shouldAffectStock() && $newStatus === SalesOrderStatus::CANCELLED) {
					foreach ($salesOrder->items as $item) {
						$this->movementService->handleItemMovement($item, 'in');
					}
				}
			}
		}
	}
