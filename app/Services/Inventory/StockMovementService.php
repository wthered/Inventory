<?php

	namespace App\Services\Inventory;

	use App\Contracts\StockMoveable;
	use App\Enums\Inventory\TransactionReason;
	use App\Enums\Inventory\TransactionType;
	use App\Enums\Inventory\TransferStatus;
	use App\Exceptions\Inventory\InsufficientStockException;
	use Illuminate\Http\JsonResponse;
	use App\Models\{Product, StockAdjustmentItem, StockReturnItem, StockTransfer, User, WarehouseLocation};
	use App\Models\Inventories\{Inventory, InventoryMovementLog, InventoryTransaction};
	use Exception;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\QueryException;
	use Illuminate\Support\Facades\{Auth, DB, Log};
	use Throwable;

	class StockMovementService {

		/**
		 * Η κεντρική μέθοδος που καλεί ο Observer.
		 *
		 * @throws Exception|Throwable
		 */
		public function handleItemMovement(Model $item, string $type): ?InventoryTransaction {
			if (empty($item->quantity)) {
				Log::info("Skipping stock movement for " . class_basename($item) . " ID: " . $item->id . " due to zero/null quantity.");
				return null;
			}

			$warehouseId = $this->resolveWarehouseId($item);
			$reason      = $this->guessReason($item);

			try {
				return $this->execute(productId: $item->product_id, quantity: $item->quantity, type: $type, reason: $reason, warehouseId: $warehouseId, locationId: $item->location_id, reference: $item);

			} catch (InsufficientStockException $e) {
				// Πιάστηκε το custom exception ελλιπούς αποθέματος.
				// Καταγράφουμε στο ανεξάρτητο Log, εκτός του transaction που έκανε rollback.
				$this->logFailedMovement(productId: $item->product_id, warehouseId: $warehouseId, locationId: $item->location_id, requested: $item->quantity, available: $this->getAvailableStock($item), reference: $item, message: $e->getMessage());

				throw $e; // Re-throw για να ενημερωθεί το UI / Controller
			}
		}

		public function resolveWarehouseId(Model $item): int {
			if ($item instanceof StockMoveable) {
				return $item->getWarehouseId();
			}
			$warehouseId = $item->warehouse_id ?? $item->warehouse?->id;
			if (!$warehouseId) {
				throw new Exception("Warehouse ID not found on model properties.");
			}
			return $warehouseId;
		}

		protected function guessReason(Model $item): string {
			return match (true) {
				$item instanceof StockReturnItem => TransactionReason::RETURNED->value,
				$item instanceof StockAdjustmentItem => TransactionReason::STOCKTAKE->value,
				isset($item->reason) => $item->reason,
				default => TransactionReason::OTHER->value
			};
		}

		/**
		 * Core Logic με Pessimistic Locking και ασφαλή updates.
		 *
		 * @throws Throwable
		 */
		public function execute(int $productId, int $quantity, string $type, string $reason, int $warehouseId, ?int $locationId = null, ?string $batchNumber = null, ?Model $reference = null): InventoryTransaction {
			return DB::transaction(function () use ($productId, $quantity, $type, $reason, $warehouseId, $locationId, $batchNumber, $reference) {

				if (is_null($locationId)) {
					$locationId = WarehouseLocation::where('warehouse_id', $warehouseId)->value('id') ?? throw new Exception("No valid location found.");
				}

				// Include batch number in unique constraint
				$criteria = [
					'product_id'   => $productId,
					'warehouse_id' => $warehouseId,
					'location_id'  => $locationId,
				];

				// Only add batch if we're tracking it
				if ($batchNumber !== null) {
					$criteria['batch_number'] = $batchNumber;
				}

				$inventory = Inventory::where($criteria)->lockForUpdate()->first();

				if (!$inventory) {
					try {
						$inventory = Inventory::query()->create(array_merge($criteria, [
							'quantity'           => 0,
							'reserved_quantity'  => 0,
							'batch_number'       => $batchNumber ?? null,
							'manufacturing_date' => today(),
							'expiry_date'        => today()->addDays(mt_rand(32, 96))->format('Y-m-d'),
						]));

						// Lock it immediately after creation to be safe
						$inventory->lockForUpdate();
					} catch (QueryException $e) {
						// If another thread created it at the exact same microsecond,
						// catch the duplicate error and fetch it with a lock.
						$inventory = Inventory::where($criteria)->lockForUpdate()->firstOrFail();
						// Example tracking an error via the inventory model instance
						$inventory->movementLogs()->create([
							'action'             => 'STOCK_OUT_ATTEMPT',
							'status'             => 'failed',
							'requested_quantity' => $quantity,
							'before_quantity'    => $inventory->quantity,
							'error_message'      => $e->getMessage(),
							'user_id'            => Auth::id() ?? User::where('email', 'wthered@gmail.com')->value('id'),
						]);
					}
				}

				$quantityAvailable = $inventory->quantity;
				$sign              = TransactionType::from($type)->sign(); // -1 ή 1

				// 4. Έλεγχος Αποθέματος με ρίψη του Custom Exception
				if ($sign === -1 && $quantityAvailable < $quantity) {
					$message = "Ανεπαρκές απόθεμα στο συγκεκριμένο ράφι. Διαθέσιμο: " . $quantityAvailable . ", Ζητήθηκε: " . $quantity;
					throw new InsufficientStockException($message);
				}

				// 5. Atomic Updates βασισμένα στο πρόσημο
				if ($sign === -1) {
					$inventory->decrement('quantity', $quantity);
				} else {
					$inventory->increment('quantity', $quantity);
				}
				// Update the product inventory count
				$totalStock = Inventory::query()->where('product_id', $productId)->sum('quantity');
				Product::query()->findOrFail($productId)->update(['current_stock' => $totalStock]);

				// 6. Δημιουργία Ledger Entry με σωστά δεδομένα
				$unitCost = $inventory->product->cost_price;
				return InventoryTransaction::query()->create([
					'product_id'      => $productId,
					'warehouse_id'    => $warehouseId,
					'location_id'     => $inventory->location_id,
					'type'            => $type,
					'reason'          => $reason,
					'quantity'        => $quantity,
					'quantity_before' => $quantityAvailable,
					'quantity_after'  => $sign === -1 ? $quantityAvailable - $quantity : $quantityAvailable + $quantity,
					'batch_number'    => $batchNumber,
					'unit_cost'       => $unitCost,
					'total_cost'      => $unitCost * $quantity,
					'reference_id'    => $reference?->id,
					'reference_type'  => $reference?->getMorphClass(),
					'created_by'      => Auth::id() ?? User::where('email', 'wthered@gmail.com')->value('id'),
				]);
			});
		}

		/**
		 * Καταγραφή αποτυχίας στο σωστό, αυτόνομο πίνακα Logs.
		 */
		protected function logFailedMovement($productId, $warehouseId, $locationId, $requested, $available, $reference, string $message): void {
			InventoryMovementLog::query()->create([
				'product_id'         => $productId,
				'warehouse_id'       => $warehouseId,
				'location_id'        => $locationId,
				'action'             => 'STOCK_OUT_ATTEMPT',
				'status'             => TransferStatus::FAILED->value,
				'requested_quantity' => $requested,
				'before_quantity'    => $available,
				'error_message'      => $message,
				'loggable_type'      => $reference ? $reference->getMorphClass() : null,
				'loggable_id'        => $reference?->id,
				'user_id'            => Auth::id() ?? 1,
			]);

			Log::warning("Inventory Shortage Logged: Product {$productId} at Location {$locationId}");
		}

		public function getAvailableStock(Model $item): int {
			return Inventory::where('product_id', $item->product_id)
				       ->where('warehouse_id', $this->resolveWarehouseId($item))
				       ->where('location_id', $item->location_id)
				       ->value('quantity') ?? 0;
		}

		/**
		 * Μαρκάρει τη μεταφορά ως ολοκληρωμένη (Arrived / Completed)
		 * και προσθέτει τα αποθέματα στην αποθήκη Β.
		 */
		public function complete(int $id): JsonResponse {
			try {
				return DB::transaction(function () use ($id) {
					// 1. Lock the transfer record
					$transfer = StockTransfer::where('id', $id)->lockForUpdate()->firstOrFail();

					// Safety Check: Don't process it twice!
					if ($transfer->status_id === TransferStatus::COMPLETED) { // Or whatever your Enum value is
						return response()->json(['error' => 'This transfer has already been completed.'], 422);
					}

					// 2. Update Header Status
					$transfer->update([
						'status_id'   => TransferStatus::COMPLETED->value, // update to your completed enum state
						'received_at' => now(),
						'received_by' => Auth::id(),
					]);

					// 3. Loop through items to update received quantities and load into Warehouse B
					foreach ($transfer->items as $item) {
						// If you don't allow partial losses, quantity_received equals what was requested
						$item->update([
							'quantity_delivered' => $item->quantity_requested,
							'quantity_received'  => $item->quantity_requested,
							'processed_by'       => Auth::id(),
							'processed_at'       => now(),
						]);

						// 4. Manually trigger the IN movement for Warehouse B now!
						// We resolve the movement service manually here since we stripped it from the item's created observer hook.
						$this->execute(
							productId: $item->product_id,
							quantity: $item->quantity_requested,
							type: TransactionType::IN->value,
							reason: 'transfer_in',
							warehouseId: $transfer->target_warehouse_id,
							locationId: $item->target_location_id,
							batchNumber: $item->batch_number,
							reference: $item
						);
					}

					return response()->json([
						'success' => "Transfer " . $transfer->transfer_number . " has been successfully received at the target warehouse."
					]);
				});
			} catch (Throwable $e) {
				return response()->json(['error' => $e->getMessage()], 500);
			}
		}
	}