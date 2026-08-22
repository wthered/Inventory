<?php

	namespace App\Http\Controllers\Inventory;

	use App\DataTransferObjects\ProductDTO;
	use App\Enums\Purchases\PurchaseOrderStatus;
	use App\Http\Controllers\Controller;
	use App\Http\Requests\Purchases\OrderUpdateRequest;
	use App\Models\Category;
	use App\Models\Purchases\PurchaseOrder;
	use App\Models\Purchases\PurchaseOrderItem;
	use App\Models\Supplier;
	use App\Models\Warehouse;
	use Illuminate\Http\Request;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\DB;

	class PurchaseController extends Controller {
		/**
		 * Display a listing of the resource.
		 */
		public function index() {
			return view('purchases.index', [
				'orders' => PurchaseOrder::query()->paginate(25),
			]);
		}

		/**
		 * Show the form for creating a new resource.
		 */
		public function create() {
			//
		}

		/**
		 * Store a newly created resource in storage.
		 */
		public function store(Request $request) {
			//
		}

		/**
		 * Display the specified resource.
		 */
		public function show(PurchaseOrder $purchase) {
			$purchase->load([
				'supplier',
				'warehouse',
				'creator',
				'items.product',
				'history.user'
			]);

			return view('purchases.show', [
				'order'    => $purchase,
				'statuses' => PurchaseOrderStatus::cases(),
			]);
		}

		/**
		 * Show the form for editing the specified resource.
		 */
		public function edit(PurchaseOrder $purchase) {
			$purchase->load(['items']);
			$products = Collection::empty();

			if ($purchase->items->isNotEmpty()) {
				$products = $purchase->items->map(function (PurchaseOrderItem $item) {
					return new ProductDTO($item->product_id);
				});
			}

			return view('purchases.edit', [
				'order'      => $purchase,
				'suppliers'  => Supplier::all(),
				'warehouses' => Warehouse::all(),
				'categories' => Category::query()->whereNull('parent_id')->with(['children'])->get(),
				'products'   => $products,
			]);
		}

		/**
		 * Update the specified resource in storage.
		 *
		 * @throws \Throwable
		 */
		public function update(OrderUpdateRequest $request, PurchaseOrder $purchase) {
			// Παίρνουμε τα validated δεδομένα
			$validatedData = $request->validated();

			// Εκτέλεση μέσα σε Transaction
			DB::transaction(function () use ($purchase, $validatedData) {

				// 1. Ενημέρωση των βασικών στοιχείων του PurchaseOrder (χωρίς τα items)
				// Φιλτράρουμε τα δεδομένα για να κρατήσουμε μόνο αυτά που ανήκουν στο PurchaseOrder model
				$orderData = collect($validatedData)->except('items')->toArray();
				$purchase->update($orderData);

				// 2. Διαγραφή των παλιών items
				$purchase->items()->delete();

				// 3. Δημιουργία και συσχέτιση των νέων items
				if (!empty($validatedData['items'])) {
					$itemsToCreate = collect($validatedData['items'])->map(function ($item) {
						return [
							'product_id'       => $item['product_id'],
							'batch_number'     => $item['batch_number'] ?? null,
							'expiry_date'      => $item['expiry_date'] ?? null,
							'quantity_ordered' => $item['quantity_ordered'],
							'unit_price'       => $item['unit_price'],
							'discount_rate'    => $item['discount_rate'] ?? 0.0,
						];
					});

					// Μαζική δημιουργία των νέων items μέσω της σχέσης
					$purchase->items()->createMany($itemsToCreate->toArray());
				}
			});

			// Επιστροφή response ή redirect ανάλογα με τις ανάγκες σου
			return redirect()
				->route('inventory.purchases.edit', ['purchase' => $purchase])
				->with('success', 'Η παραγγελία ενημερώθηκε με επιτυχία!');
		}

		/**
		 * Remove the specified resource from storage.
		 */
		public
		function destroy(
			string $id
		) {
			//
		}
	}
