<?php

	namespace App\Http\Controllers\Commercial;

	use App\Enums\Sales\SalesOrderStatus;
	use App\Http\Controllers\Controller;
	use App\Models\Customer;
	use App\Models\Sales\SalesOrder;
	use App\Models\User;
	use App\Models\Warehouse;
	use Exception;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Str;
	use Throwable;

	// Using User table for creating employee links as per migration

	class SalesOrderController extends Controller {
		/**
		 * Display a listing of the sales orders.
		 */
		public function index() {
			$sales = SalesOrder::with(['customer', 'warehouse'])
			                   ->orderBy('created_at', 'desc')
			                   ->paginate(15);

			return view('sales.index', compact('sales'));
		}

		/**
		 * Display the specified sales order.
		 */
		public function show(SalesOrder $sale) {
			// Φορτώνουμε τις σχέσεις για να μην έχουμε N+1 query ζητήματα στο view
			$sale->load(['customer', 'warehouse', 'items.product', 'history.user']);

			return view('sales.show', compact('sale'));
		}

		/**
		 * Show the form for creating a new sales order.
		 */
		public function create() {
			$customers = Customer::query()->orderBy('name')->get();
			$warehouses = Warehouse::query()->orderBy('name')->get();

			// Sorting users acting as registering staff / employees
			$employees = User::query()->orderBy('name')->get();

			return view('sales.create', compact('customers', 'warehouses', 'employees'));
		}

		/**
		 * Store a newly created sales order in storage.
		 */
		public function store(Request $request) {
			$validated = $request->validate([
				'customer_id'        => 'required|exists:customers,id',
				'warehouse_id'       => 'required|exists:warehouses,id',
				'order_date'         => 'required|date',
				'notes'              => 'nullable|string',
				'items'              => 'required|array|min:1',
				'items.*.product_id' => 'required|exists:products,id',
				'items.*.quantity'   => 'required|integer|min:1',
				'items.*.unit_price' => 'required|numeric|min:0',
			]);

			try {
				DB::beginTransaction();

				// Calculate aggregate values safely before persisting
				$subtotal = collect($validated['items'])->sum(function ($item) {
					return $item['quantity'] * $item['unit_price'];
				});

				$salesOrder = SalesOrder::query()->create([
					'order_number'      => 'SALE-'.date('Y-m-d').'-'.Str::upper(Str::random(6)),
					'customer_id'       => $validated['customer_id'],
					'warehouse_id'      => $validated['warehouse_id'],
					'order_date'        => $validated['order_date'],
					'status_id'         => SalesOrderStatus::DRAFT->value, // Explicit structural Enum initialization
					'payment_status_id' => 1, // Default baseline status flag
					'subtotal'          => $subtotal,
					'tax_amount'        => 0, // Calculations can be modified here
					'discount_amount'   => 0,
					'grand_total'       => $subtotal,
					'created_by'        => Auth::id() ?? 1, // Fallback safe authentication binding
					'notes'             => $validated['notes'] ?? null,
				]);

				foreach ($validated['items'] as $item) {
					$salesOrder->items()->create([
						'product_id'       => $item['product_id'],
						'quantity_ordered' => $item['quantity'],
						'unit_price'       => $item['unit_price'],
						'quantity_shipped' => 0,
					]);
				}

				DB::commit();

				return redirect()->route('inventory.sales.index')
				                 ->with('success', 'Sales order created successfully.');

			} catch (Exception $e) {
				DB::rollBack();
				return redirect()->back()
				                 ->withInput()
				                 ->with('error', 'Failed to create sales order: '.$e->getMessage());
			} catch (Throwable $e) {
				dd($e->getMessage());
			}
		}

		/**
		 * Show the form for editing the specified sales order.
		 */
		public function edit(SalesOrder $sale) {
			$customers = Customer::query()->orderBy('name')->get();
			$warehouses = Warehouse::query()->orderBy('name')->get();
			$employees = User::query()->orderBy('name')->get();

			$sale->load(['items', 'creator', 'warehouse']);

			return view('sales.edit', compact('sale', 'customers', 'warehouses', 'employees'));
		}

		/**
		 * Update the specified sales order in storage.
		 */
		public function update(Request $request, SalesOrder $sale) {
			$validated = $request->validate([
				'warehouse_id' => 'required|exists:warehouses,id',
				'order_date'   => 'required|date',
				'notes'        => 'nullable|string',
			]);

			try {
				$sale->update([
					'warehouse_id' => $validated['warehouse_id'],
					'order_date'   => $validated['order_date'],
					'notes'        => $validated['notes'] ?? $sale->notes,
				]);

				return redirect()->route('inventory.sales.index')
				                 ->with('success', 'Sales order updated successfully.');

			} catch (Exception $e) {
				return redirect()->back()
				                 ->withInput()
				                 ->with('error', 'Failed to update sales order.');
			}
		}

		/**
		 * Remove the specified sales order from storage.
		 */
		public function destroy(SalesOrder $sale) {
			try {
				DB::transaction(function () use ($sale) {
					$sale->items()->delete();
					$sale->delete();
				});

				return redirect()->route('inventory.sales.index')
				                 ->with('success', 'Sales order deleted successfully.');
			} catch (Exception $e) {
				return redirect()->back()
				                 ->with('error', 'Failed to delete sales order.');
			} catch (Throwable $e) {
				dd($e->getMessage());
			}
		}
	}