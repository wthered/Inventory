<?php

	namespace App\Services\Inventory;

	use App\Contracts\StockMoveable;
	use App\DataTransferObjects\ProductDTO;
	use App\Enums\Inventory\TransactionReason;
	use App\Enums\Inventory\TransactionType;
	use App\Enums\Inventory\TransferStatus;
	use App\Exceptions\Inventory\InsufficientStockException;
	use App\Models\{StockAdjustmentItem, StockReturnItem, StockTransfer, User, WarehouseLocation};
	use App\Models\Inventories\{Inventory, InventoryMovementLog, InventoryTransaction};
	use Carbon\Carbon;
	use Exception;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\QueryException;
	use Illuminate\Http\JsonResponse;
	use Illuminate\Support\Facades\{Auth, DB, Log};
	use Throwable;

	class StockMovementService {
		protected ?int $warehouse;

		public function __construct() {
			$this->warehouse = null;
		}

		/**
		 * Η κεντρική μέθοδος που καλεί ο Observer.
		 *
		 * @throws Exception|Throwable
		 */
		public function handleItemMovement(Model $item, string $type): ?InventoryTransaction {
			if (empty($item->quantity)) {
				Log::info("Skipping stock movement for ".class_basename($item)." ID: ".$item->id." due to zero/null quantity.");
				return null;
			}

			$this->warehouse = $this->resolveWarehouseId($item);
			$reason = $this->guessReason($item);

			try {
//				dd($item->quantity);
				return $this->execute(productId: $item->product_id, quantity: $item->quantity, type: $type, reason: $reason, locationId: $item->location_id, reference: $item);

			} catch (InsufficientStockException $e) {
				// Πιάστηκε το custom exception ελλιπούς αποθέματος.
				// Καταγράφουμε στο ανεξάρτητο Log, εκτός του transaction που έκανε rollback.
				$this->logFailedMovement(productId: $item->product_id, warehouseId: $this->warehouse, locationId: $item->location_id, requested: $item->quantity, available: $this->getAvailableStock($item), reference: $item, message: $e->getMessage());

				throw $e; // Re-throw για να ενημερωθεί το UI / Controller
			}
		}

		public function resolveWarehouseId(Model $item): int {
			if ($item instanceof StockMoveable) {
				return $item->getWarehouseId();
			}
			$warehouse = $item->warehouse_id ?? WarehouseLocation::query()->find($item->location_id)->warehouse;
			dd($warehouse, $this->warehouse);

			if (!$warehouse) {
				throw new Exception("Warehouse ID not found on model properties.");
			}
			return $warehouse;
		}

		protected function guessReason(Model $item): string {
			return match (true) {
				$item instanceof StockReturnItem     => TransactionReason::RETURNED->value,
				$item instanceof StockAdjustmentItem => TransactionReason::STOCKTAKE->value,
				isset($item->reason)                 => $item->reason,
				default                              => TransactionReason::OTHER->value
			};
		}

		/**
		 * Core Logic με Pessimistic Locking και ασφαλή updates.
		 *
		 * @throws Throwable
		 */
		public function execute(
			int $productId,
			int $quantity,
			string $type,
			string $reason,
//			int $warehouseId,
			?int $locationId = null,
			?string $batchNumber = null,
			?Model $reference = null
		): InventoryTransaction {
			return DB::transaction(function () use (
				$productId,
				$quantity,
				$type,
				$reason, /* $warehouseId */
				$locationId,
				$batchNumber,
				$reference
			) {

				$productObject = new ProductDTO($productId);

				if (is_null($locationId)) {
					$locationId = $productObject->locations->where('warehouse_id', $this->warehouse)->pluck('id')->random() ?? throw new Exception("No valid location found.");
				}

				// Include batch number in unique constraint
				$criteria = [
					'product_id'   => $productObject->id,
					'warehouse_id' => $this->warehouse,
					'location_id'  => $locationId,
				];

				// Only add batch if we're tracking it
				if ($batchNumber !== null) {
					$criteria['batch_number'] = $batchNumber;
				}

				$inventory = Inventory::query()->where($criteria)->lockForUpdate()->first();
				$productEntry = new ProductDTO($productId);

				if (!$inventory) {
					try {
						$inventory = Inventory::query()->create(array_merge($criteria, [
							'quantity'           => $quantity,
							'reserved_quantity'  => 0,
							'batch_number'       => $batchNumber ?? null,
							'manufacturing_date' => Carbon::parse('1st Jan last year')->addDays(mt_rand(0, 128)),
							'expiry_date'        => today()->addDays(mt_rand(32, 96)),
						]));

						// Lock it immediately after creation to be safe
						$inventory->lockForUpdate();
					} catch (QueryException $e) {
						// If another thread created it at the exact same microsecond,
						// catch the duplicate error and fetch it with a lock.
						$inventory = Inventory::query()->where($criteria)->lockForUpdate()->firstOrFail();
						// Example tracking an error via the inventory model instance
						$inventory->movementLogs()->create([
							'action'             => TransactionType::OUT,
							'status'             => 'failed',
							'requested_quantity' => $quantity,
							'before_quantity'    => $productEntry->current_stock,
							'error_message'      => $e->getMessage(),
							'user_id'            => Auth::id() ?? User::query()->where('email', 'wthered@gmail.com')->value('id'),
						]);
					}
				}

				$quantityAvailable = $inventory->quantity;
				$sign = TransactionType::from($type)->sign(); // -1 ή 0 ή 1

				// 4. Έλεγχος Αποθέματος με ρίψη του Custom Exception
				if ($sign === -1 && $quantityAvailable < $quantity) {
					$message = "Ανεπαρκές απόθεμα για το '".$inventory->product->name."' στο συγκεκριμένο ράφι. Διαθέσιμο: ".$quantityAvailable.", Ζητήθηκε: ".$quantity;
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
				$productEntry->update($productEntry->product, ['current_stock' => $totalStock]);

				// 6. Δημιουργία Ledger Entry με σωστά δεδομένα
				$unitCost = $inventory->product->cost_price;
				return InventoryTransaction::query()->create([
					'product_id'      => $productId,
					'warehouse_id'    => $this->warehouse,
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
					'created_by'      => Auth::id() ?? User::query()->where('email', 'wthered@gmail.com')->value('id'),
				]);
			});
		}

		/**
		 * Καταγραφή αποτυχίας στο σωστό, αυτόνομο πίνακα Logs.
		 */
		protected function logFailedMovement(
			$productId,
			$warehouseId,
			$locationId,
			$requested,
			$available,
			$reference,
			string $message
		): void {
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
			return Inventory::query()->where('product_id', $item->product_id)
			                ->where('warehouse_id', $this->warehouse)
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
					$transfer = StockTransfer::query()->where('id', $id)->lockForUpdate()->firstOrFail();

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
//							warehouseId: $transfer->target_warehouse_id,
							locationId: $item->target_location_id,
							batchNumber: $item->batch_number,
							reference: $item
						);
					}

					return response()->json([
						'success' => "Transfer ".$transfer->transfer_number." has been successfully received at the target warehouse."
					]);
				});
			} catch (Throwable $e) {
				return response()->json(['error' => $e->getMessage()], 500);
			}
		}
	}
