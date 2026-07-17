<?php

	namespace App\Http\Controllers;

	use App\Http\Requests\Products\FilterProductsRequest;
	use App\Services\Filters\ProductFilterService;
	use Illuminate\Http\JsonResponse;
	use Illuminate\Http\Request;

	class FilterController extends Controller {
		public function products(FilterProductsRequest $request, ProductFilterService $service): JsonResponse {
			// Το $request->validated() τώρα επιστρέφει το array από το passedValidation
			$products = $service->filter($request->validated());

			// Μετατροπή των μοντέλων σε HTML rows
			$html = $products->getCollection()->map(function ($product) {
				return view('common.products.index_row', ['product' => $product])->render();
			})->implode('');

			return response()->json([
				'success' => true,
				'products' => $html,
				'pagination' => (string) $products->links('vendor.pagination.default_custom')
			]);
		}

		public function getFilters(Request $request) {
			dd($request->all());
		}
	}
