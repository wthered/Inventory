<?php

	namespace App\Observers\Stock;

	use App\Enums\Inventory\TransactionType;
	use App\Models\{Purchases\PurchaseOrderItem, Sales\SalesOrderItem, StockAdjustmentItem, StockReturnItem, StockTransferItem};
	use App\Services\Inventory\StockMovementService;
	use Exception;
	use Illuminate\Database\Eloquent\Model;
	use InvalidArgumentException;
	use Throwable;

	class StockMovementItemObserver {

		private StockMovementService $movementService;

		public function __construct(StockMovementService $stockService) {
			$this->movementService = $stockService;
		}

		/**
		 * Όταν δημιουργείται μια νέα γραμμή (π.χ. προσθήκη προϊόντος σε επιστροφή)
		 *
		 * @throws Exception|Throwable
		 */
		public function created(Model $item): void {
			if (!$this->shouldProcess($item)) return;
			$type = $this->determineTransactionType($item);
			$this->movementService->handleItemMovement($item, $type);
		}

		/**
		 * Βρίσκει αν το παραστατικό αυξάνει ή μειώνει το stock.
		 */
		protected function determineTransactionType(Model $item): string {
			return match (true) {
				$item instanceof StockAdjustmentItem => ($item->quantity >= 0) ? TransactionType::IN->value : TransactionType::OUT->value,

				$item instanceof SalesOrderItem, $item instanceof StockTransferItem => TransactionType::OUT->value,

				// Πρόσθεσε PurchaseOrderItem αν υπάρχει
				 $item instanceof PurchaseOrderItem => TransactionType::IN->value,

				// Πρόσθεσε StockReturnItem αν υπάρχει
				$item instanceof StockReturnItem => TransactionType::RETURN->value,

				default => throw new InvalidArgumentException("Unknown stock movement for model: " . get_class($item)),
			};
		}

		/**
		 * Όταν αλλάζει κάτι στη γραμμή (ποσότητα ή ράφι)
		 *
		 * @throws Exception
		 * @throws Throwable
		 */
		public function updated(Model $item): void {
			// ΚΡΙΣΙΜΟ: Αν η παραγγελία είναι Draft, μην πειράξεις το stock!
			if (!$this->shouldProcess($item)) {
				return;
			}

			if ($item->wasChanged(['quantity', 'location_id', 'product_id'])) {
				// 1. Αντιστρέφουμε την ΠΑΛΙΑ κίνηση (Rollback παλιού stock)
				$oldItem              = clone $item;
				$oldItem->quantity    = $item->getOriginal('quantity');
				$oldItem->location_id = $item->getOriginal('location_id');
				$oldItem->product_id  = $item->getOriginal('product_id');

				$type        = $this->determineTransactionType($item);
				$reverseType = ($type === TransactionType::IN->value) ? TransactionType::OUT->value : TransactionType::IN->value;

				// Αναιρούμε το παλιό
				$this->movementService->execute(productId: $oldItem->product_id, quantity: $oldItem->quantity, type: $reverseType, reason: 'adjustment_correction', warehouseId: $this->movementService->resolveWarehouseId($item), locationId: $oldItem->location_id, reference: $item);

				// 2. Εκτελούμε τη ΝΕΑ κίνηση
				$this->movementService->handleItemMovement($item, $type);
			}
		}

		/**
		 * Όταν διαγράφεται μια γραμμή, πρέπει να επιστρέψουμε το stock εκεί που ήταν
		 *
		 * @throws Exception|Throwable
		 */
		public function deleted(Model $item): void {
			if (!$this->shouldProcess($item)) return;
			$type        = $this->determineTransactionType($item);

			$reverseType = ($type === TransactionType::IN->value) ? TransactionType::OUT->value : TransactionType::IN->value;

			$this->movementService->handleItemMovement($item, $reverseType);
		}

		protected function shouldProcess(Model $item): bool {
			// Αν είναι SalesOrderItem, δες το status του SalesOrder
			if ($item instanceof SalesOrderItem) {
				return $item->salesOrder->status->shouldAffectStock();
			}

			// Για StockAdjustment ή άλλα, ίσως θέλεις να περνάνε πάντα
			return true;
		}
	}
