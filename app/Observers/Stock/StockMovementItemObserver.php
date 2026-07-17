<?php

	namespace App\Observers\Stock;

	use App\Enums\Inventory\TransactionReason;
	use App\Enums\Inventory\TransactionType;
	use App\Enums\Inventory\TransferStatus;
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
		/**
		 * Όταν δημιουργείται μια νέα γραμμή (π.χ. προσθήκη προϊόντος σε μεταφορά)
		 *
		 * @throws Exception|Throwable
		 */
		public function created(Model $item): void {
			if ($item instanceof StockTransferItem) {
				// ONLY execute the OUT movement from the source warehouse.
				// The items are now leaving Warehouse A.
				$this->movementService->execute(
					productId: $item->product_id,
					quantity: $item->quantity_requested,
					type: TransactionType::OUT->value,
					reason: 'transfer_out',
					warehouseId: $item->movement->source_warehouse_id,
					locationId: $item->source_location_id,
					batchNumber: $item->batch_number,
					reference: $item
				);

				// DO NOT put the IN movement here.
				// Warehouse B hasn't seen the items yet!
			}
		}

		/**
		 * Βρίσκει αν το παραστατικό αυξάνει ή μειώνει το stock.
		 */
		protected function determineTransactionType(Model $item): string {
			return match (true) {
				$item instanceof StockAdjustmentItem => ($item->quantity >= 0) ? TransactionType::IN->value : TransactionType::OUT->value,
				$item instanceof SalesOrderItem      => TransactionType::OUT->value,
				$item instanceof PurchaseOrderItem   => TransactionType::IN->value,
				$item instanceof StockReturnItem     => TransactionType::RETURN->value,
				default => throw new InvalidArgumentException("Unknown stock movement for model: " . get_class($item)),
			};
		}

		/**
		 * Όταν αλλάζει κάτι στη γραμμή (αλλαγή ποσότητας ή ράφι)
		 * @throws Exception|Throwable
		 */
		public function updated(Model $item): void {
			if (!$this->shouldProcess($item)) return;

			// 1. Ειδικός χειρισμός αν πρόκειται για StockTransferItem
			if ($item instanceof StockTransferItem) {
				$diff = $item->quantity_requested - $item->getOriginal('quantity_requested');

				if ($diff === 0) return; // Δεν άλλαξε η ποσότητα

				// Αν αυξήθηκε η αιτούμενη ποσότητα, πρέπει να βγάλουμε κι άλλο stock από την πηγή (OUT).
				// Αν μειώθηκε, πρέπει να επιστρέψουμε το stock στην πηγή (IN).
				$type = $diff > 0 ? TransactionType::OUT->value : TransactionType::IN->value;

				$this->movementService->execute(
					productId: $item->product_id,
					quantity: abs($diff), // Πάντα θετικός αριθμός για την execute
					type: $type,
					reason: TransactionReason::TRANSFER_OUT->value, // Χρήση του σωστού Enum value
					warehouseId: $item->movement->source_warehouse_id,
					locationId: $item->source_location_id,
					batchNumber: $item->batch_number,
					reference: $item
				);

				return;
			}

			// 2. Generic χειρισμός για τα υπόλοιπα μοντέλα (π.χ. Adjustments, Returns)
			$diff = $item->quantity - $item->getOriginal('quantity');
			if ($diff !== 0) {
				$type = $this->determineTransactionType($item);
				if ($diff < 0) {
					$type = ($type === TransactionType::IN->value) ? TransactionType::OUT->value : TransactionType::IN->value;
				}

				$this->movementService->execute(
					productId: $item->product_id,
					quantity: abs($diff),
					type: $type,
					reason: TransactionReason::DATA_ENTRY->value,
					warehouseId: $this->movementService->resolveWarehouseId($item),
					locationId: $item->getOriginal('location_id'),
					reference: $item
				);

				// New
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

			if ($item instanceof StockTransferItem) {
				// If it was still pending or canceled, we only roll back Warehouse A's OUT movement
				// Reverse Source OUT -> IN
				$this->movementService->execute(
					productId: $item->product_id,
					quantity: $item->quantity_requested,
					type: TransactionType::IN->value,
					reason: 'transfer_reversal',
					warehouseId: $item->movement->source_warehouse_id,
					locationId: $item->source_location_id,
					reference: $item
				);

				// REMOVE the target warehouse reversal from here because stock was never added there yet!
				return;
			}

			$type        = $this->determineTransactionType($item);
			$reverseType = ($type === TransactionType::IN->value) ? TransactionType::OUT->value : TransactionType::IN->value;

			$this->movementService->handleItemMovement($item, $reverseType);
		}

		protected function shouldProcess(Model $item): bool {
			if ($item instanceof SalesOrderItem) {
				return $item->salesOrder->status->shouldAffectStock();
			}
			return true;
		}
	}
