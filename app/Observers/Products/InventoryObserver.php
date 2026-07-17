<?php

	namespace App\Observers\Products;

	use App\Models\Inventories\Inventory;
	use Exception;
	use Illuminate\Support\Facades\DB;

	class InventoryObserver {
		/**
		 * Handle the Inventory "creating" and "updating" events.
		 * This method validates that the location belongs to the warehouse.
		 *
		 * @param  Inventory  $inventory
		 *
		 * @return void
		 * @throws Exception
		 */
		public function creating(Inventory $inventory): void {
			$this->validateWarehouseLocation($inventory);
		}

		/**
		 * Private validation method.
		 *
		 * @param  Inventory  $inventory
		 *
		 * @return void
		 * @throws Exception
		 */
		private function validateWarehouseLocation(Inventory $inventory): void {
			$warehouse = $inventory->warehouse_id;
			$location  = $inventory->location_id;

			if ($warehouse === null || $location === null) {
				// Nulls are allowed by your migration but validation should prevent partial data save
				// unless you have specific business logic to handle this.
				return;
			}

			// Check if a record exists in the warehouse_locations table
			// that matches BOTH the warehouse_id AND the location_id (the PK of warehouse_locations).
			$validLocation = DB::table('warehouse_locations')->where('id', $location)->where('warehouse_id', $warehouse);

			if (!$validLocation->exists()) {
				throw new Exception("Integrity Error: Location ID $location (the bin/shelf) does not belong to Warehouse ID ".$warehouse.". Inventory move aborted.");
			}
		}

		/**
		 * @throws Exception
		 */
		public function updating(Inventory $inventory): void {
			// Only run validation if the warehouse or location has actually changed
			if ($inventory->isDirty('warehouse_id') || $inventory->isDirty('location_id')) {
				$this->validateWarehouseLocation($inventory);
			}
		}
	}
