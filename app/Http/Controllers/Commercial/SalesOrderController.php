<?php

	namespace App\Http\Controllers\Commercial;

	use App\DataTransferObjects\ProductDTO;
	use App\DataTransferObjects\Warehouse\LocationDTO;
	use App\Enums\Financial\PaymentStatus;
	use App\Enums\Sales\SalesOrderStatus;
	use App\Exceptions\Inventory\InsufficientStockException;
	use App\Exceptions\Inventory\ProductNotFoundInLocationException;
	use App\Http\Controllers\Controller;
	use App\Http\Requests\Sales\SaleOrderStoreRequest;
	use App\Http\Requests\Sales\SaleOrderUpdateRequest;
	use App\Models\Category;
	use App\Models\Customer;
	use App\Models\Inventories\InventoryAudit;
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
		public function index(Request $request) {
			// 1. Fetch filters from session (default to empty array)
			$filters = $request->session()->get('sales_filters', []);

			// 2. Query sales applying session filters dynamically
			$sales = SalesOrder::query()->with(['customer', 'warehouse'])
			                   ->when(!empty($filters['search']), function ($query) use ($filters) {
				                   $query->where(function ($q) use ($filters) {
					                   $q->where('order_number', 'like', "%".$filters['search']."%")
					                     ->orWhereHas('customer', function ($customerQuery) use ($filters) {
						                     $customerQuery->where('name', 'like', "%".$filters['search']."%");
					                     });
				                   });
			                   })->when(!empty($filters['status']), function ($query) use ($filters) {
					$query->where('status_id', $filters['status']);
				})->when(!empty($filters['payment_status']), function ($query) use ($filters) {
					$query->where('payment_status_id', $filters['payment_status']);
				})->when(!empty($filters['date']), function ($query) use ($filters) {
					$query->whereDate('order_date', $filters['date']);
				})->orderByDesc('created_at')->paginate($request->session()->get('per_page', 25));

			// 3. Return view passing active filters back to pre-fill search inputs
			return view('sales.index', [
				'sales'    => $sales,
				'payments' => PaymentStatus::class,
				'statuses' => SalesOrderStatus::class,
				'filters'  => $filters,
			]);
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
			// Fetch users with specific operational roles
			$employees = User::role([
				'sales_manager',
				'sales_supervisor',
				'sales_representative',
				'store_cashier'
			])
			                 ->with('account')
			                 ->get()
			                 ->sortBy(fn($user) => $user->account->full_name ?? '')
			                 ->pluck('account.full_name', 'id');
			return view('sales.create', [
				'customers'  => Customer::query()
				                        ->with('country')
				                        ->orderBy('name')
				                        ->get()
				                        ->groupBy(function ($customer) {
					                        return $customer->country->name ?? 'Χωρίς Χώρα';
				                        }),
				'warehouses' => Warehouse::query()->orderBy('name')->get(),
				'employees'  => $employees,
				'categories' => Category::query()->whereNull('parent_id')->orderBy('name')->pluck('name', 'id'),
			]);
		}

		/**
		 * Store a newly created sales order in storage.
		 */
		public function store(SaleOrderStoreRequest $request) {
			$validated = $request->validated();

			try {
				DB::beginTransaction();

				// 1. Calculate Financials per Product Tax Rate
				$subtotal = 0;
				$taxTotal = 0;
				$preparedItems = [];

				foreach ($validated['items'] as $item) {
					$product = new ProductDTO($item['product_id']);

					$qty = (float) $item['quantity_ordered'];
					$discountRate = (float) ($item['discount_rate'] ?? 0);
					$unitPrice = $product->selling_price;

					$lineSubtotal = $qty * $unitPrice;
					$discountedLineSubtotal = $lineSubtotal - ($lineSubtotal * ($discountRate / 100));
					$unitTax = ($unitPrice * (1 - ($discountRate / 100))) * ($product->tax_percent / 100);

					$subtotal += $discountedLineSubtotal;
					$taxTotal += $unitTax * $qty;

					// Cache item structure for creation in Step 3
					$preparedItems[] = [
						'product_id'       => $product->id,
						'quantity_ordered' => $qty,
						'unit_price'       => $unitPrice,
						'discount_rate'    => $discountRate,
						'discount_amount'  => ($discountRate * $unitPrice * $qty) / 100,
						'quantity_shipped' => 0,
					];
				}

				$orderDiscount = (float) ($validated['discount_amount'] ?? 0);
				$grandTotal = max(0, ($subtotal - $orderDiscount)) + $taxTotal;

				// 2. Create Header Record
				$salesOrder = SalesOrder::query()->create([
					'order_number'      => 'SALE-'.date('Y-m-d').'-'.Str::upper(Str::random(6)),
					'customer_id'       => $validated['customer_id'],
					'warehouse_id'      => $validated['warehouse_id'],
					'order_date'        => $validated['order_date'],
					'status_id'         => SalesOrderStatus::DRAFT->value,
					'payment_status_id' => PaymentStatus::UNPAID->value,
					'subtotal'          => $subtotal,
					'tax_amount'        => $taxTotal,
					'discount_amount'   => $orderDiscount,
					'grand_total'       => $grandTotal,
					'created_by'        => Auth::id() ?? 1,
					'notes'             => $validated['notes'] ?? null,
				]);

				// 3. Attach Line Items
				foreach ($preparedItems as $itemData) {
					$salesOrder->items()->create($itemData);
				}

				// 4. Log Audit Trail
				$salesOrder->history()->create([
					'action'      => 'order_created',
					'event'       => 'Sales Order Created',
					'description' => 'Initial sales order created with '.count($preparedItems).' items.',
					'user_id'     => Auth::id() ?? 1,
					'details'     => [
						'customer_id'  => $salesOrder->customer_id,
						'warehouse_id' => $salesOrder->warehouse_id,
						'grand_total'  => $salesOrder->grand_total,
						'status_id'    => $salesOrder->status_id->value ?? $salesOrder->status_id,
						'items_count'  => count($preparedItems),
					],
				]);

				DB::commit();

				return redirect()->route('inventory.sales.index')
				                 ->with('success', 'Sales order created successfully.');

			} catch (InsufficientStockException $e) {
				DB::rollBack();

				return redirect()->back()
				                 ->withInput()
				                 ->with('error', $e->getMessage());

			} catch (Throwable $e) {
				DB::rollBack();

				return redirect()->back()
				                 ->withInput()
				                 ->with('error', 'Failed to create sales order: '.$e->getMessage());
			}
		}

		/**
		 * Show the form for editing the specified sales order.
		 */
		public function edit(Request $request, SalesOrder $sale) {
			// Eager load items with their associated product so SKU and names render properly
			$sale->load(['items.product', 'creator', 'warehouse']);

//			dd($sale);

			return view('sales.edit', [
				'sale'       => $sale,
				'customers'  => Customer::query()->orderBy('name')->get(),
				'warehouses' => Warehouse::query()->orderBy('name')->get(),
				'employees'  => User::query()->orderBy('name')->get(),
				'categories' => Category::query()->whereNull('parent_id')->orderBy('name')->pluck('name', 'id'),
				'payments'   => PaymentStatus::class,
				'statuses'   => SalesOrderStatus::class,
			]);
		}

		/**
		 * Update the specified sales order in storage.
		 */
		public function update(SaleOrderUpdateRequest $request, SalesOrder $sale) {
			$validated = $request->validated();

			try {
				DB::beginTransaction();

				// 1. Process and Sync Line Items
				$keptItemIds = [];
				$tax_total = 0;
				$subtotal = 0;

				foreach ($validated['items'] as $item) {
					$product = new ProductDTO($item['product_id']);
					$inventory = $product->inventories->where('warehouse_id', $validated['warehouse_id'])->first();
					$location = LocationDTO::fromModel($inventory?->location);

					if ($location === null) {
						throw new ProductNotFoundInLocationException(
							product: $product,
							location: null,
							message: sprintf("Product '%s' is not assigned to any valid location in warehouse ID %s.", $product->name, $validated['warehouse_id'])
						);
					}

					$qty = (float) $item['quantity_ordered'];
					$discountRate = (float) ($item['discount_rate'] ?? 0);

					// Item Financials
					$lineSubtotal = $qty * $product->selling_price;
					$discountedLineSubtotal = $lineSubtotal - ($lineSubtotal * ($discountRate / 100));
					$unitTax = ($product->selling_price * (1 - ($discountRate / 100))) * ($product->tax_percent / 100);

					$subtotal += $discountedLineSubtotal;
					$tax_total += $unitTax * $qty;

					// Update existing line item or create new one cleanly
					$saleItem = $sale->items()->updateOrCreate(
						['id' => $item['id'] ?? null],
						[
							'product_id'       => $product->id,
							'location_id'      => $location->id,
							'quantity_ordered' => $qty,
							'unit_price'       => $product->selling_price,
							'discount_rate'    => $discountRate,
							'discount_amount'  => ($discountRate * $product->selling_price * $qty) / 100,
						]
					);

					$keptItemIds[] = $saleItem->id;
				}

				// Remove deleted items from the order
				$sale->items()->whereNotIn('id', $keptItemIds)->delete();

				// 2. Capture State Before Updating
				$oldGrandTotal = $sale->grand_total;
				$oldStatusId = $sale->status_id->value ?? $sale->status_id;

				$orderDiscount = (float) ($validated['discount_amount'] ?? 0);
				$grandTotal = max(0, ($subtotal - $orderDiscount)) + $tax_total;

				// 3. Update Header Totals
				$sale->update([
					'warehouse_id'      => $validated['warehouse_id'],
					'status_id'         => $validated['status_id'],
					'payment_status_id' => $validated['payment_status_id'],
					'order_date'        => $validated['order_date'],
					'shipping_date'     => $validated['shipping_date'] ?? null,
					'notes'             => $validated['notes'] ?? null,
					'subtotal'          => $subtotal,
					'tax_amount'        => $tax_total,
					'discount_amount'   => $orderDiscount,
					'grand_total'       => $grandTotal,
				]);

				// 4. Log Audit Trail
				$sale->history()->create([
					'action'      => 'order_updated',
					'event'       => 'Sales Order Updated',
					'description' => 'Updated order details, totals, and line items.',
					'user_id'     => Auth::id(),
					'details'     => [
						'old_grand_total' => $oldGrandTotal,
						'new_grand_total' => $grandTotal,
						'old_status_id'   => $oldStatusId,
						'new_status_id'   => $validated['status_id'],
						'items_count'     => count($keptItemIds),
					],
				]);

				DB::commit();

				return redirect()
					->route('inventory.sales.show', $sale)
					->with('success', 'Sales order updated successfully.');

			} catch (Throwable $e) {
				DB::rollBack();

				// Log the error into inventory_audits table using the polymorphic relation
				InventoryAudit::query()->create([
					'auditable_type' => get_class($sale),
					'auditable_id'   => $sale->id,
					'audit_number'   => 'AUD-'.today()->format('Y-m-d').'-W'.$validated['warehouse_id'].'-'.Str::upper(Str::random(4)),
					'warehouse_id'   => $validated['warehouse_id'],
					'created_by'     => Auth::id(),
					'notes'          => "Sales order update failed: ".$e->getMessage(),
					'metadata'       => [
						'exception_class' => get_class($e),
						'file'            => $e->getFile(),
						'line'            => $e->getLine(),
						'validated_data'  => $validated,
					],
				]);

				return redirect()
					->back()
					->withInput()
					->with('error', 'An error occurred while updating the order: '.$e->getMessage());
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
