<?php

	namespace App\Http\Controllers\Inventory;

	use App\DataTransferObjects\ProductDTO;
	use App\DataTransferObjects\UserDTO;
	use App\Enums\Inventory\AdjustmentReason;
	use App\Http\Controllers\Controller;
	use App\Http\Requests\Products\ProductInformationRequest;
	use App\Http\Requests\Products\ProductSearchRequest;
	use App\Http\Requests\Products\ProductStoreRequest;
	use App\Http\Requests\Products\UpdateProductRequest;
	use App\Models\Brand;
	use App\Models\Category;
	use App\Models\Inventories\Inventory;
	use App\Models\Product;
	use App\Models\Supplier;
	use App\Services\Inventory\InventoryLevelService;
	use App\Services\Search\ProductSearchService;
	use Exception;
	use Illuminate\Contracts\View\Factory;
	use Illuminate\Contracts\View\View;
	use Illuminate\Http\JsonResponse;
	use Illuminate\Http\RedirectResponse;
	use Illuminate\Http\Request;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\Auth;

	class ProductController extends Controller {

		private InventoryLevelService $service;
		private ProductSearchService $searchService;

		public function __construct(InventoryLevelService $inventoryLevelService, ProductSearchService $productSearchService) {
			$this->service = $inventoryLevelService;
			$this->searchService = $productSearchService;
		}

		/**
		 * Display a listing of the resource.
		 */
		public function index(): Factory|View {
			return view('products.index', [
				'user'          => UserDTO::fromModel(Auth::user()),
				'categories'    => Category::whereNull('parent_id')->get(),
				'suppliers'     => Supplier::all(),

				// 1. Counts - Χρησιμοποιούμε τη λογική των inventories
				'low_stock'     => Product::whereHas('inventories', function ($q) {
					$q->havingRaw('SUM(available_quantity) <= products.reorder_point');
				})->count(),

				'out_of_stock'  => Product::whereDoesntHave('inventories', function ($q) {
					$q->where('available_quantity', '>', 0);
				})->count(),

				'product_count' => Product::count(),
				'product_list'  => Product::with(['images', 'category', 'brand', 'inventories'])->latest()->paginate(25),

				// 2. Συνολική Αξία Αποθέματος
				'total_value'   => Product::join('inventories', 'products.id', '=', 'inventories.product_id')->selectRaw('SUM(inventories.available_quantity * products.selling_price) as total')->value('total') ?? 0,
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
		public function show(Request $request, int $product): Factory|View {
			$entry  = new ProductDTO($product);
			$profit = $entry->selling_price - $entry->cost_price;
			$stock  = Collection::make([
				'total'     => 0,
				'available' => 0,
				'reserved'  => 0,
			]);

			$status = [];

			$entry->inventories->each(function (Inventory $inventory) use (&$stock, &$status, $entry) {
				$stock['available']                                        += $inventory->quantity;
				$stock['reserved']                                         += $inventory->reserved_quantity;

				$status[$inventory->warehouse_id][$inventory->location_id] = $this->service->getInventoryAnalysis($entry);

				// Test Action
				$tier = mt_rand(0, 4);
//				$status[$inventory->warehouse_id][$inventory->location_id] = [
//					'product_id' => $entry->id,
//					'product_name' => $entry->name,
//					'current_quantity' => mt_rand(8, 64),
//					'min_stock' => 8,
//					'max_stock' => 64,
//					'tier' => $tier,
//					'tier_label' => 'Tier Label',
//					'percentage_of_max' => 60,
//					'status' => 'UNKNOWN',
//					'suggested_action' => 'Suggested Action'
//				];
			});
			$stock['total'] = $stock['available'] + $stock['reserved'];

			return view('products.show', [
				'product'      => $entry,
				'stock'        => $stock,
				'status'       => Collection::make($status),
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
				'brand'        => $entry->brand,
				'suppliers'    => $entry->suppliers,
				'warehouses'   => $entry->warehouses,
				'reasons'      => AdjustmentReason::forDropdown()->toArray(),
				'inventories'  => $entry->inventories->where('quantity', '>', 0),
				'user'         => UserDTO::fromModel($request->user()),
			]);
		}

		/**********************
		 * Show the form for editing the specified resource.
		 *********************/
		public function edit(Request $request, int $product): Factory|View {

			$object = new ProductDTO($product);

			return view('products.edit', [
				'user'             => UserDTO::fromModel($request->user()),
				'categories'       => Category::query()->whereNull('parent_id')->orderBy('sort_order')->get(),
				'parent_category'  => Category::query()->find($object->category['parent_id']),
				'child_categories' => Category::query()->where('parent_id', $object->category['parent_id'])->get(),
				'brands'           => Brand::query()->get(),
				'product'          => $object,
				'images'           => $object->images,
				'suppliers'        => $object->suppliers,
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
		public function update(UpdateProductRequest $request, Product $product): RedirectResponse {
			$input = $request->input();
			dd($input);
			$product->update($input->toArray());
			return redirect()->route('inventory.products.index')->with('success', 'Product updated successfully!');
		}

		/**
		 * Δημιουργεί ένα αντίγραφο (clone) ενός υπάρχοντος προϊόντος.
		 */
		public function clone(Product $product): RedirectResponse {
			// 1. Χρησιμοποιούμε τη μέθοδο replicate() του Laravel για εύκολη αντιγραφή
			$newProduct = $product->replicate();

			// 2. Τροποποιούμε το όνομα για να δηλώσουμε ότι είναι αντίγραφο
			$newProduct->name = $product->name . ' (COPY)';

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
			$input = $request->validated();
			return response()->json(['product' => $input->get('product')]);
		}

		public function search(ProductSearchRequest $request): JsonResponse {
			// Retrieve only safely validated data from the request rules
			$filters = $request->validated();

			// Execute search via your dedicated service layer
			$results = $this->searchService->search($filters);

			// Return lightweight payload back to your vanilla JS
			return response()->json($results);
		}
	}
