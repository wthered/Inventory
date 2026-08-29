<?php

	namespace App\Http\Controllers;

	use App\Http\Requests\Brands\BrandListFilterRequest;
	use App\Http\Requests\Customers\CustomerIndexFilterRequest;
	use App\Http\Requests\Filters\Employees\EmployeeAttendancesFilterRequest;
	use App\Http\Requests\Filters\Employees\EmployeeLeavesFilterRequest;
	use App\Http\Requests\HumanResources\Employees\EmployeeFilterRequest;
	use App\Http\Requests\Products\FilterProductsRequest;
	use App\Http\Requests\Sales\SalesIndexFilterRequest;
	use App\Http\Requests\Stocks\StockAdjustments\FilterAdjustmentsRequest;
	use App\Models\Brand;
	use App\Models\Customer;
	use App\Models\HumanResources\Employee;
	use App\Models\Product;
	use App\Models\Sales\SalesOrder;
	use App\Models\StockAdjustment;
	use App\Models\Supplier;
	use App\Services\Filters\ProductFilterService;
	use Illuminate\Http\JsonResponse;
	use Illuminate\Pagination\Paginator;
	use Throwable;

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
			                   ->values()
			                   ->pluck('name', 'id')
			                   ->map(function ($name, $index) {
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
		 * @return JsonResponse
		 */
//		public function suppliersByBrand(FilterSuppliersByBrandRequest $request, Brand $brand): JsonResponse {
//			dd($request->validated());
//			$suppliersOptions = Supplier::query()->whereHas('products', function ($query) use ($brand) {
//				$query->where('brand_id', $brand->id);
//			})->orderBy('name')->get()->map(function (Supplier $supplier) {
//				return "<option value='".$supplier->id."'>".$supplier->name."</option>";
//			})->prepend("<option value=''>All Suppliers</option>");
//
//			return response()->json([
//				'success'   => $suppliersOptions->isNotEmpty(),
//				'suppliers' => $suppliersOptions->join(''),
//			]);
//		}

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

		public function brandProducts(BrandListFilterRequest $request, Brand $brand) {
			$input = $request->validated();
//			dd($input);
			$products = $brand->products()->when($request->filled('category_id'), function ($query) use ($input) {
				$query->whereHas('category', function ($q) use ($input) {
					$q->where('id', $input['category_id']);
				});
			})->when($request->filled('search'), function ($query) use ($input) {
				$query->where(function ($q) use ($input) {
					$q->where('name', 'LIKE', "%".$input['search']."%")
					  ->orWhere('sku', 'LIKE', "%".$input['search']."%");
				});
			})->when($request->filled('warehouse_id'), function ($query) use ($input) {
				$query->whereHas('inventories', function ($q) use ($input) {
					$q->where('warehouse_id', $input['warehouse_id']);
				});
			})->get()->map(function (Product $product) {
				return view('partials.brands.product_card', ['product' => $product])->render();
			});
			return response()->json([
				'success' => $products->isNotEmpty(),
				'results' => $products->implode('')
			], $products->isNotEmpty() ? 200 : 404);
		}

		/**
		 * Filters Sale Orders into Index page when a filter value changes
		 *
		 * @throws Throwable
		 */
		public function sales(SalesIndexFilterRequest $request): JsonResponse {
			$input = $request->validated();

			// 1. Store active filter inputs in the session
			$request->session()->put('sales_filters', $input);

			$sales = SalesOrder::query()
			                   ->when($request->filled('search'), function ($query) use ($input) {
				                   $query->where(function ($q) use ($input) {
					                   $q->where('order_number', 'like', "%".$input['search']."%")
					                     ->orWhereHas('customer', function ($customerQuery) use ($input) {
						                     $customerQuery->where('name', 'like', "%".$input['search']."%");
					                     });
				                   });
			                   })->when($request->filled('status'), function ($query) use ($input) {
					$query->where('status_id', $input['status']);
				})->when($request->filled('payment_status'), function ($query) use ($input) {
					$query->where('payment_status_id', $input['payment_status']);
				})->when($request->filled('date'), function ($query) use ($input) {
					$query->whereDate('order_date', $input['date']);
				})->latest()->paginate($request->session()->get('per_page', 25))
			                   ->setPath(route('inventory.sales.index'));

			// Render rows partial
			$sales->through(fn(SalesOrder $sale) => view('partials.sales.filter_row', ['sale' => $sale])->render());

			return response()->json([
				'rows'       => $sales->getCollection()->implode(''),
				'pagination' => view('partials.sales.pagination', ['sales' => $sales])->render(),
			]);
		}

		/**
		 * @throws Throwable
		 */
		public function customers(CustomerIndexFilterRequest $request) {
			$input = $request->validated();

			// 1. Initialize the query builder on the Customer model
			$query = Customer::query();

			// 2. Filter by search input
			if (!empty($input['search'])) {
				$search = $input['search'];
				$query->where(function ($q) use ($search) {
					$q->where('name', 'like', "%{$search}%")
					  ->orWhere('email', 'like', "%{$search}%")
					  ->orWhere('phone', 'like', "%{$search}%")
					  ->orWhere('code', 'like', "%{$search}%");
				});
			}

			// 3. Filter by status input
			if (!empty($input['status'])) {
				$query->where('is_active', $input['status'] === 'active');
			}

			// 4. Paginate results
			$customers = $query->paginate($request->session()->get('per_page', 10))->withPath(route('inventory.customers.index'));

			// 5. Return JSON response for AJAX
			return response()->json([
				'html'       => view('partials.customers.index_rows', compact('customers'))->render(),
				'pagination' => $customers->hasPages() ? $customers->links('pagination::simple')->render() : ''
			]);
		}

		/**
		 * @throws Throwable
		 */
		public function filter(EmployeeFilterRequest $request) {
			$query = Employee::with('department');

			if ($request->filled('search')) {
				$search = $request->input('search');
				$query->where(function ($q) use ($search) {
					$q->where('name', 'like', "%{$search}%")
					  ->orWhere('surname', 'like', "%{$search}%")
					  ->orWhere('email', 'like', "%{$search}%")
					  ->orWhere('phone', 'like', "%{$search}%")
					  ->orWhere('employee_number', 'like', "%{$search}%");
				});
			}

			if ($request->filled('department_id')) {
				$query->where('department_id', $request->validated('department_id'));
			}

			// Sort employees by ID
			$employees = $query->orderBy('id')->paginate($request->session()->get('per_page', 10))->withPath(route('hr.employees.index'));

			return response()->json([
				'html'       => view('partials.employees.index_rows', compact('employees'))->render(),
				'pagination' => $employees->hasPages() ? $employees->links('pagination::simple')->render() : ''
			]);
		}

		public function employees(EmployeeFilterRequest $request) {
			$input = $request->validated();
			dd($input);
		}

		public function attendances(EmployeeAttendancesFilterRequest $request, Employee $employee) {
			$input = $request->validated();
			$page = $request->input('att_page', 1);

			Paginator::currentPageResolver(function () use ($page) {
				return $page;
			});

			$attendances = $employee->attendances()
			                        ->when($request->filled('date_start'), function ($query) use ($input) {
				                        $query->whereDate('work_date', '>', $input['date_start']);
			                        })->when($request->filled('date_final'), function ($query) use ($input) {
					$query->whereDate('work_date', '<', $input['date_final']);
				})->when($request->filled('status'), function ($query) use ($input) {
					$query->where('status', $input['status']);
				})->paginate($request->session()->get('per_page'), ['*'], 'att_page');

			return response()->json([
				'attendances' => view('partials.employees.attendance_rows', [
					'attendances' => $attendances,
				])->render(),
			]);
		}

		public function leaves(EmployeeLeavesFilterRequest $request, Employee $employee) {
			$input = $request->validated();
			$leaves = $employee->leaveRequests()->when($request->filled('leave_date_start'), function ($query) use (
				$input
			) {
				$query->whereDate('start_date', '>', $input['leave_date_start']);
			})->when($request->filled('leave_date_final'), function ($query) use ($input) {
				$query->whereDate('end_date', '<', $input['leave_date_final']);
			})->when($request->filled('status'), function ($query) use ($input) {
				$query->where('status', $input['leave_status']);
			})->when($request->filled('leave_type'), function ($query) use ($input) {
				$query->where('leave_type', $input['leave_type']);
			})->paginate($request->session()->get('per_page'), ['*'], 'leave_page');

			return response()->json([
				'leaves' => view('partials.employees.leave_rows', ['leaveRequests' => $leaves])->render(),
			]);
		}
	}
