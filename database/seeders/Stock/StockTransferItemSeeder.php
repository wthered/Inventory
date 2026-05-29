<?php

	namespace Database\Seeders\Stock;

	use App\Models\Product;
	use App\Models\StockTransfer;
	use App\Models\StockTransferItem;
	use App\Models\User;
	use Illuminate\Database\Seeder;

	class StockTransferItemSeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			$transfers = StockTransfer::all();
			$products  = Product::all();

			// Fallback αν δεν υπάρχουν warehouse managers
			$users = User::role('warehouse_manager')->pluck('id');
			if ($users->isEmpty()) {
				$users = User::pluck('id');
			}

			foreach ($transfers as $transfer) {
				$randomProducts = $products->random(mt_rand(2, 8));

				foreach ($randomProducts as $product) {
					$qty = mt_rand(12, 64);

					// ✅ Εδώ είναι η διόρθωση: Παίρνουμε το value από το Enum case
					$currentStatus = $transfer->status_id->value;

					StockTransferItem::create([
						'stock_transfer_id'  => $transfer->id,
						'product_id'         => $product->id,
						'quantity_requested' => $qty,

						// Λογική βασισμένη στα integer values του Enum
						// 1: PENDING, 2: SHIPPED, 3: COMPLETED κλπ.
						'quantity_delivered' => ($currentStatus >= 2) ? $qty : 0,
						'quantity_received'  => ($currentStatus === 3) ? $qty : 0,

						'processed_by'       => $users->random(),
						'processed_at'       => ($currentStatus >= 2) ? $transfer->approved_at : null,
						'notes'              => fake()->optional(0.2)->sentence(),
					]);
				}
			}

			$this->command->info('Linked items to ' . $transfers->count() . ' transfers.');
		}
	}