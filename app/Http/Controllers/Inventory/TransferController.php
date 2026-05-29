<?php

	namespace App\Http\Controllers\Inventory;

	use App\Enums\TransferStatus As TransferStatusEnum;
	use App\Http\Controllers\Controller;
	use App\Http\Requests\Inventories\TransferRequest;
	use App\Models\Inventories\TransferStatus;
	use App\Models\Inventory;
	use App\Models\Inventories\InventoryTransaction;
	use App\Models\StockTransfer;
	use App\Models\Transfer;
	use App\Models\TransferItem;
	use App\Models\Warehouse;
	use App\Models\WarehouseLocation;
	use Carbon\Carbon;
	use Exception;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Str;
	use Throwable;

	class TransferController extends Controller {
		/**
		 * Handle stock transfer between locations
		 * POST /transfers
		 *
		 * @throws Throwable
		 */
		public function store(TransferRequest $request) {
			$data = $request->validated();

			try {
				// Validate source has sufficient stock
				$sourceInventory = Inventory::where('product_id', $data->get('product_id'))->where('location_id', $data->get('location_id'))->firstOrFail();

				if ($sourceInventory->quantity < $data->get('quantity')) {
					throw new Exception("Insufficient stock at source location. Available: " . $sourceInventory->quantity);
				}

				$targetLocation = WarehouseLocation::where([
					'warehouse_id' => $data['targetLocation']['warehouse'],
					'zone'         => $data['targetLocation']['zone'],
				])->firstOrFail();

				// Get or create destination inventory
				$destinationInventory = Inventory::firstOrCreate([
					'product_id'   => $data->get('product_id'),
					'location_id'  => $data->get('targetInventory'),
					'warehouse_id' => $data['targetLocation']['warehouse'],
				], ['quantity' => 0]);

				$sourceBefore      = $sourceInventory->quantity;
				$destinationBefore = $destinationInventory->quantity;

				// Perform the transfer. No need for ->save() to be called
				$sourceInventory->decrement('quantity', $data->get('quantity', 0));
				$destinationInventory->increment('quantity', $data->get('quantity', 0));

				$sourceAfter      = $sourceInventory->fresh()->quantity;
				$destinationAfter = $destinationInventory->fresh()->quantity;

				// Create transfer record
				$transfer = Transfer::query()->create([
					'product_id'          => $data->get('product_id'),
					'source_warehouse_id' => $data['sourceLocation']['warehouse'],
					'target_warehouse_id' => $data['targetLocation']['warehouse'],
					'status_id'           => TransferStatus::query()->find(TransferStatusEnum::PENDING)->id,
					'transferred_by'      => Auth::id(),
					'transfer_date'       => Carbon::now(config('app.timezone'))->toDateString(),
					'approved_by'         => Auth::id(),
					'approved_at'         => Carbon::now(config('app.timezone'))->toDateString(),
					'reference_number'    => 'TRF-'.date('Y-m-d').'-'.Str::of(Str::ulid())->upper(),
					'notes'               => $data->get('notes', 'Completed transfer for product'),
				]);

				// Source transaction (decrease)
				$decrease = InventoryTransaction::create([
					'product_id'         => $data->get('product_id'),
					'warehouse_id'       => $data['sourceLocation']['warehouse'],
					'location_id'        => $data->get('location_id'),
					'type'               => InventoryTransaction::TYPE_OUT,
					'quantity'           => $data->get('quantity'),
					'quantity_before'    => $sourceBefore,
					'quantity_after'     => $sourceAfter,
					'reference_type'     => Transfer::class,
					'reference_id'       => $transfer->id,
					'notes'              => $data->get('notes', 'Outbound Transfer from ' . Auth::user()->name),
					'created_by'         => Auth::id(),
				]);

				// Destination transaction (increase)
				$increase = InventoryTransaction::create([
					'product_id'         => $data->get('product_id'),
					'warehouse_id'       => $data['targetLocation']['warehouse'],
					'location_id'        => $targetLocation->id,
					'type'               => InventoryTransaction::TYPE_IN,
					'quantity'           => $data->get('quantity'),
					'quantity_before'    => $destinationBefore,
					'quantity_after'     => $destinationAfter,
					'reference_type'     => Transfer::class,
					'reference_id'       => $transfer->id,
					'notes'              => $data->get('notes', 'Inbound transfer from ' . Auth::user()->name),
					'created_by'         => Auth::id(),
				]);

				$transfer_item = TransferItem::create([
					'transfer_id'    => $transfer->id,
					'product_id'     => $data->get('product_id'),
					'quantity'       => $data->get('quantity'),
					'item_notes'     => $data->get('notes', 'Transferred by ' . Auth::user()->name),
				]);

				$success = $increase->fresh()->quantity === $decrease->fresh()->quantity;
				return response()->json([
					'success'                  => $success,
					'message'                  => $success ? 'Transfer completed successfully.' : 'Something went wrong. Please try again.',
					'transfer_item'            => $transfer_item->transfer_id,
					'source_new_quantity'      => $sourceAfter,
					'target_new_quantity'      => $destinationAfter,
				]);

			} catch (Exception $e) {

				return response()->json([
					'success' => false,
					'message' => 'Transfer failed: ' . $e->getMessage(),
				], 500);

			}
		}

		/**
		 * List all transfers
		 * GET /transfers
		 */
		public function index() {
			$transfers = Transfer::with([
				'product',
				'sourceLocation',
				'destinationLocation',
				'transferredBy'
			])->latest()->paginate(20);

			return view('transfers.index', compact('transfers'));
		}

		/**
		 * Show specific transfer
		 * GET /transfers/{id}
		 */
		public function show(Transfer $transfer) {
			$transfer->load([
				'product',
				'sourceLocation',
				'destinationLocation',
				'sourceWarehouse',
				'destinationWarehouse',
				'transferredBy'
			]);

			return view('transfers.show', compact('transfer'));
		}

		public function create() {}

		public function warehouse(Warehouse $warehouse) {
			dd($warehouse);
		}
	}
