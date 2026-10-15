<?php

	namespace App\Http\Controllers;

	use App\Http\Requests\Brands\BrandStoreRequest;
	use App\Http\Requests\Brands\BrandUpdateRequest;
	use App\Http\Requests\Products\ProductSearchRequest;
	use App\Models\Brand;
	use App\Models\Category;
	use App\Models\Inventories\Inventory;
	use App\Models\Product;
	use App\Services\Search\ProductSearchService;
	use Illuminate\Http\JsonResponse;
	use Illuminate\Http\RedirectResponse;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Storage;
	use Illuminate\Support\Str;

	class BrandController extends Controller {

		private ProductSearchService $service;

		public function __construct(ProductSearchService $searchService) {
			$this->service = $searchService;
		}

		/**
		 * Display a listing of the resource.
		 */
		public function index(Request $request) {
			$query = Brand::query()->with('categories');

			// Text search on brand name
			if ($request->filled('search')) {
				$query->where('name', 'LIKE', '%' . trim($request->input('search')) . '%');
			}

			// Filter by child subcategory
			if ($request->filled('category_id')) {
				$query->whereHas('categories', function ($q) use ($request) {
					$q->where('categories.id', $request->input('category_id'));
				});
			} // Otherwise filter by parent category
			elseif ($request->filled('parent_category_id')) {
				$query->whereHas('categories', function ($q) use ($request) {
					$q->where('categories.parent_id', $request->input('parent_category_id'));
				});
			}

			$brands = $query->paginate($request->session()->get('per_page', 15))->withQueryString();

			// Parent categories for primary dropdown
			$categories = Category::query()->whereNull('parent_id')->pluck('name', 'id');

			// Child categories if parent is selected
			$subcategories = collect();
			if ($request->filled('parent_category_id')) {
				$subcategories = Category::query()
				                         ->where('parent_id', $request->input('parent_category_id'))
				                         ->pluck('name', 'id');
			}

			return view('brands.index', compact('brands', 'categories', 'subcategories'));
		}

		/**
		 * Show the form for creating a new resource.
		 *
		 */
		public function create() {
			return view('brands.create', [
				'categories' => Category::query()->whereNull('parent_id')->orderBy('name')->pluck('name', 'id'),
			]);
		}

		/**
		 * Store a newly created resource in storage.
		 */
		public function store(BrandStoreRequest $request) {
			// 1. Get validated data from request
			$input = $request->validated();

			// Add slug as automated attribute
			$input['slug'] = Str::slug($input['name']);

			// 2. Handle Logo Upload using Storage::disk('public')
			if ($request->hasFile('logo')) {
				$file = $request->file('logo');

				// Format date string (e.g., 20260812)
				$date = now()->format('Y-m-d');

				// Construct custom filename: keychron_20260812.png
				$filename = $input['slug'] . "_" . $date . "." . $file->getClientOriginalExtension();

				// Store in 'storage/app/public/brands/keychron_20260812.png'
				// Saves 'brands/keychron_20260812.png' to $data['logo']
				$input['logo'] = $file->storeAs('brands', $filename, 'public');
			}

			// 3. Create Brand
			$brand = Brand::query()->create($input);

			// 4. Attach Category to Pivot Table (if category_id was selected)
			if (!empty($input['category_id'])) {
				$brand->categories()->attach($input['category_id']);
			}

			return redirect(route('inventory.brands.show', ['brand' => $brand]));
		}

		/**
		 * Display the specified resource.
		 */
		public function show(Brand $brand) {
			$brand->load('categories');
			return view('brands.show', ['brand' => $brand]);
		}

		/**
		 * Show the form for editing the specified resource.
		 */
		public function edit(Brand $brand) {

			// 1. Φέρνουμε ΟΛΕΣ τις γονικές κατηγορίες μαζί με τις υποκατηγορίες τους
			$parentCategories = Category::query()->whereNull('parent_id')
			                            ->with('children')
			                            ->orderBy('name')
			                            ->get();

			// 2. Στέλνουμε στο view το brand και την πλήρη λίστα κατηγοριών
			return view('brands.edit', compact('brand', 'parentCategories'));
		}

		/**
		 * Update the specified resource in storage.
		 */
		public function update(BrandUpdateRequest $request, Brand $brand): RedirectResponse {
			$input = $request->validated();

			if ($request->hasFile('logo')) {
				if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
					Storage::disk('public')->delete($brand->logo);
				}

				$file = $request->file('logo');

				// 1. Καθαρισμός του ονόματος του Brand για το αρχείο (slug)
				$brandSlug = Str::slug($input['name']);

				// 2. Λήψη της αυθεντικής επέκτασης (π.χ. jpg, png, webp)
				$extension = $file->getClientOriginalExtension();

				// 3. Σύνθεση του custom ονόματος: {brand_name}-{Y-m-d}.{extension}
				$filename = $brandSlug . "-" . today()->format('Y-m-d') . "-" . Str::lower(Str::random(4)) . "." . $extension;

				// 4. Αποθήκευση στον κατάλογο logos του public δίσκου με το νέο όνομα
				$input['logo'] = $file->storeAs('logos', $filename, 'public');
			} else {
				unset($input['logo']);
			}

			$brand->update($input);

			$categories = $input['categories'] ?? [];
			$brand->categories()->sync($categories);

			return redirect()
				->route('inventory.brands.index')
				->with('success', 'Το Brand ενημερώθηκε επιτυχώς!');
		}

		/**
		 * Remove the specified resource from storage.
		 */
		public function destroy(Brand $brand): RedirectResponse {
			// 1. Εκτέλεση Soft Delete (το αρχείο logo παραμένει στον δίσκο)
			$brand->delete();

			// 2. Επιστροφή στην index με μήνυμα επιτυχίας
			return redirect()
				->route('inventory.brands.index')
				->with('success', 'Το Brand αρχειοθετήθηκε/διαγράφηκε επιτυχώς!');
		}

		/*****************************
		 * @param  ProductSearchRequest  $request
		 * @param  Brand  $brand
		 *
		 * @return JsonResponse
		 * Used by purchases/{purchase}/edit to get related products to brand
		 */
		public function getProducts(ProductSearchRequest $request, Brand $brand) {
			$input = $request->validated();
//			dd($input);

			$itemProduct = Product::query()->find($input['product']);

			$products = $brand->products()->whereHas('inventories', function ($query) use ($input) {
				$query->where('warehouse_id', $input['warehouse']);
			})->where('category_id', $input['category_id'])->get()->map(function (Product $product) use ($itemProduct) {
				return "<option value='" . $product->id . "'" . ($product->id === $itemProduct->id ? 'selected' : '') . ">" . $product->name . "</option>";
			})->prepend("<option value=''>Επιλέξτε προϊόν&hellip;</option>");

			// Επιστροφή καθαρού HTML string
			return response()->json([
				'products'  => $products->join(''),
				'inventory' => Inventory::query()->whereHas('product', function ($query) use ($itemProduct) {
					$query->where('product_id', $itemProduct->id);
				})->get()->map(function (Inventory $inventory) {
					return [
						'manufacturing_date' => $inventory->manufacturing_date->format('Y-m-d'),
						'expiry_date'        => $inventory->expiry_date->format('Y-m-d'),
					];
				})->first(),
			]);
		}
	}
