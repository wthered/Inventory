<?php

	namespace Database\Seeders\inventories;

	use App\Enums\Inventory\MovementStatus;
	use App\Models\Inventories\InventoryMovementLog;
	use App\Models\StockAdjustmentItem;
	use App\Models\StockReturnItem;
	use App\Models\StockTransferItem;
	use Illuminate\Database\Seeder;
	use Illuminate\Support\Carbon;
	use Illuminate\Support\Facades\DB;

	class InventoryMovementLogSeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			// Ανάκτηση IDs από υπάρχουσες εγγραφές
			$productId   = DB::table('products')->value('id');
			$warehouseId = DB::table('warehouses')->value('id');
			$locationId  = DB::table('warehouse_locations')->value('id');
			$userId      = DB::table('users')->value('id');

			// Έλεγχος αν υπάρχουν τα απαραίτητα δεδομένα
			if (!$productId || !$warehouseId || !$locationId || !$userId) {
				$this->command->warn('Προσοχή: Απαιτούνται εγγραφές στους πίνακες products, warehouses, warehouse_locations και users.');
				return;
			}

			// Δημιουργία logs βασισμένα στο MovementStatus
			$logs = [
				[
					'product_id'         => $productId,
					'warehouse_id'       => $warehouseId,
					'location_id'        => $locationId,
					'action'             => 'ADJUSTMENT_' . MovementStatus::COMPLETED->name,
					'status'             => MovementStatus::COMPLETED->value,
					'requested_quantity' => 20,
					'before_quantity'    => 5,
					'error_message'      => null,
					'loggable_type'      => StockAdjustmentItem::class,
					'loggable_id'        => fake()->numberBetween(1, StockAdjustmentItem::count()),
					'user_id'            => $userId,
					'created_at'         => Carbon::now()
						->subDays(3),
					'updated_at'         => Carbon::now()
						->subDays(3),
				],
				[
					'product_id'         => $productId,
					'warehouse_id'       => $warehouseId,
					'location_id'        => $locationId,
					'action'             => 'TRANSFER_' . MovementStatus::IN_TRANSIT->name,
					'status'             => MovementStatus::IN_TRANSIT->value,
					'requested_quantity' => 50,
					'before_quantity'    => 100,
					'error_message'      => null,
					'loggable_type'      => StockTransferItem::class,
					'loggable_id'        => fake()->numberBetween(1, StockTransferItem::count()),
					'user_id'            => $userId,
					'created_at'         => Carbon::now()->subDays(1),
					'updated_at'         => Carbon::now()->subDays(1),
				],
				[
					'product_id'         => $productId,
					'warehouse_id'       => $warehouseId,
					'location_id'        => $locationId,
					'action'             => 'RETURN_' . MovementStatus::CANCELED->name,
					'status'             => MovementStatus::CANCELED->value,
					'requested_quantity' => 5,
					'before_quantity'    => 85,
					'error_message'      => 'Return request denied by manager.',
					'loggable_type'      => StockReturnItem::class,
					'loggable_id'        => fake()->numberBetween(1, StockReturnItem::query()->count()),
					'user_id'            => $userId,
					'created_at'         => Carbon::now(),
					'updated_at'         => Carbon::now(),
				],
			];

			foreach ($logs as $log) {
				InventoryMovementLog::query()->create($log);
			}

			$this->command->info('Ο InventoryMovementLogSeeder εκτελέστηκε με επιτυχία (χρήση ::class)!');
		}
	}
