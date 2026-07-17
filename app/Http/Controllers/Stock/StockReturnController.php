<?php

	namespace App\Http\Controllers\Stock;

	use App\Enums\Inventory\StockReturnStatus;
	use App\Http\Controllers\Controller;
	use App\Http\Requests\Stocks\StockReturns\StockReturnStoreRequest;
	use App\Http\Requests\Stocks\StockReturns\StockReturnUpdateRequest;
	use App\Models\StockReturn;
	use Illuminate\Http\Request;
	use Illuminate\Support\Str;

	class StockReturnController extends Controller {
		/**
		 * Display a listing of the resource.
		 */
		public function index() {
			return view('stocks.returns.index', [
				'returns'  => StockReturn::query()->latest()->with(['items.product', 'creator'])->paginate(10),
				'statuses' => StockReturnStatus::cases(),
			]);
		}

		/**
		 * Show the form for creating a new resource.
		 */
		public function create() {
			// Generate a fresh, unique RMA number for the form input placeholder or default value
			// Format: RMA-S-YYYYMMDD-RANDOM
			return view('stocks.returns.create', [
				'suggestedRma' => 'RMA-S-'.date('Ymd').'-'.Str::upper(Str::random(6)),
				'statuses'     => StockReturnStatus::cases(),
			]);
		}

		/**
		 * Store a newly created resource in storage.
		 */
		public function store(StockReturnStoreRequest $request): StockReturn {
			dd($request->validated());
			return StockReturn::query()->create($request->validated());
		}

		/**
		 * Display the specified resource.
		 */
		public function show(string $id) {
			$return = StockReturn::with([
				'items.product',
				'creator',
				'returnable'
			])->findOrFail($id);

			return view('stocks.returns.show', compact('return'));
		}

		/**
		 * Show the form for editing the specified resource.
		 */
		public function edit(Request $request, StockReturn $return) {
			// Αν η επιστροφή έχει ήδη ολοκληρωθεί/εγκριθεί, ίσως θες να απαγορεύσεις την επεξεργασία
//			if ($stockReturn->status != StockReturnStatus::PENDING) {
//				return redirect()->route('inventory.returns.index')
//				                 ->with('error', 'Δεν μπορείτε να επεξεργαστείτε μια μην εκκρεμή επιστροφή.');
//			}

			return view('stocks.returns.edit', [
				'return'   => $return,
				'statuses' => StockReturnStatus::cases(),
			]);
		}

		/**
		 * Update the specified resource in storage.
		 */
		public function update(StockReturnUpdateRequest $request, StockReturn $return) {
			$input = $request->validated();
			$return->update($input);
			return redirect()->route('inventory.returns.index');
		}

		/**
		 * Remove the specified resource from storage.
		 */
		public function destroy(string $id) {
			//
		}
	}
