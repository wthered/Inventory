<?php

	namespace App\Http\Controllers\Inventory;

	use App\DataTransferObjects\ProductDTO;
	use App\DataTransferObjects\UserDTO;
	use App\DataTransferObjects\Warehouse\LocationDTO;
	use App\DataTransferObjects\Warehouse\WarehouseDTO;
	use App\Http\Controllers\Controller;
	use App\Http\Requests\Inventories\FetchProductLocationOptionsRequest;
	use App\Http\Requests\Stocks\StockAdjustments\StockAdjustmentItemRequest;
	use App\Http\Requests\Warehouses\IndexWarehouseAislesRequest;
	use App\Http\Requests\Warehouses\IndexWarehouseBinsRequest;
	use App\Http\Requests\Warehouses\IndexWarehouseRacksRequest;
	use App\Http\Requests\Warehouses\IndexWarehouseShelvesRequest;
	use App\Http\Requests\Warehouses\IndexWarehousesRequest;
	use App\Http\Requests\Warehouses\IndexWarehouseZonesRequest;
	use App\Http\Requests\Warehouses\WarehouseFilterRequest;
	use App\Models\Warehouse;
	use App\Models\WarehouseLocation;
	use App\Services\Warehouse\WarehouseAnalyticsService;
	use App\Services\Warehouse\WarehouseFilterService;
	use App\Services\Warehouse\WarehouseLayoutService;
	use Illuminate\Contracts\View\Factory;
	use Illuminate\Contracts\View\View;
	use Illuminate\Http\JsonResponse;
	use Illuminate\Http\Request;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Str;
	use Throwable;

	class WarehouseController extends Controller {
		public function __construct(
			protected WarehouseLayoutService $layoutService,
			protected WarehouseAnalyticsService $analyticsService,
			protected WarehouseFilterService $filterService
		) {}

		/**
		 * Display a listing of the resource.
		 */
		public function index(Request $request): Factory|View {
			return view('warehouses.index', [
				'warehouses' => Warehouse::with(['manager.account'])->withCount('locations')->paginate($request->session()->get('per_page', 25)),
				'user'       => UserDTO::fromModel(Auth::user())
			]);
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
		 * Display the specified resource.
		 */
		/**
		 * Display the specified resource.
		 */
		/**
		 * Display the specified resource.
		 */
		public function show(Request $request, int $id): View {
			// 1. Eager load manager account and pre-aggregate relational counts in 1 query
			$warehouseModel = Warehouse::with(['manager.account'])
			                           ->withCount(['locations', 'inventories'])
			                           ->findOrFail($id);

			// 2. Build the optimized base query for locations
			$locationsQuery = $warehouseModel->locations()
			                                 ->getQuery()
			                                 ->with([
				                                 'inventories.product',
				                                 'inventories.warehouse' // Prevents N+1 lookup inside DTO conversions
			                                 ]);

			// 3. Apply standard layouts
			$locationsQuery = $this->filterService->applyFilters($locationsQuery, $request->only([
				'zone',
				'aisle',
				'rack',
				'shelf'
			]));

			// 4. Tighten pagination chunks to prevent high memory hydration leaks
			$perPage = $request->session()->get('per_page', 24); // Changed to 24 (even divisor for grid view layout alignment)
			$locationsPagination = $locationsQuery->paginate($perPage);

			// 5. Transform collection in-place safely without duplicating array offsets
			$locationsPagination->through(function (WarehouseLocation $location) {
				return LocationDTO::fromModel($location);
			});

			// 6. DEFER HEAVY LEDGER CALCULATION: Cache heavy calculations for 10 minutes
			$analytics = cache()->remember("warehouse:{$id}:analytics", now()->addMinutes(10), function () use (
				$warehouseModel,
				$id
			) {
				return [
					'recentActivities'  => $this->analyticsService->getRecentActivities($id),
					'totalValue'        => $this->analyticsService->calculateWarehouseValue($id),
					'latestInventories' => $this->analyticsService->getLatestInventories($warehouseModel),
					'transfersCount'    => $this->analyticsService->getMonthlyTransferCount($warehouseModel),
				];
			});

			return view('warehouses.show', [
				'warehouse'         => WarehouseDTO::fromModel($warehouseModel),
				'locations'         => $locationsPagination,
				'recentActivities'  => $analytics['recentActivities'],
				'totalValue'        => $analytics['totalValue'],
				'latestInventories' => $analytics['latestInventories'],
				'transfersCount'    => $analytics['transfersCount'],
				'filterOptions'     => $this->filterService->getFilterOptions($warehouseModel),
				'staffCount'        => $warehouseModel->manager_id ? 1 : 0,
				'user'              => Auth::check() ? UserDTO::fromModel(Auth::user()) : null,
				'inventoriesCount'  => $warehouseModel->inventories_count,
			]);
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

		/******************************************************
		 * In products/{product} page, I load warehouse locations to transfer products from Warehouses
		 * Input: product_id, warehouse_id
		 * Returns: a list of source and destination (target) warehouses
		 *****************************************************/
		public function getWarehouseList(IndexWarehousesRequest $request): JsonResponse {
			$input = $request->validated();
			$product = new ProductDTO(intval($input->get('product_id')));

			$inventories = $product->inventories->where('warehouse_id', $input['warehouse_id']);

			$warehouses = $inventories->map(function ($inventory) use ($input) {
				return [
					'value'    => $inventory->location->id,
					'text'     => $inventory->warehouse->name." (aka ".$inventory->location->name.")",
					'selected' => $inventory->location->id === $input['location_id'],
				];
			});

			return response()->json([
				'source'    => $warehouses->values()->all(),
				'target'    => Warehouse::query()->orderBy('name')->get(),
				'inventory' => $inventories->where('location_id', $input['location_id'])->values(),
			]);
		}

		public function getLocations(FetchProductLocationOptionsRequest $request): JsonResponse {
			$input = $request->validated();
//			dd($input);
			$warehouse = Warehouse::query()->find($input['warehouse']);

			return response()->json([
				'zone'  => $this->createZoneOptions($warehouse),
				'aisle' => $this->createAisleOptions($warehouse),
				'rack'  => $this->createRackOptions($warehouse),
				'shelf' => $this->createShelfOptions($warehouse),
				'bin'   => $this->createBinOptions($warehouse),
			]);
		}

		private function createZoneOptions(Warehouse $warehouse): string {
			return Collection::range(1, $warehouse['zones'])->map(function ($zone) {
				return "<option value='Z".$zone."'>Zone ".$zone."</option>";
			})->prepend("<option value=''>Warehouse Zone</option>")->implode('');
		}

		private function createAisleOptions(Warehouse $warehouse): string {
			return Collection::range(1, $warehouse['aisles'])->map(function (int $aisle) {
				return "<option value='".$aisle."'>Aisle ".$aisle."</option>";
			})->prepend("<option value=''>Warehouse Aisle</option>")->implode('');
		}

		private function createRackOptions(Warehouse $warehouse): string {
			return Collection::range(1, $warehouse['racks'])->map(function ($rack) {
				return "<option value='".$rack."'>Rack ".$rack."</option>";
			})->prepend("<option value=''>Warehouse Rack</option>")->implode('');
		}

		private function createShelfOptions(Warehouse $warehouse): string {
			return Collection::range(1, $warehouse['shelves'])->map(function ($shelf) {
				return "<option value='".$shelf."'>Shelf ".$shelf."</option>";
			})->prepend("<option value=''>Warehouse Shelf</option>")->implode('');
		}

		private function createBinOptions(Warehouse $warehouse): string {
			return Collection::range(1, $warehouse['bins'])->map(function ($bin) {
				return "<option value='".$bin."'>Bin ".$bin."</option>";
			})->prepend("<option value=''>Warehouse Bin</option>")->implode('');
		}

		/**
		 * Gets available destinations when changing the
		 * desired destination warehouse drop down value
		 */
		public function getZones(IndexWarehouseZonesRequest $request): JsonResponse {
			$input = $request->validated();
//			dd($input['warehouse']);

			return response()->json([
				'success' => Collection::make($input)->isNotEmpty(),
				'zone'    => $this->createZoneOptions(Warehouse::query()->find($input['warehouse'])),
			]);
		}

		public function getAisles(IndexWarehouseAislesRequest $request): JsonResponse {
			$input = $request->validated();
			$warehouse = Warehouse::query()->find($input['warehouse']);

			return response()->json([
				'success' => Collection::make($input)->isNotEmpty(),
				'aisle'   => $this->createAisleOptions($warehouse),
			]);
		}

		public function getRacks(IndexWarehouseRacksRequest $request): JsonResponse {
			$input = $request->validated();
//			dd($input);
			$warehouse = Warehouse::query()->find($input['warehouse']);

			return response()->json([
				'success' => Collection::make($request->validated())->isNotEmpty(),
				'rack'    => $this->createRackOptions($warehouse),
			]);
		}

		public function getShelves(IndexWarehouseShelvesRequest $request): JsonResponse {
			$input = $request->validated();
			$warehouse = Warehouse::query()->find($input['warehouse']);

			return response()->json([
				'success' => Collection::make($input)->isNotEmpty(),
				'shelf'   => $this->createShelfOptions($warehouse),
			]);
		}

		public function getBins(IndexWarehouseBinsRequest $request): JsonResponse {
			$input = $request->validated();
			$warehouse = Warehouse::query()->find($input['warehouse']);

			return response()->json([
				'success' => Collection::make($input)->isNotEmpty(),
				'bin'     => $this->createBinOptions($warehouse),
			]);
		}

		public function toggleStatus(Warehouse $warehouse) {
			dd($warehouse);
		}

		public function activity(Warehouse $warehouse) {
			dd($warehouse);
		}

		/**
		 * @throws Throwable
		 */
		public function filter(WarehouseFilterRequest $request, int $id) {
//			dd($request->validated());

			// SYNTAX: Warehouse::query() is already a Builder, so this works as-is
			$warehouse = Warehouse::query()->find($id);
			$locationsQuery = $warehouse->locations()->getQuery()->with(['inventories.product']);

			// Now this matches the Builder type-hint in WarehouseFilterService
			$locationsQuery = $this->filterService->applyFilters($locationsQuery, $request->validated());

			$locationsPagination = $locationsQuery->paginate($request->session()->get('per_page', 25));

			$locationCollection = $locationsPagination->getCollection()->map(function (WarehouseLocation $location) {
				return LocationDTO::fromModel($location);
			});

			$options = $this->filterService->getFilterOptions($warehouse);

//			dd($options);

			// Pass the Builder to your service
			return response()->json([
				'options'   => Collection::make($options[Str::plural($request->validated('type'))])
				                         ->map(function ($option) {
					                         return "<option value='".$option['value']."'>".$option['text']."</option>";
				                         })->implode(''),
				'locations' => $locationCollection->map(function (LocationDTO $location) {
					return view('partials.location_card', ['location' => $location])->render();
				})->implode(''),
			]);
		}

		/**
		 * Ανακτά τα στοιχεία μιας θέσης βάσει του StockAdjustmentItem ID (πραγματικό PK),
		 * ή απευθείας Location ID αν πρόκειται για νέα γραμμή.
		 *
		 * @param  StockAdjustmentItemRequest  $request
		 *
		 * @return JsonResponse
		 */
		public function getLocationDetails(StockAdjustmentItemRequest $request): JsonResponse {
			$inputLocation = $request->validated('location_id');

			// Εύρεση της θέσης μαζί με την αποθήκη της
			$location = WarehouseLocation::query()->with('warehouse')->findOrFail($inputLocation);
			$warehouse = $location->warehouse;

			// Έλεγχος των ενεργών επιπέδων της αποθήκης
			$levelsConfig = [
				'zone'  => $warehouse->zones > 0,
				'aisle' => $warehouse->aisles > 0,
				'rack'  => $warehouse->racks > 0,
				'shelf' => $warehouse->shelves > 0,
				'bin'   => $warehouse->bins > 0,
			];

			// 5. Ανάκτηση όλων των θέσεων της αποθήκης για το client-side cascade filtering
			$rawLocations = WarehouseLocation::query()->where('warehouse_id', $warehouse->id)->get()->map(function ($loc
			) use (
				$levelsConfig
			) {
				$codeParts = [];
				if ($levelsConfig['zone'] && $loc->zone !== null) {
					$codeParts[] = $loc->zone;
				}
				if ($levelsConfig['aisle'] && $loc->aisle !== null) {
					$codeParts[] = $loc->aisle;
				}
				if ($levelsConfig['rack'] && $loc->rack !== null) {
					$codeParts[] = $loc->rack;
				}
				if ($levelsConfig['shelf'] && $loc->shelf !== null) {
					$codeParts[] = $loc->shelf;
				}
				if ($levelsConfig['bin'] && $loc->bin !== null) {
					$codeParts[] = $loc->bin;
				}

				return [
					'id'   => $loc->id,
					'code' => implode('-', $codeParts),
				];
			});

			// 6. Δημιουργία των έτοιμων HTML Options
			$levels = [];

			// --- ZONE ---
			if ($levelsConfig['zone']) {
				$zoneHtml = Collection::range(1, $warehouse->zones)->map(function (int $val) use ($location) {
					$selected = ($location->zone == $val) ? 'selected' : '';
					return "<option value='$val' $selected>".__('warehouse.levels.zone')." ".sprintf('%02d', $val)."</option>";
				})->prepend("<option value=''>".__('warehouse.levels_plural.zone')."</option>")->join('');

				$levels['zone'] = ['html' => $zoneHtml, 'disabled' => false, 'visible' => true];
			} else {
				$levels['zone'] = [
					'html'     => "<option value=''>".__('warehouse.levels_plural.zone')."</option>",
					'disabled' => true, 'visible' => false
				];
			}

			// --- AISLE ---
			if ($levelsConfig['aisle']) {
				$aisleHtml = Collection::range(1, $warehouse->aisles)->map(function (int $val) use ($location) {
					$selected = ($location->aisle == $val) ? 'selected' : '';
					return "<option value='$val' $selected>".__('warehouse.levels.aisle')." ".sprintf('%02d', $val)."</option>";
				})->prepend("<option value=''>".__('warehouse.levels_plural.aisle')."</option>")->join('');

				$levels['aisle'] = ['html' => $aisleHtml, 'disabled' => false, 'visible' => true];
			} else {
				$levels['aisle'] = [
					'html'     => "<option value=''>".__('warehouse.levels_plural.aisle')."</option>",
					'disabled' => true, 'visible' => false
				];
			}

			// --- RACK ---
			if ($levelsConfig['rack']) {
				$rackHtml = Collection::range(1, $warehouse->racks)->map(function (int $val) use ($location) {
					$selected = ($location->rack == $val) ? 'selected' : '';
					return "<option value='$val' $selected>".__('warehouse.levels.rack')." ".sprintf('%02d', $val)."</option>";
				})->prepend("<option value=''>".__('warehouse.levels_plural.rack')."</option>")->join('');

				$levels['rack'] = ['html' => $rackHtml, 'disabled' => false, 'visible' => true];
			} else {
				$levels['rack'] = [
					'html'     => "<option value=''>".__('warehouse.levels_plural.rack')."</option>",
					'disabled' => true, 'visible' => false
				];
			}

			// --- SHELF ---
			if ($levelsConfig['shelf']) {
				$shelfHtml = Collection::range(1, $warehouse->shelves)->map(function (int $val) use ($location) {
					$selected = ($location->shelf == $val) ? 'selected' : '';
					return "<option value='$val' $selected>".__('warehouse.levels.shelf')." ".sprintf('%02d', $val)."</option>";
				})->prepend("<option value=''>".__('warehouse.levels_plural.shelf')."</option>")->join('');

				$levels['shelf'] = ['html' => $shelfHtml, 'disabled' => false, 'visible' => true];
			} else {
				$levels['shelf'] = [
					'html'     => "<option value=''>".__('warehouse.levels_plural.shelf')."</option>",
					'disabled' => true, 'visible' => false
				];
			}

			// --- BIN ---
			if ($levelsConfig['bin']) {
				$binHtml = Collection::range(1, $warehouse->bins)->map(function (int $val) use ($location) {
					$selected = ($location->bin == $val) ? 'selected' : '';
					return "<option value='$val' $selected>".__('warehouse.levels.bin')." ".sprintf('%02d', $val)."</option>";
				})->prepend("<option value=''>".__('warehouse.levels_plural.bin')."</option>")->join('');

				$levels['bin'] = ['html' => $binHtml, 'disabled' => false, 'visible' => true];
			} else {
				$levels['bin'] = [
					'html'     => "<option value=''>".__('warehouse.levels_plural.bin')."</option>",
					'disabled' => true, 'visible' => false
				];
			}

			return response()->json([
				'current_location_id' => $location->id,
				'levels'              => $levels,
				'raw_locations'       => $rawLocations,
			]);
		}

		public function resolveLocation(WarehouseFilterRequest $request): JsonResponse {
			$input = $request->validated();

			$locationId = WarehouseLocation::query()
			                               ->where('warehouse_id', $input['warehouse'])
			                               ->where('zone', $input['zone'])
			                               ->where('aisle', $input['aisle'])
			                               ->where('rack', $input['rack'])
			                               ->where('shelf', $input['shelf'])
			                               ->where('bin', $input['bin'])
			                               ->value('id'); // Returns the scalar ID directly (or null if not found)

			if (!$locationId) {
				return response()->json([
					'message' => 'The specified location was not found in this warehouse or is not active.'
				], 404);
			}

			return response()->json([
				'id' => $locationId,
			]);
		}
	}
