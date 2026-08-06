<?php

	namespace App\Http\Controllers\Inventory;

	use App\Http\Controllers\Controller;
	use App\Http\Requests\Categories\CategoryFilterRequest;
	use App\Http\Requests\Categories\CategoryStoreRequest;
	use App\Http\Requests\Categories\CategoryUpdateRequest;
	use App\Http\Requests\Transactions\FilterBrandsRequest;
	use App\Models\Brand;
	use App\Models\Category;
	use App\Models\Product;


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
				->with('success', "Category '".$validated['name']."' was successfully created.");
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
				->with('success', "Category '".$category->name."' was successfully updated.");
		}

		/**
		 * Remove the specified resource from storage.
		 */
		public function destroy(Category $category) {
			$category->delete();
			return redirect()->route('inventory.categories.index');
		}

		public function filterBrands(FilterBrandsRequest $request, Category $category) {
			return $category->brands()
			                ->pluck('name', 'id')
			                ->map(function ($name, $id) {
				                return "<option value='".$id."'>".$name."</option>";
			                })
			                ->prepend("<option value='' selected>Επιλέξτε Μάρκα...</option>")
			                ->join('');

		}

		public function filter(CategoryFilterRequest $request, Category $category) {
			$category->load(['brands', 'children']);

//			dd($request->validated());

			// Extract child category IDs once to keep queries clean and efficient
			$children = $category->children()->pluck('id');

			return response()->json([
				'categories' => $category->children()->whereHas('products')->get()->map(function (Category $child) {
					return "<option value='".$child->id."'>".$child->name."</option>";
				})->prepend("<option value=''>Select Sub Category</option>")->join(''),

				'brands' => Brand::query()->whereHas('products', function ($query) use ($children) {
					$query->whereIn('category_id', $children);
				})->get()->map(function (Brand $brand) {
					return "<option value='".$brand->id."'>".$brand->name."</option>";
				})->prepend("<option value=''>All Brands</option>")->join(''),

				'products' => Product::query()->whereIn('category_id', $children)->orderBy('name')->get()
				                     ->map(function (Product $product) {
					                     return view('common.products.index_row', ['product' => $product])->render();
				                     })->join(''),
			]);
		}
	}
