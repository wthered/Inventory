<?php

	namespace App\Http\Controllers;

	use App\Http\Requests\Filters\FilterSuppliersByBrandRequest;
	use App\Http\Requests\Products\FilterProductsRequest;
	use App\Http\Requests\Stocks\StockAdjustments\FilterAdjustmentsRequest;
	use App\Models\Brand;
	use App\Models\StockAdjustment;
	use App\Models\Supplier;
	use App\Services\Filters\ProductFilterService;
	use Illuminate\Http\JsonResponse;

	class FilterController extends Controller {

		/******************
		 * Used by Products Index
		 *
		 * @param  FilterProductsRequest  $request
		 * @param  ProductFilterService   $service
		 *
		 * @return JsonResponse
		 */
		public function products(FilterProductsRequest $request, ProductFilterService $service): JsonResponse {
			// Filtered Collectio of Products (Collection of Product Models)
			$filtered = $service->filter($request->validated());

			$products = $filtered->getCollection()->map(function ($product) {
				return view('common.products.index_row', ['product' => $product])->render();
			});

//			dd($filtered->getCollection());

			// Returns a collection of unique Brand models (excluding nulls)
			$brands = $filtered->getCollection()->pluck('brand')->filter()->unique('id')
			                   ->values()->pluck('name', 'id')->map(function ($name, $index) {
					return "<option value='".$index."'>".$name."</option>";
				})->prepend("<option value=''>All Brands</option>");

			$suppliers = Supplier::query()->whereHas('products', function ($query) use ($filtered) {
				return $query->whereIn('id', $filtered->getCollection()->pluck('id')->values());
			})->pluck('name', 'id')->map(function ($name, $index) {
				return "<option value='".$index."'>".$name."</option>";
			})->prepend("<option value=''>All Suppliers</option>");

			return response()->json([
				'success'    => $products->isNotEmpty(),
				'products'   => $products->implode(''),
				'brands'     => $brands->implode(''),
				'suppliers'  => $suppliers->implode(','),
				'pagination' => $filtered->hasPages() ? $filtered->withQueryString()->links('pagination::simple')->render() : "&nbsp;",
			]);
		}

		/**
		 * Fetch suppliers that have products of the given brand.
		 *
		 * @param  FilterSuppliersByBrandRequest  $request
		 * @param  Brand                          $brand
		 *
		 * @return JsonResponse
		 */
		public function suppliersByBrand(FilterSuppliersByBrandRequest $request, Brand $brand): JsonResponse {
//			dd($request->validated());
			$suppliersOptions = Supplier::query()->whereHas('products', function ($query) use ($brand) {
				$query->where('brand_id', $brand->id);
			})->orderBy('name')->get()->map(function (Supplier $supplier) {
				return "<option value='".$supplier->id."'>".$supplier->name."</option>";
			})->prepend("<option value=''>All Suppliers</option>")->join('');

			return response()->json([
				'success'   => !empty($suppliersOptions),
				'suppliers' => $suppliersOptions,
			]);
		}

		public function adjustments(FilterAdjustmentsRequest $request) {
			$input = $request->validated();
//			dd($input['product']);

			$adjustments = StockAdjustment::query()->with([
				'items.product', 'creator'
			])->when(!empty($input['product'] ?? null), function ($query) use ($input) {
				$query->whereHas('items.product', function ($q) use ($input) {
					$q->where(function ($subQuery) use ($input) {
						$subQuery->where('sku', 'like', "%".$input['product']."%")
						         ->orWhere('name', 'like', "%".$input['product']."%");
					});
				});
			})->when(!empty($input['reason'] ?? null), function ($query) use ($input) {
				// Checks line item reason or header reason based on your schema
				$query->whereHas('items', function ($q) use ($input) {
					$q->where('reason', $input['reason']);
				});
			})->when(!empty($input['date'] ?? null), function ($query) use ($input) {
				$query->whereDate('adjustment_date', $input['date']);
			})->paginate(25);

			return response()->json([
				'success'     => $adjustments->isNotEmpty(),
				'adjustments' => $adjustments->getCollection()->map(function ($adjustment) {
					return view('common.adjustments.index_row', ['adjustment' => $adjustment])->render();
				}),
				'pagination'  => $adjustments->hasPages() ? $adjustments->withQueryString()->links('pagination::simple')->render() : '',
			]);
		}
	}
