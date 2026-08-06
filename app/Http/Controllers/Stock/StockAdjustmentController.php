<?php

	namespace App\Http\Controllers\Stock;

	use App\DataTransferObjects\ProductDTO;
	use App\DataTransferObjects\UserDTO;
	use App\Enums\Inventory\AdjustmentReason;
	use App\Enums\Inventory\AdjustmentType;
	use App\Enums\Inventory\MovementStatus;
	use App\Http\Controllers\Controller;
	use App\Http\Requests\Stocks\StockAdjustments\StockAdjustmentApproveRequest;
	use App\Http\Requests\Stocks\StockAdjustments\StockAdjustmentStoreRequest;
	use App\Http\Requests\Stocks\StockAdjustments\StockAdjustmentUpdateRequest;
	use App\Http\Requests\Stocks\StockAdjustments\StockAdjustmentValidationRequest;
	use App\Models\Category;
	use App\Models\Product;
	use App\Models\StockAdjustment;
	use App\Models\StockAdjustmentItem;
	use App\Models\Warehouse;
	use App\Models\WarehouseLocation;
	use App\Services\Stock\AdjustmentReasonService;
	use App\Services\Stock\InventoryStockService;
	use Carbon\Carbon;
	use Exception;
	use Illuminate\Http\RedirectResponse;
	use Illuminate\Http\Request;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Str;
	use Throwable;

	class StockAdjustmentController extends Controller {

		private AdjustmentReasonService $service;
		private InventoryStockService   $inventoryService;

		public function __construct(
			AdjustmentReasonService $adjustmentReasonService,
			InventoryStockService $stockService
		) {
			$this->service = $adjustmentReasonService;
			$this->inventoryService = $stockService;
		}

		/**
		 * Display a listing of the resource.
		 */
		public function index() {
			$adjustments = StockAdjustment::query()->with(['items.product', 'creator'])->latest()->paginate();

			// Μετασχηματισμός του collection κρατώντας το pagination άθικτο
			$adjustments->through(function ($adjustment) {
				if ($adjustment->creator) {
					// Αντικαθιστούμε το Eloquent Model του creator με το DTO του
					$adjustment->creator = UserDTO::fromModel($adjustment->creator)->account;
				}
				return $adjustment;
			});

			return view('stocks.adjustments.index', [
				'adjustments' => $adjustments,
				'reasons'     => AdjustmentReason::class,
				'user'        => Auth::check() ? UserDTO::fromModel(Auth::user()) : null,
			]);
		}

		/**
		 * Store a newly created resource in storage.
		 *
		 * @throws Throwable
		 */
		public function store(StockAdjustmentStoreRequest $request): RedirectResponse {
			$input = $request->validated();

			$adjustment = DB::transaction(function () use ($input) {
				// 1. Create Parent Stock Adjustment
				$adjustment = StockAdjustment::query()->create([
					'warehouse_id'      => (int) $input['warehouse_id'],
					'adjustment_number' => 'ADJ-'.Carbon::parse($input['adjustment_date'])->format('Ymd').'-'.Str::upper(Str::random(6)),
					'adjustment_date'   => Carbon::parse($input['adjustment_date'])->format('Y-m-d'),
					'notes'             => $input['notes'] ?? null,
					'status'            => MovementStatus::PENDING,
					'created_by'        => Auth::id(),
				]);

				// 2. Loop & Create Adjustment Items
				foreach ($input['items'] as $item) {
					$locationId = !empty($item['location_id']) ? (int) $item['location_id'] : null;
					$productId = (int) $item['product_id'];
					$quantity = (int) $item['quantity'];

					// Snapshot quantity_before from current stock (if location exists)
					$currentStock = $locationId
						? (WarehouseLocation::query()->where('id', $locationId)->value('current_capacity') ?? 0)
						: 0;

					// Calculate stock after adjustment based on item type
					$quantityAfter = ($item['type'] === 'increase')
						? ($currentStock + $quantity)
						: ($currentStock - $quantity);

					$product = new ProductDTO($productId);

					$adjustment->items()->create([
						'product_id'      => $product->id,
						'location_id'     => $locationId,
						'type'            => $item['type'],
						'reason'          => $item['reason'],
						'quantity'        => $quantity,
						'quantity_before' => $currentStock,
						'quantity_after'  => $quantityAfter,
						'unit_cost'       => $product->cost_price,
						'notes'           => $item['notes'] ?? null,
					]);
				}

				return $adjustment;
			});

			return redirect()->route('inventory.adjustments.show', $adjustment)->with('success', 'Stock adjustment created successfully.');
		}

		/**
		 * Show the form for creating a new resource.
		 */
		public function create(Request $request) {
			return view('stocks.adjustments.create', [
				'warehouses'        => Warehouse::query()->get(),
				'typeGroup'         => AdjustmentType::class,
				'adjustmentReasons' => AdjustmentReason::class,
			]);
		}

		/**
		 * Display the specified resource.
		 */
		public function show(Request $request, StockAdjustment $adjustment) {
			// Έλεγχος αν η κατάσταση είναι Approved ή Completed
			$isFinalized = $adjustment->status === MovementStatus::APPROVED || $adjustment->status === MovementStatus::COMPLETED;

			$adjustment->load(['items']);
			$adjustment->items->map(function (StockAdjustmentItem $item) {
				$item->isNegative = $item->type === AdjustmentType::DECREASE;
			});

			return view('stocks.adjustments.show', [
				'adjustment'         => $adjustment,
				'adjustmentStatuses' => MovementStatus::forAdjustment(),
				'canBeEdited'        => $adjustment->status !== MovementStatus::COMPLETED && $adjustment->status !== MovementStatus::CANCELED,
				'user'               => Auth::check() ? UserDTO::fromModel($request->user()) : null,

				// 💡 Προσθήκη των χρωμάτων εδώ:
				'statusColor'        => $isFinalized ? '#f0fdf4' : '#fffbeb',
				'statusBorderColor'  => $adjustment->status->color(),
			]);
		}

		/**
		 * Show the form for editing the specified resource.
		 *
		 * @throws Exception
		 */
		public function edit(string $stockAdjustment) {
			// 1. Σιγουρεύεσαι ότι οι γραμμές έχουν ήδη φορτωμένα τα updates τους
			$adjustment = StockAdjustment::query()->find($stockAdjustment)->load([
				'items.location', 'items.product.brand'
			]);

			// 2. Φέρνεις τις τοποθεσίες της συγκεκριμένης αποθήκης, ταξινομημένες
			$locations = WarehouseLocation::query()->where('warehouse_id', $adjustment->warehouse_id)->orderBy('name')->pluck('id');

			$activeLocations = $adjustment->items()->whereHas('location', function ($query) use ($locations) {
				$query->whereIn('location_id', $locations);
			})->pluck('id')->sort();

			$locationList = Collection::empty();
			WarehouseLocation::query()->whereIn('id', $activeLocations)->get()->each(function (
				WarehouseLocation $location
			) use (&$locationList) {
				$locationList->push($location);
			});

			return view('stocks.adjustments.edit', [
				'adjustment'       => $adjustment,
				'products'         => Product::query()->get(),
				'warehouses'       => Warehouse::all(),
				'locations'        => $locationList,
				'current_location' => $adjustment->items->where('location_id', $adjustment->warehouse_id)->first(),
				'categories'       => Category::query()->whereNull('parent_id')->with('children')->orderBy('sort_order')->get(),
				'reasons'          => AdjustmentReason::forDropdown(),
				'types'            => AdjustmentType::class,
				'user'             => Auth::check() ? UserDTO::fromModel(Auth::user()) : null,
				'manager'          => $adjustment->warehouse->manager->account ? UserDTO::fromModel($adjustment->warehouse->manager) : null,
			]);
		}

		/**
		 * Update the specified resource in storage.
		 *
		 * @throws Throwable
		 */
		public function update(StockAdjustmentUpdateRequest $request, StockAdjustment $adjustment) {
			$data = $request->validated();

			// Χρήση Transaction για να εξασφαλίσουμε την ακεραιότητα των δεδομένων
			DB::transaction(function () use ($adjustment, $data) {

				// 2. Ενημέρωση των στοιχείων του Header
				$adjustment->update([
					'warehouse_id'    => $data['warehouse_id'],
					'adjustment_date' => $data['adjustment_date'],
					'notes'           => $data['notes'] ?? null,
				]);

				// 3. Διαχείριση Γραμμών (Items)
				if (!empty($data['items'])) {

					// Α) ΔΙΑΓΡΑΦΗ: Κρατάμε μόνο τα πραγματικά IDs που ήρθαν από τη φόρμα
					$existingItems = array_filter(array_keys($data['items']), function ($key) {
						return !str_starts_with($key, 'new_');
					});

					// Διαγράφουμε όσα items υπήρχαν στη βάση αλλά λείπουν πλέον από τη φόρμα
					$adjustment->items()->whereNotIn('id', $existingItems)->delete();

					// Β) ΕΝΗΜΕΡΩΣΗ Ή ΔΗΜΙΟΥΡΓΙΑ
					foreach ($data['items'] as $itemEntry => $itemData) {

						if (str_starts_with($itemEntry, 'new_')) {
							// Νέα γραμμή από τη JS
							$product = Product::find($itemData['product_id']);

							// Φέρνουμε το inventory status για τη συγκεκριμένη τοποθεσία
							$productLocation = $product?->inventories()->where('location_id', $itemData['location_id'])->first();

							$quantityBefore = $productLocation ? $productLocation->quantity : 0;

							$adjustment->items()->create([
								'product_id'      => $itemData['product_id'],
								'location_id'     => $itemData['location_id'],
								'reason'          => $itemData['reason'],
								'type'            => $itemData['type'],
								'quantity'        => $itemData['quantity'],
								'quantity_before' => $quantityBefore,
								'unit_cost'       => $product ? $product->cost_price : 0,
								'notes'           => $itemData['notes'] ?? null,
							]);
						} else {
							// Ενημέρωση υπάρχουσας γραμμής
							$item = $adjustment->items()->find($itemEntry);
							$item?->update([
								'product_id'  => $itemData['product_id'],
								'location_id' => $itemData['location_id'],
								'reason'      => $itemData['reason'],
								'type'        => $itemData['type'],
								'quantity'    => $itemData['quantity'],
								'notes'       => $itemData['notes'] ?? null,
							]);
						}
					}
				} else {
					// Αν η φόρμα δεν έστειλε κανένα item, διαγράφονται όλα
					$adjustment->items()->delete();
				}
			});

			// 4. Ανακατεύθυνση στο ευρετήριο με μήνυμα επιτυχίας
			return redirect()->route('inventory.adjustments.index')
			                 ->with('success', 'Η προσαρμογή ενημερώθηκε επιτυχώς.');
		}

		/**
		 * Remove the specified resource from storage.
		 */
		public function destroy(StockAdjustment $adjustment) {
			$adjustment->delete();
			// Ανακατεύθυνση στο ευρετήριο με μήνυμα επιτυχίας
			return redirect()->route('inventory.adjustments.index')
			                 ->with('success', 'Η προσαρμογή διαγράφηκε επιτυχώς.');
		}

		/**
		 * @throws Exception
		 */
		public function reasons(Request $request) {
			return $this->service->generateReasonDropdown();
		}

		public function check(StockAdjustmentValidationRequest $request) {
			$input = $request->validated();
			$quantity = [
				'current'    => $input['inputData']['currentQuantity'],
				'maximum'    => $input['inputData']['maximumQuantity'],
				'adjustment' => $input['quantity'],
			];
			$isAllowed = $quantity['current'] + $quantity['adjustment'] <= $quantity['maximum'];
			return response()->json([
				'success' => false,
				'allowed' => $isAllowed,
			], $isAllowed ? 200 : 406);
		}

		/**
		 * Εγκρίνει και οριστικοποιεί την προσαρμογή αποθέματος.
		 */
		public function approve(StockAdjustmentApproveRequest $request, StockAdjustment $adjustment) {
			$input = $request->validated();
//			dd($input);

			//  Έλεγχος αν έχει ήδη εγκριθεί για αποφυγή διπλών εγγραφών
			if ($adjustment->approved_at) {
//				dd($adjustment);
				return redirect()
					->route('inventory.adjustments.show', $adjustment->id)
					->with('error', 'Αυτό το παραστατικό έχει ήδη εγκριθεί.');
			}

			try {
				DB::transaction(function () use ($input, $adjustment) {
					// 1. Ενημέρωση των στηλών έγκρισης στην Database του Adjustment
					$adjustment->update([
						'status'      => $input['status'],
						'approved_by' => Auth::user()->id,
						'approved_at' => now(),
					]);

					// 2. Κλήση του Service για την ενημέρωση των πραγματικών ποσοτήτων στην αποθήκη
					$this->inventoryService->updatePhysicalInventory($adjustment);
				});

			} catch (Exception|Throwable $e) {
				return redirect()
					->route('inventory.adjustments.show', $adjustment->id)
					->with('error', 'Σφάλμα κατά την έγκριση: '.$e->getMessage());
			}

			// 3. Επιστροφή στην προβολή με μήνυμα επιτυχίας (Το dd() αφαιρέθηκε!)
			return redirect()
				->route('inventory.adjustments.show', $adjustment->id)
				->with('success', 'Το παραστατικό εγκρίθηκε και το απόθεμα ενημερώθηκε επιτυχώς!');
		}
	}
