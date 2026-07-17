<?php

	namespace Database\Seeders;

	use App\Models\User;
	use App\Models\Warehouse;
	use App\Models\WarehouseLocation;
	use Illuminate\Support\Facades\DB;

	class WarehouseSeeder extends ParentSeeder {

		public function run(): void {
			$this->command->info('🏗️ Creating Warehouses via Factories...');

			$users = User::role(['admin', 'warehouse_manager'])->pluck('id')->sort()->unique()->values();

			// 1. Δημιουργούμε τις αποθήκες
			$warehouses = Warehouse::factory()->count(mt_rand(12, 16))->create()->each(function ($warehouse) use ($users) {
				if ($users->isNotEmpty()) {
					$warehouse->update([
						'manager_id' => $users->random()
					]);
				}
			});

			if ($warehouses->isNotEmpty()) {
				$warehouses->first()->update(['is_primary' => true]);
			}

			$totalLocations = 0;

			// 2. Χρήση του Factory για τη γέννηση των locations της κάθε αποθήκης
			foreach ($warehouses as $warehouse) {
				// Παίρνουμε το έτοιμο grid array από το factory
				$locationsData = WarehouseLocation::factory()->makeGridData($warehouse);

				// Bulk insert για μέγιστη ταχύτητα
				DB::table('warehouse_locations')->insert($locationsData);

				$totalLocations += count($locationsData);
			}

			$this->command->info("✅ Successfully seeded {$warehouses->count()} warehouses with {$totalLocations} slots using WarehouseLocationFactory!");
		}
	}