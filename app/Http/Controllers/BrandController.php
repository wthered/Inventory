<?php

namespace App\Http\Controllers;

use App\Http\Requests\Products\ProductSearchRequest;
use App\Models\Product;
use App\Services\Search\ProductSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BrandController extends Controller {

	private ProductSearchService $service;

	public function __construct(ProductSearchService $searchService) {
		$this->service = $searchService;
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index() {
		//
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

	public function getProducts(ProductSearchRequest $request) {
		$input = $request->validated();

		$products = Product::query()
		                   ->when(!empty($input['category_id']), function ($query) use ($input) {
			                   return $query->where('category_id', $input['category_id']);
		                   })
		                   ->when(!empty($input['brand_id']), function ($query) use ($input) {
			                   return $query->where('brand_id', $input['brand_id']);
		                   })
		                   ->orderBy('name')
		                   ->get();

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
