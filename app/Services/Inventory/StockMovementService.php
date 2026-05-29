<?php

	namespace App\Services\Inventory;

	use App\Contracts\StockMoveable;
	use App\Enums\Inventory\TransactionReason;
	use App\Enums\Inventory\TransactionType;
	use App\Models\{Product, StockAdjustmentItem, StockReturnItem, StockTransferItem};
	use App\Models\Inventories\{Inventory, InventoryAudit, InventoryTransaction};
	use Exception;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Support\Facades\{Auth, DB, Log};
	use Throwable;

	class StockMovementService {
		/**
		 * Η κεντρική μέθοδος που καλεί ο Observer.
		 * Διαχειρίζεται το transaction wrap και το error logging.
		 *
		 * @throws Exception|Throwable
		 */
		public function handleItemMovement(Model $item, string $type): ?InventoryTransaction {
			// Αν δεν υπάρχει ποσότητα, δεν υπάρχει λόγος να γίνει κίνηση
			if (empty($item->quantity)) {
				Log::info("Skipping stock movement for " . class_basename($item) . " ID: ".$item->id." due to zero/null quantity.");
				return null;
			}
			$warehouseId = $this->resolveWarehouseId($item);
			$reason      = $this->guessReason($item);

			try {
				return $this->execute(productId: $item->product_id, quantity: $item->quantity, type: $type, reason: $reason, warehouseId: $warehouseId, locationId: $item->location_id, reference: $item);
			} catch (Exception $e) {
				// Αν το transaction αποτύχει λόγω αποθέματος, καταγράφουμε το Audit.
				// Το κάνουμε ΕΚΤΟΣ του κύριου transaction που μόλις έκανε rollback.
				if ($e->getMessage() === "Ανεπαρκές απόθεμα στο συγκεκριμένο ράφι.") {
					$this->logFailedMovement($item->product_id, $warehouseId, $item->location_id, $item->quantity, $this->getAvailableStock($item), $item);
				}
				throw $e;
			}
		}

		/**
		 * Προσδιορίζει το Warehouse ID από το Item ή το Header του.
		 *
		 * @throws Exception
		 */
		public function resolveWarehouseId(Model $item): int {
			try {
				// 1. Προσπάθεια μέσω Interface (Η πιο σωστή οδός)
				if ($item instanceof StockMoveable) {
					return $item->getWarehouseId();
				}

				// 2. Προσπάθεια μέσω property ή σχέσης (Fallback)
				$warehouseId = $item->warehouse_id ?? $item->warehouse?->id;

				if (!$warehouseId) {
					throw new Exception("Warehouse ID not found on model properties.");
				}

				return $warehouseId;

			} catch (Throwable $e) {
				// Καταγράφουμε το σφάλμα για να ξέρουμε ποιο Model "έσκασε"
				Log::error("Warehouse Resolution Failed: " . class_basename($item), [
					'id' => $item->id ?? 'N/A',
					'error' => $e->getMessage()
				]);

				throw new Exception("Could not resolve Warehouse ID for " . class_basename($item));
			}
		}

		/**
		 * "Μαντεύει" την αιτία της κίνησης βάσει του Model.
		 */
		protected function guessReason(Model $item): string {
			return match (true) {
				$item instanceof StockReturnItem => TransactionReason::RETURNED->value,
				$item instanceof StockAdjustmentItem => TransactionReason::STOCKTAKE->value,
				isset($item->reason) => $item->reason,
				default => TransactionReason::OTHER->value
			};
		}

		/**
		 * Η βασική μέθοδος εκτέλεσης (Core Logic).
		 *
		 * @throws Throwable
		 */
		public function execute(int $productId, int $quantity, string $type, string $reason, int $warehouseId, ?int $locationId = null, ?Model $reference = null): InventoryTransaction {
			return DB::transaction(function () use ($productId, $quantity, $type, $reason, $warehouseId, $locationId, $reference) {

				// 1. Pessimistic Locking για αποφυγή Race Conditions
				$inventory = Inventory::query()->where('product_id', $productId)
					             ->where('warehouse_id', $warehouseId)
					             ->where('location_id', $locationId)
					             ->lockForUpdate()
					             ->first() ?? Inventory::create([
					'product_id'   => $productId,
					'warehouse_id' => $warehouseId,
					'location_id'  => $locationId,
					'quantity'     => 0
				]);

				$quantityBefore  = $inventory->quantity;
				$transactionType = TransactionType::from($type);
				$sign            = $transactionType->sign();
				$quantityChange  = $quantity * $sign;

				// 2. Validation Αποθέματος
				if ($sign === -1 && $quantityBefore < $quantity) {
					throw new Exception("Ανεπαρκές απόθεμα στο συγκεκριμένο ράφι.");
				}

				// 3. Atomic Updates
				$inventory->increment('quantity', $quantityChange);

				// Ενημερώνουμε το Product Global Stock απευθείας (πιο γρήγορο από SUM)
				Product::where('id', $productId)->increment('current_stock', $quantityChange);

				// 4. Δημιουργία Ledger Entry
				return InventoryTransaction::create([
					'product_id'      => $productId,
					'warehouse_id'    => $warehouseId,
					'location_id'     => $locationId,
					'type'            => $type,
					'reason'          => $reason,
					'quantity'        => $quantity,
					'quantity_before' => $quantityBefore,
					'quantity_after'  => $quantityBefore + $quantityChange,
					'reference_id'    => $reference?->id,
					'reference_type'  => $reference?->getMorphClass(),
					'created_by'      => Auth::id() ?? 1,
				]);
			});
		}

		/**
		 * Καταγραφή αποτυχίας στο Audit Trail.
		 */
		protected function logFailedMovement($productId, $warehouseId, $locationId, $requested, $available, $reference): void {
			InventoryAudit::query()->create([
				'product_id'         => $productId,
				'warehouse_id'       => $warehouseId,
				'location_id'        => $locationId,
				'action'             => 'STOCK_OUT_ATTEMPT',
				'status'             => 'FAILED',
				'requested_quantity' => $requested,
				'before_quantity'    => $available,
				'error_message'      => "Insufficient Stock: Required ".$requested.", but only ".$available." available.",
				'auditable_type'     => $reference ? $reference->getMorphClass() : null,
				'auditable_id'       => $reference?->id,
				'user_id'            => Auth::id() ?? 1,
			]);

			Log::warning("Inventory Shortage: Product ".$productId." at Location ".$locationId." in Warehouse ".$warehouseId);
		}

		/**
		 * Επιστρέφει το τρέχον διαθέσιμο απόθεμα (Read-only).
		 *
		 * @throws Exception
		 */
		public function getAvailableStock(Model $item): int {
			return Inventory::where('product_id', $item->product_id)
				       ->where('warehouse_id', $this->resolveWarehouseId($item))
				       ->where('location_id', $item->location_id)
				       ->value('quantity') ?? 0;
		}
	}