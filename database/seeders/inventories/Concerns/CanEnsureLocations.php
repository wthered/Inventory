<?php

	namespace Database\Seeders\inventories\Concerns;

	use DB;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Str;

	trait CanEnsureLocations {
		/**
		 * Ελέγχει και δημιουργεί τα locations αν λείπουν.
		 * (Εδώ βάζεις τη λογική που εκτύπωνε τα "Warehouse Helm's Deep finished...")
		 */
		protected function ensureWarehouseLocations(Collection $warehouses): void {
			$remaining = $warehouses->shuffle();

			while ($remaining->isNotEmpty()) {
//				$warehouse = $remaining->shuffle()->shift(); // Παίρνει και αφαιρεί το πρώτο
//				$index = Collection::range(0, $remaining->count() - 1)->random();
				$index = $remaining->keys()->random();
				$warehouse = $remaining[$index];

				$this->command->info("[" . Str::padLeft($index + 1, 2, '0') . " / " . $warehouses->count() . "] Building infrastructure for: " . $warehouse->name);

				$batch = []; // Χρησιμοποιούμε απλό array για μέγιστη ταχύτητα

				for ($zone = 1; $zone <= max(1, $warehouse->zones); $zone++) {
					for ($aisle = 1; $aisle <= max(1, $warehouse->aisles); $aisle++) {
						for ($rack = 1; $rack <= max(1, $warehouse->racks); $rack++) {
							for ($shelf = 1; $shelf <= max(1, $warehouse->shelves); $shelf++) {
								for ($bin = 1; $bin <= max(1, $warehouse->bins); $bin++) {

									$batch[] = [
										'warehouse_id' => $warehouse->id,
										'code'         => "W".$warehouse->id."-Z".$zone."-A".$aisle."-R".$rack."-S".$shelf."-B.".$bin,
										'name'         => "Loc: Z".$zone."-A".$aisle."-R".$rack."-S".$shelf."-B".$bin,
										'zone'         => $zone,
										'aisle'        => $aisle,
										'rack'         => $rack,
										'shelf'        => $shelf,
										'bin'          => $bin,
										'created_at'   => now()->subHours(mt_rand(0,23))->subMinutes(mt_rand(0,59))->subSeconds(mt_rand(0,59)),
										'updated_at'   => now()->addHours(mt_rand(0,23))->addMinutes(mt_rand(0,59))->addSeconds(mt_rand(0,59)),
									];

									if (count($batch) >= static::BATCH_SIZE) {
										DB::table('warehouse_locations')->insert($batch);
										$batch = [];
									}
								}
							}
						}
					}
				}
				$remaining->forget($index);

				if (!empty($batch)) {
					DB::table('warehouse_locations')->insert($batch);
				}
			}
		}
	}