<?php

	namespace App\Http\Controllers\Reports;

	use App\DataTransferObjects\ProductDTO;
	use App\Enums\Inventory\AdjustmentType;
	use App\Enums\Inventory\MovementStatus;
	use App\Http\Controllers\Controller;
	use App\Http\Requests\Inventories\AdjustInventoryRequest;
	use App\Http\Requests\Inventories\FetchProductLocationOptionsRequest;
	use App\Models\Inventories\Inventory;
	use App\Models\Product;
	use App\Models\StockAdjustment;
	use App\Services\Inventory\InventoryReportService;
	use App\Services\Inventory\LocationOptionsService;
	use Carbon\Carbon;
	use Illuminate\Contracts\View\View;
	use Illuminate\Http\JsonResponse;
	use Illuminate\Support\Str;

	class InventoryController extends Controller {
		/**
		 * Services
		 */
		protected InventoryReportService $reportService;
		protected LocationOptionsService $locationService;

		/**
		 * Constructor with dependency injection
		 */
		public function __construct(InventoryReportService $reportService, LocationOptionsService $locationService) {
			$this->reportService = $reportService;
			$this->locationService = $locationService;
		}

		/**
		 * Display inventory dashboard with reports
		 */
		public function index(): View {
			$report = $this->reportService->getDashboardReport();

			return view('reports.inventory', [
				'lowStock'        => $report['lowStock'],
				'totalStockValue' => $report['totalStockValue'],
				'outOfStock'      => $report['outOfStock'],
				'stockByCategory' => $report['byCategory'],
				'recentMovements' => $report['recentMovements'],
			]);
		}

		/**
		 * Get location options for a product in a warehouse
		 * Used by product/show on open Modal
		 */
		public function getLocations(FetchProductLocationOptionsRequest $request, Product $product): JsonResponse {
			$input = $request->validated();

			$options = $this->locationService->getLocationOptions($product->id, $input['warehouse'], $input['location'] ?? null);

			return response()->json($options);
		}

		public function adjust(AdjustInventoryRequest $request, Inventory $inventory) {
			$input = $request->validated();
//			dd($input);

			$adjustment = StockAdjustment::query()->create([
				'adjustment_number' => 'ADJ-'.Str::upper(Str::random(8)),
				'warehouse_id'      => $input['warehouse_id'],
				'adjustment_date'   => Carbon::now(config('app.timezone'))->toDateString(),
				'notes'             => $input['notes'],
				'status'            => MovementStatus::PENDING->value,
				'created_by'        => $input['created_by'],
			]);

			$object = new ProductDTO($input['product']);
			$productLocation = $object->product->inventories()->where('location_id', $input['location'])->first();
			$adjustment->items()->create([
				'product_id'      => $object->id,
				'location_id'     => $input['location'],
				'reason'          => $input['reason'],
				'quantity'        => $input['quantity'],
				'quantity_before' => $productLocation->quantity,
				//				'quantity_after'      => 'Handled by Observer'
				'unit_cost'       => $object->cost_price,
				'type'            => AdjustmentType::ADJUSTMENT->value,
				'notes'           => $input['notes'],
			]);

			return response()->json(['success' => true]);
		}
	}
