<?php

	namespace App\Http\Controllers;

	use App\Http\Requests\Countries\CountryStatesRequest;
	use App\Models\Country;
	use Illuminate\Http\JsonResponse;
	use Illuminate\Http\Request;

	class CountryController extends Controller {
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

		/**
		 * Get active cities for the given country.
		 */
		public function cities(Country $country): JsonResponse {
			$cities = $country->cities()
			                  ->where('is_active', true)
			                  ->orderBy('name')
			                  ->pluck('name', 'id')
			                  ->map(function ($name, $index) {
				                  return "<option value='".$index."'>".$name."</option>";
			                  })->prepend("<option value=''>Select City</option>");

			return response()->json(['options' => $cities->join('')]);
		}

		public function states(CountryStatesRequest $request, Country $country): JsonResponse {
			$input = $request->validated();

			$states = $country->cities()
			                  ->whereNotNull('state')
			                  ->whereRaw('LOWER(state) LIKE ?', ["%".$input['query']."%"])
			                  ->distinct()
			                  ->pluck('state')
			                  ->map(function ($state) {
				                  return "<div class='autocomplete-item'>".$state."</div>";
			                  });

			return response()->json($states->join(''));
		}
	}
