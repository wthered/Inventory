<?php

	namespace App\Http\Controllers\Inventory;

	use App\Http\Controllers\Controller;
	use App\Http\Requests\Transactions\FilterBrandsRequest;
	use App\Models\Brand;
	use App\Models\Category;
	use Illuminate\Http\JsonResponse;
	use Illuminate\Http\Request;
	use Illuminate\Support\Collection;


	class CategoryController extends Controller {
		/**
		 * Display a listing of the resource.
		 */
		public function index() {
			//
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
		public function show(string $id) {
			//
		}

		/**
		 * Show the form for editing the specified resource.
		 */
		public function edit(string $id) {
			//
		}

		/**
		 * Update the specified resource in storage.
		 */
		public function update(Request $request, string $id) {
			//
		}

		/**
		 * Remove the specified resource from storage.
		 */
		public function destroy(string $id) {
			//
		}

		public function filterBrands(FilterBrandsRequest $request) {
			$input = $request->validated();
			$options = Collection::make(["<option value='' selected>Επιλέξτε Μάρκα...</option>"]);

			Category::find($input['category_id'])->brands()->pluck('name', 'id')->each(function ($name, $id) use (&$options) {
				$options->push("<option value='".$id."'>".$name."</option>");
			});

			return $options->join('');
		}
	}
