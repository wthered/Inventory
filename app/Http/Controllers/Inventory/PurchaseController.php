<?php

	namespace App\Http\Controllers\Inventory;

	use App\Enums\Purchases\PurchaseOrderStatus;
	use App\Http\Controllers\Controller;
	use App\Http\Requests\Purchases\OrderUpdateRequest;
	use App\Models\Category;
	use App\Models\Product;
	use App\Models\Purchases\PurchaseOrder;
	use App\Models\Supplier;
	use App\Models\Warehouse;
	use Illuminate\Http\Request;
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
			// Φορτώνουμε τα προϊόντα της συγκεκριμένης παραγγελίας για να έχουμε πρόσβαση στα category_id και brand_id τους
			$purchaseProducts = $purchase->items->pluck('product_id');
//			dd($purchaseProducts);

			$products = Product::query();

			if ($purchase->items->isNotEmpty()) {
				$products->where(function ($query) use ($purchase) {
					$products = Product::whereIn('id', $purchase->items->pluck('product_id'))->get();
					foreach ($products as $index => $product) {
						// Για το πρώτο προϊόν χρησιμοποιούμε 'where', για τα επόμενα 'orWhere'
//						$method = ($index === 0) ? 'where' : 'orWhere';

						$query->where(function ($subQuery) use ($product) {
							$subQuery->where('category_id', $product->category_id)
							         ->where('brand_id', $product->brand_id);
						});
					}
				});
			} else {
				// Αν η παραγγελία δεν έχει καθόλου προϊόντα, επιστρέφουμε άδειο Collection
				$products->whereRaw('1 = 0');
			}

			return view('purchases.edit', [
				'order'      => $purchase,
				'suppliers'  => Supplier::all(),
				'warehouses' => Warehouse::all(),
				'categories' => Category::with('brands')->get(),
				'products'   => $products->get(), // Εκτέλεση του query
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
