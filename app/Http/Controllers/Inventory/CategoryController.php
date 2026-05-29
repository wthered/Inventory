<?php

	namespace App\Http\Controllers\Inventory;

	use App\Http\Controllers\Controller;
	use App\Models\Brand;
	use App\Models\Category;
	use Illuminate\Http\JsonResponse;
	use Illuminate\Http\Request;


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

		/****
		 * Get all the Level 2 Categories
		 */
		public function filter(Request $request, Category $category): JsonResponse {
			$children = Category::query()->where(['parent_id' => $category->id])->get()->map(function ($child) {
				return "<option value='" . $child->id . "'>" . $child->name . "</option>";
			});

			$brands = Brand::query()->where('category_id', $category->id)->get()->map(function ($brand) {
				return "<option value='" . $brand->id . "'>" . $brand->name . "</option>";
			});

			return response()->json([
				'success'  => true,
				'children' => "<option value=''>-- Select ".$category->name." Subcategory --</option>" . $children->implode(''),
				'brands'   => "<option value = ''>-- Select Brand --</option>" . $brands->implode(''),
			]);
		}
	}
