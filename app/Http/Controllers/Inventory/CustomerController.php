<?php

	namespace App\Http\Controllers\Inventory;

	use App\Enums\Customers\CustomerType;
	use App\Enums\Financial\PaymentTerms;
	use App\Http\Controllers\Controller;
	use App\Http\Requests\Customers\CustomerStoreRequest;
	use App\Http\Requests\Customers\CustomerUpdateRequest;
	use App\Models\Country;
	use App\Models\Customer;
	use Illuminate\Contracts\View\Factory;
	use Illuminate\Contracts\View\View;
	use Illuminate\Http\RedirectResponse;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Gate;

	class CustomerController extends Controller {
		/**
		 * Display a listing of customers with searching, filtering, and pagination.
		 */
		public function index(Request $request): Factory|View|\Illuminate\View\View {
			// Check authorization using CustomerPolicy@viewAny ('customer.view')
			Gate::authorize('viewAny', Customer::class);

			$query = Customer::query();

			// Search by name, company, email, phone, or code
			if ($search = $request->input('search')) {
				$query->where(function ($q) use ($search) {
					$q->where('name', 'like', "%".$search."%")
					  ->orWhere('company_name', 'like', "%".$search."%")
					  ->orWhere('email', 'like', "%".$search."%")
					  ->orWhere('phone', 'like', "%".$search."%")
					  ->orWhere('code', 'like', "%".$search."%");
				});
			}

			// Filter by active status
			if ($request->filled('status')) {
				$query->where('is_active', $request->status === 'active');
			}

			$customers = $query->latest()
			                   ->paginate(25)
			                   ->withQueryString();

			return view('customers.index', compact('customers'));
		}

		/**
		 * Show the form for creating a new customer.
		 *
		 * @return \Illuminate\View\View
		 */
		public function create() {
			return view('customers.create', [
				'countries' => Country::query()->get(),
				'terms'     => PaymentTerms::cases(),
				'types'     => CustomerType::cases(),
			]);
		}

		/**
		 * Store a newly created customer in storage.
		 *
		 * @param  CustomerStoreRequest  $request
		 *
		 * @return RedirectResponse
		 */
		public function store(CustomerStoreRequest $request) {
//			dd($request->validated());
			$customer = Customer::query()->create($request->validated());

			return redirect()
				->route('inventory.customers.show', $customer->id)
				->with('success', 'Customer profile created successfully.');
		}

		/**
		 * Display the specified customer profile.
		 *
		 * @param  Customer  $customer
		 *
		 * @return \Illuminate\View\View
		 */
		public function show(Customer $customer) {
			// Eager load sales_orders with creator, ordered by order_date descending
			$customer->load([
				'sales' => function ($query) {
					$query->with('creator')->orderBy('order_date', 'desc');
				},
				'city',
				'country',
			]);

			return view('customers.show', ['customer' => $customer]);
		}

		/**
		 * Show the form for editing the specified customer.
		 *
		 * @param  Customer  $customer
		 *
		 * @return \Illuminate\View\View
		 */
		public function edit(Customer $customer) {
			return view('customers.edit', [
				'customer'  => $customer,
				'countries' => Country::query()->get(),
				'terms'     => PaymentTerms::cases(),
				'types'     => CustomerType::cases(),
			]);
		}

		/**
		 * Update the specified customer in storage.
		 *
		 * @param  CustomerUpdateRequest  $request
		 * @param  Customer               $customer
		 *
		 * @return RedirectResponse
		 */
		public function update(CustomerUpdateRequest $request, Customer $customer) {
//			dd($request->validated());
			$customer->update($request->validated());

			return redirect()
				->route('inventory.customers.show', $customer->id)
				->with('success', 'Customer profile updated successfully.');
		}

		/**
		 * Remove the specified resource from storage.
		 */
		public function destroy(string $id) {
			//
		}
	}
