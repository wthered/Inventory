<?php

	namespace App\Http\Controllers;

	use App\Http\Requests\Brands\BrandUpdateRequest;
	use App\Http\Requests\Products\ProductSearchRequest;
	use App\Models\Brand;
	use App\Models\Category;
	use App\Models\Product;
	use App\Services\Search\ProductSearchService;
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
		public function index() {
			// Φέρνουμε τα brands μαζί με τις κατηγορίες τους με ένα μόνο query
			$brands = Brand::with('categories')->paginate(10);
			$categories = Category::query()->whereNull('parent_id')->orderBy('name')->pluck('name', 'id');

			return view('brands.index', [
				'brands'     => $brands,
				'categories' => $categories,
			]);
		}

		/**
		 * Show the form for creating a new resource.
		 *
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
				$filename = $brandSlug."-".today()->format('Y-m-d')."-".Str::lower(Str::random(4)).".".$extension;

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

		public function getProducts(ProductSearchRequest $request) {
			$input = $request->validated();
			dd($input);

//			$products = Product::query()
//			                   ->when(!empty($input['category_id']), function ($query) use ($input) {
//				                   return $query->where('category_id', $input['category_id']);
//			                   })
//			                   ->when(!empty($input['brand_id']), function ($query) use ($input) {
//				                   return $query->where('brand_id', $input['brand_id']);
//			                   })
//			                   ->orderBy('name')
//			                   ->get();

			$products = $this->service->search($input);

			// 1. Μετατρέπουμε τα δεδομένα σε option tags
			$options = $products->map(function (Product $product) {
				return "<option value='".$product->id."'>".$product->name." (SKU: ".$product->sku.")</option>";
			});

			// 2. Αν θες ΠΑΝΤΑ ένα default prompt στην αρχή, το κάνεις prepend:
			$options->prepend("<option value='' selected>Πληκτρολογήστε για αναζήτηση ή επιλέξτε προϊόν&hellip;</option>");

			// 3. Επιστροφή καθαρού HTML string
			return $options->join('');
		}
	}
