<?php

	namespace App\Http\Controllers\Inventory;

	use App\Http\Controllers\Controller;
	use App\Http\Requests\Suppliers\SupplierUpdateRequest;
	use App\Models\Supplier;
	use Illuminate\Contracts\View\Factory;
	use Illuminate\Contracts\View\View;
	use Illuminate\Http\Request;

	class SupplierController extends Controller {

		/**
		 * Display a listing of the suppliers.
		 */
		public function index(Request $request
		): Factory|View|\Illuminate\View\View {
			$search = $request->input('search');

			$suppliers = Supplier::query()
			                     ->withCount('products')
			                     ->when($search, function ($query) use ($search) {
				                     $query->where(function ($q) use ($search) {
					                     $q->where('name', 'like', "%{$search}%")
					                       ->orWhere('company_name', 'like', "%{$search}%")
					                       ->orWhere('code', 'like', "%{$search}%")
					                       ->orWhere('email', 'like', "%{$search}%")
					                       ->orWhere('phone', 'like', "%{$search}%")
					                       ->orWhere('contact_person', 'like', "%{$search}%");
				                     });
			                     })
			                     ->orderBy('name', 'asc')
			                     ->paginate(15)
			                     ->withQueryString();

			return view('suppliers.index', compact('suppliers', 'search'));
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
		 * Display the specified supplier details and associated products.
		 */
		public function show(Supplier $supplier
		): Factory|View|\Illuminate\View\View {
			// Load products with pivot details and total inventory across all warehouses
			$supplier->load([
				'products' => function ($query) {
					$query->withCount('inventories');
				}
			]);

			return view('suppliers.show', compact('supplier'));
		}

		/**
		 * Show the form for editing the specified supplier.
		 */
		public function edit(Supplier $supplier
		): Factory|View|\Illuminate\View\View {
			return view('suppliers.edit', compact('supplier'));
		}

		/**
		 * Update the specified supplier in storage.
		 */
		public function update(SupplierUpdateRequest $request, Supplier $supplier): \Illuminate\Http\RedirectResponse {
			$validated = $request->validated();

			// Ensure is_active boolean is correctly handled from request
			$validated['is_active'] = $request->boolean('is_active');

			$supplier->update($validated);

			return redirect()
				->route('inventory.suppliers.show', $supplier->id)
				->with('success', 'Supplier updated successfully.');
		}

		/**
		 * Remove the specified resource from storage.
		 */
		public function destroy(string $id) {
			//
		}
	}
