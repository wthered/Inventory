<?php

	namespace App\Services\Inventory;

	use App\Models\Warehouse;
	use App\Models\WarehouseLocation;
	use Illuminate\Support\Collection;

	class LocationOptionsService {
		/**
		 * Get location options for a product
		 */
		public function getLocationOptions(int $productId, int $warehouseId, ?int $locationId = null): array {
			$warehouse   = Warehouse::query()->findOrFail($warehouseId);
			$inventories = $this->getProductInventories($productId, $warehouseId);
			$location    = $locationId ? WarehouseLocation::find($locationId) : null;

			return [
				'warehouse' => $this->createWarehouseOptions($inventories, $warehouse, $locationId),
				'zone'      => $this->createZoneOptions($location, $warehouse),
				'aisle'     => $this->createAisleOptions($location, $warehouse),
				'rack'      => $this->createRackOptions($location, $warehouse),
				'shelf'     => $this->createShelfOptions($location, $warehouse),
				'bin'       => $this->createBinOptions($location, $warehouse),
				'inventory' => $this->formatInventoryData($inventories),
			];
		}

		/**
		 * Get inventories for a product in a warehouse
		 */
		private function getProductInventories(int $productId, int $warehouseId): Collection {
			return Warehouse::find($warehouseId)->inventories()->where('product_id', $productId)->with('location')->get();
		}

		/**
		 * Create warehouse dropdown options
		 */
		private function createWarehouseOptions(Collection $inventories, Warehouse $warehouse, ?int $selectedLocationId): string {
			$options = $inventories->map(function ($inventory) use ($warehouse, $selectedLocationId) {
				$selected = $inventory->location_id == $selectedLocationId ? 'selected' : '';
				$disabled = $inventory->location_id != $selectedLocationId ? 'disabled' : '';

				return "<option value='" . $inventory->id . "' $selected $disabled>".$warehouse->name."</option>";
			});

			return $this->wrapWithDefaultOption($options->implode(''), 'Select Warehouse');
		}

		/**
		 * Wrap options with a default option
		 */
		private function wrapWithDefaultOption(string $options, string $defaultText): string {
			$default = sprintf('<option value="0">%s</option>', e($defaultText));
			return $default . $options;
		}

		/**
		 * Create zone dropdown options
		 */
		private function createZoneOptions(?WarehouseLocation $location, Warehouse $warehouse): string {
			$options = Collection::range(1, $warehouse->zones)->map(function ($zone) use ($location) {
				$zoneName = 'Z' . $zone;
				$selected = $location && $location->zone == $zoneName ? 'selected' : 'disabled';
				return "<option value='" . e($zoneName) . "' " . $selected . ">Zone " . $zone . "</option>";
			});
			return $this->wrapWithDefaultOption($options->implode(''), 'Select Zone');
		}

		/**
		 * Create aisle dropdown options
		 */
		private function createAisleOptions(?WarehouseLocation $location, Warehouse $warehouse): string {
			$options = Collection::range(1, $warehouse->aisles)->map(function ($aisle) use ($location) {
				$aisleName = 'A' . $aisle;
				$selected  = $location && $location->aisle == $aisleName ? 'selected' : 'disabled';

				return sprintf('<option value="%s" %s>Aisle %d</option>', e($aisleName), $selected, $aisle);
			});

			return $this->wrapWithDefaultOption($options->implode(''), 'Select Aisle');
		}

		/**
		 * Create rack dropdown options
		 */
		private function createRackOptions(?WarehouseLocation $location, Warehouse $warehouse): string {
			$options = Collection::range(1, $warehouse->racks)->map(function ($rack) use ($location) {
				$selected = $location && $location->rack == $rack ? 'selected' : 'disabled';
				return sprintf('<option value="%d" %s>Rack %d</option>', $rack, $selected, $rack);
			});

			return $this->wrapWithDefaultOption($options->implode(''), 'Select Rack');
		}

		/**
		 * Create shelf dropdown options
		 */
		private function createShelfOptions(?WarehouseLocation $location, Warehouse $warehouse): string {
			$options = Collection::range(1, $warehouse->shelves)
				->map(function ($shelf) use ($location) {
					$selected = $location && $location->shelf == $shelf ? 'selected' : 'disabled';

					return sprintf('<option value="%d" %s>Shelf %d</option>', $shelf, $selected, $shelf);
				});

			return $this->wrapWithDefaultOption($options->implode(''), 'Select Shelf');
		}

		/**
		 * Create bin dropdown options
		 */
		private function createBinOptions(?WarehouseLocation $location, Warehouse $warehouse): string {
			$options = Collection::range(1, $warehouse->bins)
				->map(function ($bin) use ($location) {
					$selected = $location && $location->bin == $bin ? 'selected' : 'disabled';

					return sprintf('<option value="%d" %s>Bin %d</option>', $bin, $selected, $bin);
				});

			return $this->wrapWithDefaultOption($options->implode(''), 'Select Bin');
		}

		/**
		 * Format inventory data for JSON response
		 */
		private function formatInventoryData(Collection $inventories): array {
			return $inventories->map(function ($inventory) {
				return [
					'inventory'    => $inventory->id,
					'quantity'     => $inventory->quantity,
					'available'    => $inventory->available_quantity,
					'warehouse'    => $inventory->warehouse_id,
					'location'     => $inventory->location_id,
					'batch_number' => $inventory->batch_number,
					'expiry_date'  => $inventory->expiry_date,
				];
			})->toArray();
		}

		/**
		 * Get available locations for a product
		 */
		public function getAvailableLocations(int $productId, int $warehouseId): Collection {
			return WarehouseLocation::where('warehouse_id', $warehouseId)
				->whereHas('inventories', function ($query) use ($productId) {
					$query
						->where('product_id', $productId)
						->where('quantity', '>', 0);
				})
				->with([
					'inventories' => function ($query) use ($productId) {
						$query->where('product_id', $productId);
					}
				])
				->get()
				->map(function ($location) {
					return [
						'id'        => $location->id,
						'zone'      => $location->zone,
						'aisle'     => $location->aisle,
						'rack'      => $location->rack,
						'shelf'     => $location->shelf,
						'bin'       => $location->bin,
						'quantity'  => $location->inventories->sum('quantity'),
						'available' => $location->inventories->sum('available_quantity'),
					];
				});
		}
	}
