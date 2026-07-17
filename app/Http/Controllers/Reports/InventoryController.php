<?php

	namespace App\Http\Controllers\Reports;

	use App\Http\Controllers\Controller;
	use App\Http\Requests\Inventories\FetchProductLocationOptionsRequest;
	use App\Models\Product;
	use App\Services\Inventory\InventoryReportService;
	use App\Services\Inventory\LocationOptionsService;
	use Illuminate\Contracts\View\View;
	use Illuminate\Http\JsonResponse;
	use Illuminate\Http\Request;

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
			$this->reportService   = $reportService;
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

		public function getInventories(Request $request, Product $product): JsonResponse {
			return response()->json($product->inventories());
		}
	}
