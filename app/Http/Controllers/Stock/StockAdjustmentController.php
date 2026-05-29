<?php

	namespace App\Http\Controllers\Stock;

	use App\DataTransferObjects\ProductDTO;
	use App\Http\Controllers\Controller;
	use App\Http\Requests\Stocks\StockAdjustments\StockAdjustmentStoreRequest;
	use App\Models\Product;
	use App\Models\StockAdjustment;
	use App\Services\Stock\AdjustmentReasonService;
	use Carbon\Carbon;
	use Illuminate\Http\Request;
	use Illuminate\Support\Str;

	class StockAdjustmentController extends Controller {

		private AdjustmentReasonService $service;

		public function __construct(AdjustmentReasonService $adjustmentReasonService) {
			$this->service = $adjustmentReasonService;
		}

		/**
		 * Display a listing of the resource.
		 */
		public function index() {
			//
		}

		/**
		 * Store a newly created resource in storage.
		 */
		public function store(StockAdjustmentStoreRequest $request) {
			$input = $request->validated();

			$adjustment = StockAdjustment::create([
				'adjustment_number' => 'ADJ-' . Str::upper(Str::random(8)),
				'warehouse_id'      => $input['warehouse_id'],
				'adjustment_date'   => Carbon::now(config('app.timezone'))->toDateString(),
				'notes'             => $input['notes'],
				'created_by'        => $input['created_by'],
			]);

			$object = new ProductDTO($input['product']);
			$productLocation = $object->product->inventories()->where('location_id', $input['location'])->first();
			$adjustment->items()->create([
				'stock_adjustment_id' => $adjustment->id,
				'product_id'          => $object->id,
				'location_id'         => $input['location'],
				'reason'              => $input['reason'],
				'quantity'            => $input['quantity'],
				'quantity_before'     => $productLocation->quantity,
//				'quantity_after'      => 'Handled by Observer'
				'unit_cost'           => $object->cost_price,
				'notes'               => $input['notes'],
			]);

			return response()->json(['success' => true]);
		}

		/**
		 * Show the form for creating a new resource.
		 */
		public function create() {
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

		public function getReasons() {
			return $this->service->generateReasonDropdown();
		}
	}
