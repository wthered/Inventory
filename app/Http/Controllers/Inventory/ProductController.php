<?php

	namespace App\Http\Controllers\Inventory;

	use App\DataTransferObjects\ProductDTO;
	use App\Enums\Inventory\AdjustmentReason;
	use App\Http\Controllers\Controller;
	use App\Http\Requests\Products\ProductInformationRequest;
	use App\Http\Requests\Products\ProductSearchRequest;
	use App\Http\Requests\Products\ProductStoreRequest;
	use App\Http\Requests\Products\ProductUpdateRequest;
	use App\Models\Category;
	use App\Models\Inventories\Inventory;
	use App\Models\Product;
	use App\Models\Supplier;
	use App\Models\Warehouse;
	use App\Services\Inventory\InventoryLevelService;
	use App\Services\Inventory\LocationOptionsService;
	use App\Services\Search\ProductSearchService;
	use Exception;
	use Illuminate\Contracts\View\Factory;
	use Illuminate\Contracts\View\View;
	use Illuminate\Http\JsonResponse;
	use Illuminate\Http\RedirectResponse;
	use Illuminate\Http\Request;
	use Illuminate\Support\Collection;

	class ProductController extends Controller {

		private InventoryLevelService  $service;
		private ProductSearchService   $searchService;
		private LocationOptionsService $locationService;

		public function __construct(
			InventoryLevelService $inventoryLevelService,
			ProductSearchService $productSearchService,
			LocationOptionsService $locationOptionsService,
		) {
			$this->service = $inventoryLevelService;
			$this->searchService = $productSearchService;
			$this->locationService = $locationOptionsService;
		}

		/**
		 * Display a listing of the resource.
		 */
		public function index(): Factory|View {
			return view('products.index', [
				'categories' => Category::query()->whereNull('parent_id')->get(),
				'suppliers'  => Supplier::all(),

				// 1. Counts - Χρησιμοποιούμε τη λογική των inventories
				'low_stock'  => Product::query()->whereHas('inventories', function ($q) {
					$q->havingRaw('SUM(available_quantity) <= products.reorder_point');
				})->count(),

				'out_of_stock' => Product::query()->whereDoesntHave('inventories', function ($q) {
					$q->where('available_quantity', '>', 0);
				})->count(),

				'product_count' => Product::query()->count(),
				'product_list'  => Product::with([
					'images', 'category', 'brand', 'inventories'
				])->latest()->paginate(25),

				// 2. Συνολική Αξία Αποθέματος
				'total_value'   => Product::query()->join('inventories', 'products.id', '=', 'inventories.product_id')->selectRaw('SUM(inventories.available_quantity * products.selling_price) as total')->value('total') ?? 0,
			]);
		}

		/**
		 * Show the form for creating a new resource.
		 */
		public function create() {
			//
		}

		/**
		 * Display the specified resource.
		 *
		 * @throws Exception
		 */
		public function show(Request $request, Product $product): Factory|View {
			$entry = new ProductDTO($product->id);
			$profit = $entry->selling_price - $entry->cost_price;

			$inventoryItems = Collection::make($entry->inventories->items());
			$stock = Collection::make([
				'available' => $inventoryItems->sum('available_quantity'),
				'reserved'  => $inventoryItems->sum('reserved_quantity'),
			]);

			$inventoryStatuses = [];
			$entry->inventories->each(function (Inventory $item) use (&$inventoryStatuses, $entry) {
				$inventoryStatuses[$item->warehouse_id][$item->location_id] = $this->service->getInventoryAnalysis($entry);
			});

			return view('products.show', [
				'product'      => $entry,
				'stock'        => $stock,
				'statuses'     => $inventoryStatuses,
				'active_image' => $entry->images->where('is_default', 1)->first(),
				'thumbnails'   => $entry->images->where('is_default', 0)->all(),
				'profit'       => [
					'absolute' => $profit,
					'relative' => number_format(100 * $profit / $entry->cost_price, 2)
				],
				'categories'   => [
					'parent' => $entry->parent,
					'child'  => $entry->category,
				],
				'reasons'      => AdjustmentReason::forDropdown()->toArray(),
			]);
		}

		/**********************
		 * Show the form for editing the specified resource.
		 *********************/
		public function edit(Request $request, int $product): Factory|View {

			$object = new ProductDTO($product);

			return view('products.edit', [
				'categories'       => Category::query()->whereNull('parent_id')->orderBy('sort_order')->get(),
				'child_categories' => Category::query()->where('parent_id', $object->category['parent_id'])->get(),
				'brands'           => Category::query()->find($object->category['parent_id'])->brands()->get(),
				'product'          => $object,
			]);
		}

		/**
		 * Store a newly created resource in storage.
		 */
		public function store(ProductStoreRequest $request) {
			$input = $request->validated();
			dd($input);
		}

		/**
		 * Remove the specified resource from storage.
		 */
		public function destroy(Product $product): RedirectResponse {
			$product->delete();
			return response()->redirectTo(route('inventory.products.index'));
		}

		/**
		 * Update the specified resource in storage.
		 */
		public function update(ProductUpdateRequest $request, Product $product): RedirectResponse {
			// $request->validated() contains $validated array modified in passedValidation()
			$entry = ProductDTO::update($product, $request->validated());
			return redirect()->route('inventory.products.show', ['product' => $entry->id])->with('success', 'Product updated successfully!');
		}

		/**
		 * Δημιουργεί ένα αντίγραφο (clone) ενός υπάρχοντος προϊόντος.
		 */
		public function clone(Product $product): RedirectResponse {
			// 1. Χρησιμοποιούμε τη μέθοδο replicate() του Laravel για εύκολη αντιγραφή
			$newProduct = $product->replicate();

			// 2. Τροποποιούμε το όνομα για να δηλώσουμε ότι είναι αντίγραφο
			$newProduct->name = $product->name.' (COPY)';

			// 3. Καθαρίζουμε τα timestamps για να θεωρηθεί ως νέο record
			$newProduct->created_at = now();
			$newProduct->updated_at = now();

			// 4. Αποθηκεύουμε το νέο προϊόν
			$newProduct->save();

			// Προαιρετικά: Εάν υπάρχουν σχετικές σχέσεις (π.χ. tags, categories)
			// θα πρέπει να τις αντιγράψετε χειροκίνητα εδώ, π.χ.:
			// $newProduct->categories()->attach($product->categories->pluck('id'));

			return redirect()->route('inventory.products.edit', $newProduct->id)->with('success', 'Product successfully duplicated and ready for editing.');
		}

		/**
		 * Εμφανίζει το ιστορικό αλλαγών για ένα συγκεκριμένο προϊόν.
		 */
		public function history(Product $product): Factory|View {
			// Χρησιμοποιούμε τη σχέση 'history' που θα ορίσουμε στο μοντέλο Product
			// Φέρνουμε τα δεδομένα ταξινομημένα από το πιο πρόσφατο (desc)
			$history = $product->history()->with('user')->latest()->get();

			// Επιστροφή της view με τα δεδομένα
			return view('products.history', [
				'product' => $product,
				'history' => $history,
			]);
		}

		public function getInformation(ProductInformationRequest $request): JsonResponse {
			$input = Collection::make($request->validated());
			return response()->json([
				'product' => $input->get('product'),
			]);
		}

		public function search(ProductSearchRequest $request): JsonResponse {
			// Retrieve only safely validated data from the request rules
			$filters = $request->validated();

			// Execute search via your dedicated service layer
			$results = $this->searchService->search($filters);

			// Return lightweight payload back to your vanilla JS
			return response()->json($results);
		}

		public function getInventory(Request $request, Product $product) {
			$inventory = Collection::empty();
			$product->inventories()->get()->each(function ($item) use (&$inventory, $product) {
				$options = $this->locationService->getLocationOptions($product->id, $item->warehouse_id, $item->location_id);
				$inventory->push([
					'warehouse' => Warehouse::query()->find($item->warehouse_id),
					'location'  => $item->location_id,
					'zone'      => $options['zone'],
					'aisle'     => $options['aisle'],
					'rack'      => $options['rack'],
					'shelf'     => $options['shelf'],
					'bin'       => $options['bin'],
				]);
			});

			return response()->json([
				'inventory' => $inventory->first(),
			]);
		}
	}
