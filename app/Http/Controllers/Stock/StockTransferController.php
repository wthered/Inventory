<?php

	namespace App\Http\Controllers\Stock;

	use App\DataTransferObjects\UserDTO;
	use App\Enums\Inventory\TransferStatus;
	use App\Exceptions\Inventory\InsufficientStockException;
	use App\Http\Controllers\Controller;
	use App\Http\Requests\Stocks\StockTransfers\StockTransferStoreRequest;
	use App\Http\Requests\Stocks\StockTransfers\StockTransferUpdateRequest;
	use App\Models\Inventories\Inventory;
	use App\Models\StockTransfer;
	use App\Models\WarehouseLocation;
	use Carbon\Carbon;
	use Illuminate\Contracts\View\Factory;
	use Illuminate\Contracts\View\View;
	use Illuminate\Http\JsonResponse;
	use Illuminate\Http\RedirectResponse;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\DB;
	use Throwable;

	class StockTransferController extends Controller {

		public function index(): Factory|View|\Illuminate\View\View {
			// Fetch transfers with their related warehouses for the listing view
			$transfers = StockTransfer::with([
				'sourceWarehouse',
				'targetWarehouse',
				'items'
			])->has('items')->latest()->paginate(15);

			return view('transfers.index', [
				'transfers' => $transfers,
				'user'      => Auth::check() ? UserDTO::fromModel(Auth::user()) : null,
				'status'    => TransferStatus::class,
			]);
		}

		public function store(StockTransferStoreRequest $request): JsonResponse {
			$validated = $request->validated();

			// 1. Find target location safely
			$targetLocation = WarehouseLocation::query()
			                                   ->where([
				                                   'warehouse_id' => $validated['targetLocation']['warehouse'],
				                                   'zone'         => $validated['targetLocation']['zone'],
				                                   'aisle'        => $validated['targetLocation']['aisle'],
				                                   'rack'         => $validated['targetLocation']['rack'],
				                                   'shelf'        => $validated['targetLocation']['shelf'],
				                                   'bin'          => $validated['targetLocation']['bin'],
			                                   ])
			                                   ->firstOrFail();

			// 2. Find source inventory to grab batch numbers if tracking
			$sourceInventory = Inventory::query()
			                            ->where('location_id', $validated['location_id'])
			                            ->where('product_id', $validated['product_id'])
			                            ->firstOrFail();

			try {
				return DB::transaction(function () use ($validated, $sourceInventory, $targetLocation) {

					// Create Header
					$transfer = StockTransfer::query()->create([
						'transfer_number'     => StockTransfer::generateTransferNumber(),
						'source_warehouse_id' => $validated['sourceLocation']['warehouse'],
						'target_warehouse_id' => $validated['targetLocation']['warehouse'],
						'status_id'           => TransferStatus::PENDING->value,
						'created_by'          => Auth::id(),
						'transfer_date'       => Carbon::now(config('app.timezone'))->format('Y-m-d'),
					]);

					// Create Item (This triggers StockMovementItemObserver)
					$item = $transfer->items()->create([
						'product_id'         => $validated['product_id'],
						'batch_number'       => $sourceInventory->batch_number,
						'source_location_id' => $validated['location_id'],
						'target_location_id' => $targetLocation->id,
						'quantity_requested' => $validated['quantity'],
						'quantity_delivered' => 0,
						'quantity_received'  => 0,
						'processed_by'       => Auth::id(),
						'processed_at'       => now(),
						'notes'              => $validated['notes'] ?? null,
					]);

					return response()->json([
						'transfer' => $transfer->id,
						'item'     => $item->id,
						'success'  => "Transfer ".$transfer->transfer_number." initiated successfully.",
					]);
				});
			} catch (InsufficientStockException $e) {
				// This catches the custom exception thrown deep from inside StockMovementService!
				return response()->json(['error' => $e->getMessage()], 422);
			} catch (Throwable $e) {
				return response()->json(['error' => $e->getMessage()], 500);
			}
		}

		/**
		 * Display the specified stock transfer details.
		 */
		public function show(StockTransfer $transfer): Factory|View|\Illuminate\View\View {
			// Κάνουμε eager load τις αποθήκες, τον δημιουργό και τα items με τα προϊόντα τους για τη σελίδα προβολής
			$transfer->load([
				'sourceWarehouse',
				'targetWarehouse',
				'creator',
				'items'
			]);

			return view('transfers.show', [
				'transfer' => $transfer,
				'user'     => Auth::check() ? UserDTO::fromModel(Auth::user()) : null,
				'status'   => TransferStatus::class,
			]);
		}

		/**
		 * Remove/Cancel the specified stock transfer from storage.
		 */
		public function destroy(Request $request, StockTransfer $transfer): RedirectResponse {
			// 1. Επιχειρηματικός έλεγχος ασφαλείας με βάση το Enum σου
			if ($transfer->status_id->isFinalized()) {
				return redirect()
					->route('inventory.transfers.index')
					->with('error', 'Δεν είναι δυνατή η διαγραφή ή ακύρωση μιας ολοκληρωμένης/τελικής μεταφοράς.');
			}

			try {
				DB::transaction(function () use ($transfer) {
					// Αν η μεταφορά είναι DRAFT ή PENDING, μπορεί να ακυρωθεί/διαγραφεί.
					// Ανάλογα με την αρχιτεκτονική σου, μπορείς είτε να αλλάξεις το status σε CANCELED:
					$transfer->update([
						'status_id' => TransferStatus::CANCELED->value
					]);

					// Ή αν θέλεις hard delete, χρησιμοποιείς: $transfer->delete();
					// (Η αλλαγή σε CANCELED προτιμάται για λόγους ιστορικότητας/ledger)
				});

				return redirect()
					->route('inventory.transfers.index')
					->with('success', "Η μεταφορά {$transfer->transfer_number} ακυρώθηκε με επιτυχία.");

			} catch (Throwable $e) {
				return redirect()
					->route('inventory.transfers.index')
					->with('error', 'Παρουσιάστηκε σφάλμα κατά την ακύρωση της μεταφοράς: '.$e->getMessage());
			}
		}

		/**
		 * Update the specified stock transfer in storage.
		 */
		public function update(StockTransferUpdateRequest $request, StockTransfer $transfer): RedirectResponse {
			// Ασφάλεια: Έλεγχος κατάστασης και στο update
			if ($transfer->status_id !== TransferStatus::PENDING) {
				return redirect()
					->route('inventory.transfers.index')
					->with('error', 'Δεν είναι δυνατή η ενημέρωση αυτής της μεταφοράς.');
			}

			$validated = $request->validated();
//			dd($validated);

			try {
				DB::transaction(function () use ($transfer, $validated) {
					// Ενημέρωση σημειώσεων/header
					$transfer->update([
						'notes' => $validated['notes'] ?? $transfer->notes,
					]);

					// Ενημέρωση των ποσοτήτων στα items
					foreach ($validated['items'] as $itemData) {
						$item = $transfer->items()->find($itemData['id']);
						$item?->update([
							'quantity_requested' => $itemData['quantity']
						]);
					}
				});

				return redirect()
					->route('inventory.transfers.show', $transfer)
					->with('success', "Η μεταφορά {$transfer->transfer_number} ενημερώθηκε με επιτυχία.");

			} catch (Throwable $e) {
				return redirect()
					->back()
					->withInput()
					->with('error', 'Παρουσιάστηκε σφάλμα κατά την ενημέρωση: '.$e->getMessage());
			}
		}

		/**
		 * Show the form for editing the specified stock transfer.
		 */
		public function edit(
			Request $request,
			StockTransfer $transfer
		): Factory|View|\Illuminate\View\View|RedirectResponse {
			// Ασφάλεια: Επιτρέπεται η επεξεργασία ΜΟΝΟ αν η κατάσταση είναι PENDING
			if ($transfer->status_id !== TransferStatus::PENDING) {
				return redirect()
					->route('inventory.transfers.index')
					->with('error', 'Μπορείτε να επεξεργαστείτε μόνο μεταφορές που βρίσκονται σε εκκρεμότητα (Pending).');
			}

			$transfer->load([
				'sourceWarehouse',
				'targetWarehouse',
				'items'
			]);

			return view('transfers.edit', [
				'transfer' => $transfer,
				// Αν χρειάζεσαι λίστα αποθηκών για αλλαγή (προαιρετικά, αν το επιτρέπεις)
				// 'warehouses' => \App\Models\Warehouse::all(),
				'user'     => Auth::check() ? UserDTO::fromModel(Auth::user()) : null,
			]);
		}
	}