<?php

	namespace App\Http\Controllers\Inventory;

	use App\DataTransferObjects\ProductDTO;
	use App\DataTransferObjects\UserDTO;
	use App\DataTransferObjects\Warehouse\LocationDTO;
	use App\DataTransferObjects\Warehouse\WarehouseDTO;
	use App\Http\Controllers\Controller;
	use App\Http\Requests\Inventories\FetchProductLocationOptionsRequest;
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
		public function __construct(protected WarehouseLayoutService $layoutService, protected WarehouseAnalyticsService $analyticsService, protected WarehouseFilterService $filterService) {}

		/**
		 * Display a listing of the resource.
		 */
		public function index(): Factory|View {
			return view('warehouses.index', [
				'warehouses' => Warehouse::paginate(15),
				'user'       => Auth::user()
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
		public function show(Request $request, int $id): View {
			$warehouseModel = Warehouse::with(['manager'])
				->withCount('inventories')
				->findOrFail($id);

			// .getQuery() converts the HasMany relationship into a Builder instance
			$locationsQuery = $warehouseModel
				->locations()
				->getQuery()
				->with(['inventories.product']);

			// Now this matches the Builder type-hint in WarehouseFilterService
			$locationsQuery = $this->filterService->applyFilters($locationsQuery, $request->only([
				'zone',
				'aisle',
				'rack',
				'shelf'
			]));

			$locationsPagination = $locationsQuery->paginate($request
				->session()
				->get('per_page', 25));

			$locationCollection = $locationsPagination
				->getCollection()
				->map(function (WarehouseLocation $location) {
					return LocationDTO::fromModel($location);
				});

			return view('warehouses.show', [
				'warehouse'         => WarehouseDTO::fromModel($warehouseModel),
				'locations'         => $locationsPagination->setCollection($locationCollection),

				// Use the new service methods here
				'recentActivities'  => $this->analyticsService->getRecentActivities($id),
				'totalValue'        => $this->analyticsService->calculateWarehouseValue($id),
				'latestInventories' => $this->analyticsService->getLatestInventories($warehouseModel),
				'transfersCount'    => $this->analyticsService->getMonthlyTransferCount($warehouseModel),

				// Provide filter options to the view
				'filterOptions'     => $this->filterService->getFilterOptions($warehouseModel),

				'staffCount'       => $warehouseModel->manager_id ? 1 : 0,
				'user'             => Auth::check() ? UserDTO::fromModel(Auth::user()) : null,
				'inventoriesCount' => $warehouseModel->inventories_count,
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
			$input   = $request->validated();
			$product = new ProductDTO(intval($input->get('product_id')));

			$inventories = $product->inventories->where('warehouse_id', $input['warehouse_id']);

			$warehouses = $inventories->map(function ($inventory) use ($input) {
				return [
					'value'    => $inventory->location->id,
					'text'     => $inventory->warehouse->name . " (aka " . $inventory->location->name . ")",
					'selected' => $inventory->location->id === $input['location_id'],
				];
			});

			return response()->json([
				'source'    => $warehouses
					->values()
					->all(),
				'target'    => Warehouse::query()
					->orderBy('name')
					->get(),
				'inventory' => $inventories
					->where('location_id', $input['location_id'])
					->values(),
			]);
		}

		public function getLocations(FetchProductLocationOptionsRequest $request): JsonResponse {
			$input     = $request->validated();
			$warehouse = Warehouse::query()->find($input['warehouse']);

			$shelves = "<option value='' disabled>Select Shelf</option>";
			Collection::range(1, $warehouse['shelves'])->each(function ($shelf) use (&$shelves) {
				$shelves .= "<option value='" . $shelf . "'>Shelf " . $shelf . "</option>";
			});

			$bins = "<option value='' disabled>Select Bin</option>";
			Collection::range(1, $warehouse['bins'])->each(function ($bin) use (&$bins) {
				$bins .= "<option value='" . $bin . "'>Bin " . $bin . "</option>";
			});

			return response()->json([
				'zone'  => $this->createZoneOptions($warehouse),
				'aisle' => $this->createAisleOptions($warehouse),
				'rack'  => $this->createRackOptions($warehouse),
				'shelf' => $this->createShelfOptions($warehouse),
				'bin'   => $this->createBinOptions($warehouse),
			]);
		}

		private function createZoneOptions(Warehouse $warehouse): Collection {
			return Collection::range(1, $warehouse['zones'])
				->map(function ($zone) {
					return [
						'value' => "Z" . $zone,
						'text'  => "Z" . $zone
					];
				});
		}

		private function createAisleOptions(Warehouse $warehouse): array {
			$options = Collection::range(1, $warehouse['aisles'])
				->map(function (int $aisle) {
					return "<option value='" . $aisle . "'>Aisle " . $aisle . "</option>";
				})
				->implode('');
			return [
				'options'   => $options,
				'locations' => 5,
			];
		}

		private function createRackOptions(Warehouse $warehouse): Collection {
			return Collection::range(1, $warehouse['racks'])
				->map(function ($rack) {
					return [
						'value' => $rack,
						'text'  => $rack
					];
				});
		}

		private function createShelfOptions(Warehouse $warehouse): Collection {
			return Collection::range(1, $warehouse['shelves'])
				->map(function ($shelf) {
					return [
						'value' => $shelf,
						'text'  => $shelf
					];
				});
		}

		private function createBinOptions(Warehouse $warehouse): Collection {
			return Collection::range(1, $warehouse['bins'])
				->map(function ($bin) {
					return [
						'value' => $bin,
						'text'  => $bin
					];
				});
		}

		/**
		 * Gets available destinations when changing the
		 * desired destination warehouse drop down value
		 */
		public function getZones(IndexWarehouseZonesRequest $request, Warehouse $warehouse): JsonResponse {
			$input = $request->validated();

			return response()->json([
				'success' => Collection::make($input)
					->isNotEmpty(),
				'zone'    => $this->createZoneOptions($warehouse),
			]);
		}

		public function getAisles(IndexWarehouseAislesRequest $request, Warehouse $warehouse): JsonResponse {
			$input = $request->validated();

			return response()->json([
				'success' => Collection::make($input)
					->isNotEmpty(),
				'aisle'   => $this->createAisleOptions($warehouse),
			]);
		}

		public function getRacks(IndexWarehouseRacksRequest $request, Warehouse $warehouse): JsonResponse {
			$input = $request->validated();

			return response()->json([
				'success' => Collection::make($input)
					->isNotEmpty(),
				'rack'    => $this->createRackOptions($warehouse),
			]);
		}

		public function getShelves(IndexWarehouseShelvesRequest $request, Warehouse $warehouse): JsonResponse {
			$input = $request->validated();

			return response()->json([
				'success' => Collection::make($input)
					->isNotEmpty(),
				'shelf'   => $this->createShelfOptions($warehouse),
			]);
		}

		public function getBins(IndexWarehouseBinsRequest $request, Warehouse $warehouse): JsonResponse {
			$input = $request->validated();

			return response()->json([
				'success' => Collection::make($input)
					->isNotEmpty(),
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
			// Pass the Builder to your service
			return response()->json([
				'options'   => Collection::make($options[Str::plural($request->validated('type'))])->map(function ($option) {
					return "<option value='" . $option['value'] . "'>".$option['text']."</option>";
				})->implode(''),
				'locations' => $locationCollection->map(function (LocationDTO $location) {
					return view('partials.location_card', ['location' => $location])->render();
				})->implode(''),
			]);
		}
	}
