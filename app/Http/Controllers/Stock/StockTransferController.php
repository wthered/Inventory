<?php

	namespace App\Http\Controllers\Stock;

	use App\Enums\TransferStatus;
	use App\Http\Controllers\Controller;
	use App\Http\Requests\Stocks\StockTransfers\StockTransferStoreRequest;
	use App\Models\Inventories\Inventory;
	use App\Models\Inventories\InventoryTransaction;
	use App\Models\Product;
	use App\Models\StockTransfer;
	use App\Models\StockTransferItem;
	use App\Models\WarehouseLocation;
	use Carbon\Carbon;
	use Illuminate\Http\JsonResponse;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Str;
	use Throwable;

	class StockTransferController extends Controller {
		public function store(StockTransferStoreRequest $request): JsonResponse {
			$validated = $request->validated();

			// Βρες το inventory record που συνδέει προϊόν ΚΑΙ τοποθεσία
			$sourceInventory = Inventory::query()
				->where('product_id', $validated['product_id'])
				->whereHas('location', function ($query) use ($validated) {
					$query->where([
						'warehouse_id' => $validated['source_location']['warehouse_id'],
						'zone'         => "Z" . $validated['source_location']['zone'],
						'aisle'        => "A" . $validated['source_location']['aisle'],
						'rack'         => $validated['source_location']['rack'],
						'shelf'        => $validated['source_location']['shelf'],
						'bin'          => $validated['source_location']['bin'],
					]);
				})->firstOrFail();

			$targetInventory = Inventory::query()
				->where('product_id', $validated['product_id'])
				->whereHas('location', function ($query) use ($validated) {
					$query->where([
						'warehouse_id' => $validated['target_location']['warehouse_id'],
						'zone'         => config('warehouses.prefixes.zone') . $validated['target_location']['zone'],
						'aisle'        => config('warehouses.prefixes.aisle') . $validated['target_location']['aisle'],
						'rack'         => $validated['target_location']['rack'],
						'shelf'        => $validated['target_location']['shelf'],
						'bin'          => $validated['target_location']['bin'],
					]);
				})->firstOrFail();

			try {
				// Return a JSON response is transaction has been finished with success (all parts succeeded)
				return DB::transaction(function () use ($validated, $sourceInventory, $targetInventory) {
					// 1. Create the Header
					$transfer = StockTransfer::create([
						'transfer_number'     => StockTransfer::generateTransferNumber(),
						'source_warehouse_id' => $validated['source_location']['warehouse_id'],
						'target_warehouse_id' => $validated['target_location']['warehouse_id'],
						'status_id'           => TransferStatus::PENDING,
						'created_by'          => Auth::id(),
						'transfer_date'       => Carbon::now(config('app.timezone'))->toDateString(),
					]);

					// 2. Δημιουργία του Item μέσω της σχέσης (Clean code). Pending σημαίνει 0 delivered (Not yet delivered)
					// Τα υπογραμισμένα αν τα κάνω click, θα γόνουν filled στο StockTransfer model και ΟΧΙ στο StockTransferItem
					$transfer->items()->create([
						'product_id'         => $validated['product_id'],
						'quantity_requested' => $validated['quantity'],
						'quantity_delivered' => 0, //
						'quantity_received'  => 0,
						'processed_by'       => Auth::id(),
						'notes'              => $validated['notes'] ?? null,
					]);

					// 3. Record the "OUT" transaction from Source
					$outgoing = InventoryTransaction::create([
						'product_id'      => $validated['product_id'],
						'warehouse_id'    => $transfer->source_warehouse_id,
						'type'            => InventoryTransaction::TYPE_OUT,
						'reason'          => InventoryTransaction::REASON_TRANSFER_OUT,
						'quantity'        => $validated['quantity'],
						'quantity_before' => $sourceInventory->quantity,
						'quantity_after'  => $sourceInventory->quantity - $validated['quantity'],
						'batch_number'    => $sourceInventory->batch_number,
						'reference_id'    => $transfer->id,
						'reference_type'  => StockTransfer::class,
						'created_by'      => Auth::id(),
					]);

					// 4. Record the "IN" transaction at Destination
					$incoming = InventoryTransaction::create([
						'product_id'      => $validated['product_id'],
						'warehouse_id'    => $transfer->target_warehouse_id,
						'location_id'     => $transfer->source_location_id,
						'type'            => InventoryTransaction::TYPE_IN,
						'reason'          => InventoryTransaction::REASON_TRANSFER_IN,
						'quantity'        => $validated['quantity'],
						'quantity_before' => $targetInventory->quantity,
						'quantity_after'  => $targetInventory->quantity + $validated['quantity'],
						'batch_number'    => $sourceInventory->batch_number,
						'reference_id'    => $transfer->id,
						'reference_type'  => StockTransfer::class,
						'created_by'      => Auth::id(),
					]);

					$sourceInventory->decrement('quantity', $validated['quantity']);
					$targetInventory->increment('quantity', $validated['quantity']);

					return response()->json([
						'transfer' => $transfer->id,
						'outgoing' => $outgoing->id,
						'incoming' => $incoming->id,
						'success'  => "Transfer " . $transfer->transfer_number . " processed successfully.",
					]);
				});
			} catch (Throwable $e) {
				return response()->json([
					'error' => $e->getMessage(),
				]);
			}
		}
	}
