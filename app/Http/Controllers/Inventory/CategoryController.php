<?php

	namespace App\Http\Controllers\Inventory;

	use App\Http\Controllers\Controller;
	use App\Http\Requests\Brands\BrandListFilterRequest;
	use App\Http\Requests\Categories\CategoryFilterRequest;
	use App\Http\Requests\Categories\CategoryStoreRequest;
	use App\Http\Requests\Categories\CategoryUpdateRequest;
	use App\Http\Requests\Transactions\FilterBrandsRequest;
	use App\Models\Brand;
	use App\Models\Category;
	use App\Models\Product;
	use Illuminate\Http\JsonResponse;


	class CategoryController extends Controller {

		/**
		 * Display a listing of the resource.
		 */
		public function index() {
			// Φέρνουμε μόνο τις κατηγορίες που δεν έχουν parent_id[cite: 1]
			$categories = Category::query()
			                      ->whereNull('parent_id')
			                      ->withCount('products') // Count for the top-level parent categories
			                      ->with([
					'children' => function ($query) {
						$query->withCount('products'); // Count for each subcategory
					}
				])->orderBy('name')->paginate(10);

			return view('categories.index', compact('categories'));
		}

		/**
		 * Show the form for creating a new resource.
		 */
		public function create() {
			// Pull only top-level root categories to pass down as eligible parent selections[cite: 21]
			$parentCategories = Category::query()->whereNull('parent_id')->get();

			return view('categories.create', compact('parentCategories'));
		}

		/**
		 * Store a newly created resource in storage.
		 */
		public function store(CategoryStoreRequest $request) {
			// Safely pull pre-sanitized and validated field attributes
			$validated = $request->validated();

			// Mass-assign inputs. CategoryObserver catches this execution line
			// and calculates the next unique 'sort_order' value instantly.
			$category = Category::query()->create($validated);

			// Redirect directly to the newly created inventory category detail profile[cite: 21]
			return redirect()
				->route('inventory.categories.show', $category->id)
				->with('success', "Category '" . $validated['name'] . "' was successfully created.");
		}

		/**
		 * Display the specified resource.
		 */
		public function show(Category $category) {
			// Fetch the category with its children (subcategories)
			$category = $category->load(['children']);

			// Paginate the products belonging specifically to this category
			$products = $category->products()
			                     ->orderBy('name')
			                     ->paginate(10);

			return view('categories.show', compact('category', 'products'));
		}

		/**
		 * Show the form for editing the specified resource.
		 */
		public function edit(Category $category) {
			return view('categories.edit', [
				'category'         => $category,
				'parentCategories' => Category::query()->whereNull('parent_id')->get(),
				'brands'           => Brand::query()->with(['categories'])->orderBy('name')->get(),
				'linkedBrandIds'   => $category->brands()->pluck('brands.id')->toArray(),
			]);
		}

		/**
		 * Update the specified resource in storage.
		 */
		public function update(CategoryUpdateRequest $request, Category $category) {
			$validated = $request->validated();

			// Persist standard category fields
			$category->update($validated);

			// Synchronize the many-to-many relationship pivot table entries safely
			// If no brands are selected, an empty array will clear them all out
			$category->brands()->sync($request->input('brands', []));

			return redirect()
				->route('inventory.categories.show', $category->id)
				->with('success', "Category '" . $category->name . "' was successfully updated.");
		}

		/**
		 * Remove the specified resource from storage.
		 */
		public function destroy(Category $category) {
			$category->delete();
			return redirect()->route('inventory.categories.index');
		}

		public function filterBrands(FilterBrandsRequest $request, Category $category) {
			$input = $request->validated();

			// Safely check if product input exists before querying
			$product = !empty($input['product']) ? $category->products()->where('id', $input['product'])->first() : null;

			$brands = $category->brands()->whereHas('products', function ($query) use ($input) {
				$query->whereHas('inventories', function ($q) use ($input) {
					$q->where('warehouse_id', $input['warehouse']);
				});
			})->pluck('name', 'id')->map(function ($name, $id) use ($product) {
				// Safe check using optional chaining or ternary
				$isSelected = $product && $product->brand_id == $id ? ' selected' : '';
				return "<option value='" . $id . "'" . $isSelected . ">" . $name . "</option>";
			})->prepend("<option value='' selected>Επιλέξτε Μάρκα...</option>")->join('');

			return response()->json(['options' => $brands]);
		}

		public function filter(CategoryFilterRequest $request, Category $category): JsonResponse {
			$input = $request->validated();
//			dd($input);

			// Extract child category IDs once to keep queries clean and efficient
			$children = Category::query()->where('parent_id', $category->id)
			                    ->whereHas('products', function ($query) use ($input) {
				                    $query->whereHas('inventories', function ($q) use ($input) {
					                    $q->where('warehouse_id', $input['warehouse']);
				                    });
			                    })->pluck('id');

//			dd($children);
			$itemProduct = Product::query()->find($input['product']);
//			dd($itemProduct);

			return response()->json([
				'categories' => Category::query()->whereIn('id', $children)->get()->map(function (Category $child) use ($itemProduct) {
					return "<option value='" . $child->id . "'" . ($child->id == $itemProduct->category_id ? 'selected' : '') . ">" . $child->name . "</option>";
				})->prepend("<option value=''>Select Sub Category</option>")->join(''),

				'brands' => Brand::query()->whereHas('products', function ($query) use ($children) {
					$query->whereIn('category_id', $children);
				})->get()->map(function (Brand $brand) use ($itemProduct) {
					return "<option value='" . $brand->id . "'" . ($brand->id === $itemProduct->brand_id ? 'selected' : '') . ">" . $brand->name . "</option>";
				})->prepend("<option value=''>All Brands</option>")->join(''),

				'products' => Product::query()->whereHas('category', function ($query) use ($children) {
					$query->whereIn('id', $children);
				})->whereHas('inventories', function ($q) use ($input) {
					$q->where('warehouse_id', $input['warehouse']);
				})->orderBy('name')->get()->map(function (Product $product) {
					return view('common.products.index_row', ['product' => $product])->render();
				})->join(''),
			]);
		}


		public function filterBrandList(BrandListFilterRequest $request, Category $category) {
			$input = $request->validated();
			$term  = $input['search'] ?? null;

			$brands = $category->brands()
			                   ->with('categories')
			                   ->when($term, function ($query) use ($term) {
				                   $query->where('name', 'LIKE', '%' . $term . '%');
			                   })
			                   ->orderBy('name')
			                   ->paginate($request->session()->get('per_page', 15));

			$rows = $brands->getCollection()->map(function ($brand) {
				return view('common.brands.index_row', ['brand' => $brand])->render();
			});

			return response()->json([
				'success'    => $rows->isNotEmpty(),
				'rows'       => $rows->implode(''),
				'pagination' => $brands->hasPages() ? $brands->withQueryString()->links('pagination::simple')->render() : '',
			]);
		}
	}
