<?php

	namespace App\Http\Controllers\Inventory;

	use App\Http\Controllers\Controller;
	use App\Http\Requests\Inventories\AdjustInventoryRequest;
	use App\Models\Inventory;
	use App\Models\Product;
	use App\Models\StockAdjustment;
	use Exception;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Throwable;

	class AdjustmentController extends Controller {

		public function index(Request $request) {
			// Basic usage
			$adjustments = StockAdjustment::with([
				'product',
				'location',
				'adjuster'
			])->latest()->paginate(20);

			return view('adjustments.index', compact('adjustments'));
		}

		public function getIncreases() {

			// Using trait scope
			$increases = StockAdjustment::increases()->with('product')->orderBy('created_at', 'desc')->paginate(15);

			return view('adjustments.increases', compact('increases'));
		}

		public function getPendingApprovals() {
			$pending = StockAdjustment::pendingApproval()
				->with([
					'product',
					'adjuster'
				])
				->recent(30)
				->get();

			return response()->json($pending);
		}

		public function getLargeAdjustments() {
			// Chain multiple scopes
			$largeAdjustments = StockAdjustment::largeAdjustments(100)->withCost()->thisMonth()->with([
				'product',
				'warehouse'
			])->get();

			return view('adjustments.large', compact('largeAdjustments'));
		}

		public function getProductAdjustments($productId) {
			$adjustments = StockAdjustment::forProduct($productId)
				->with([
					'location',
					'adjuster'
				])
				->betweenDates('2024-01-01', '2024-12-31')
				->orderBy('created_at', 'desc')
				->paginate(20);

			return view('products.adjustments', compact('adjustments'));
		}

		public function getDamagedStockReport() {
			$adjustment = new StockAdjustment();

			// Complex query using multiple scopes
			$damagedStock = $adjustment
				->scopeByReason(StockAdjustment::query(), 'damaged')
				->decreases()
				->with([
					'product',
					'location',
					'adjuster'
				])
				->betweenDates(now()->startOfMonth(), now()->endOfMonth())
				->orderBy('quantity', 'desc')
				->get();

			$totalLoss = $damagedStock->sum('total_cost');

			return view('reports.damaged-stock', compact('damagedStock', 'totalLoss'));
		}

		public function getUserAdjustments($userId) {
			$adjustments = StockAdjustment::byAdjuster($userId)
				->recent(90)
				->with([
					'product',
					'location'
				])
				->paginate(25);

			return view('users.adjustments', compact('adjustments'));
		}

		public function getWarehouseAdjustments($warehouseId) {
			$adjustments = StockAdjustment::inWarehouse($warehouseId)
				->with([
					'product',
					'location',
					'adjuster'
				])
				->latest()
				->paginate(20);

			$summary = [
				'increases'      => StockAdjustment::inWarehouse($warehouseId)
					->increases()
					->count(),
				'decreases'      => StockAdjustment::inWarehouse($warehouseId)
					->decreases()
					->count(),
				'total_quantity' => StockAdjustment::inWarehouse($warehouseId)
					->sum('quantity'),
				'pending'        => StockAdjustment::inWarehouse($warehouseId)
					->pendingApproval()
					->count(),
			];

			dd($summary);
			return view('warehouses.adjustments', compact('adjustments', 'summary'));
		}

		public function searchAdjustments(Request $request) {
			$query = StockAdjustment::query();

			// Dynamic filtering using scopes
			if ($request->filled('type')) {
				if ($request->type === 'increase') {
					$query->increases();
				} elseif ($request->type === 'decrease') {
					$query->decreases();
				}
			}

			if ($request->filled('reason')) {
				$query->byReason($request->reason);
			}

			if ($request->filled('start_date') && $request->filled('end_date')) {
				$query->betweenDates($request->start_date, $request->end_date);
			}

			if ($request->filled('product_id')) {
				$query->forProduct($request->product_id);
			}

			if ($request->filled('warehouse_id')) {
				$query->inWarehouse($request->warehouse_id);
			}

			if ($request->filled('large_only')) {
				$query->largeAdjustments(50);
			}

			if ($request->filled('pending_only')) {
				$query->pendingApproval();
			}

			$adjustments = $query
				->with([
					'product',
					'location',
					'adjuster'
				])
				->orderBy('created_at', 'desc')
				->paginate(25);

			return view('adjustments.search', compact('adjustments'));
		}

		/**
		 * Handle AJAX adjustment request
		 * POST /adjustments
		 *
		 * @throws Throwable
		 */
		public function store(AdjustInventoryRequest $request) {
			$input = $request->validated();

			try {
				// Get current inventory
				$inventory = Inventory::query()
					->where('product_id', $input->get('product_id'))
					->where('location_id', $input->get('location_id'))
					->firstOrFail();

				$quantityBefore = $inventory->quantity;
				$product        = Product::query()
					->findOrFail($input->get('product_id'));

				// Apply adjustment
				if ($input->get('type') === 'increase') {
					$inventory->increment('quantity', $input->get('quantity', 0));
				} else {
					if ($inventory->quantity < $input->get('quantity')) {
						throw new Exception('Insufficient stock for decrease');
					}
					$inventory->decrement('quantity', $input->get('quantity', 0));
				}

				$quantityAfter = $inventory->quantity;

				// Create adjustment record
				$adjustment = Adjustment::query()
					->create([
						'product_id'        => $product->id,
						'location_id'       => $input->get('location_id'),
						'warehouse_id'      => $inventory->warehouse_id,
						'type'              => $input->get('type'),
						'quantity'          => $input->get('quantity'),
						'quantity_before'   => $quantityBefore,
						'quantity_after'    => $quantityAfter,
						'reason'            => $input->get('reason'),
						'notes'             => $input->get('notes'),
						'unit_cost'         => $product->cost_price,
						'total_cost'        => $input->get('type') === 'increase' ? $product->cost_price * $input->get('quantity') : 0,
						'adjusted_by'       => Auth::id(),
						'requires_approval' => $input->get('quantity', 0) > 50,
					]);

				return response()->json([
					'success'           => true,
					'message'           => 'Stock adjusted successfully.',
					'adjustment_id'     => $adjustment->id,
					'new_quantity'      => $quantityAfter,
					'requires_approval' => $adjustment->requires_approval,
				]);

			} catch (Exception $e) {
				return response()->json([
					'success' => false,
					'message' => 'Adjustment failed: ' . $e->getMessage(),
				], 500);
			} catch (Throwable $e) {
				return response()->json([
					'success' => false,
					'message' => $e->getMessage(),
					'input'   => $input
				]);
			}
		}
	}
