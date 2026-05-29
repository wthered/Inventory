<?php

	namespace Database\Seeders\Stock;

	use App\Enums\Inventory\StockReturnStatus;
	use App\Enums\Stock\QualityStatus;
	use App\Models\Product;
	use App\Models\StockReturn;
	use App\Models\StockReturnItem;
	use App\Models\WarehouseLocation;
	use Illuminate\Database\Seeder;
	use Illuminate\Support\Facades\DB;
	use Throwable;

	class StockReturnItemSeeder extends Seeder {

		/**
		 * @throws Throwable
		 */
		public function run(): void {
			$products = Product::all(['id', 'cost_price']);
			$allLocations = WarehouseLocation::all()->groupBy('warehouse_id');
			$returns = StockReturn::all();

			if ($returns->isEmpty()) {
				$this->command->warn("No StockReturns found. Skipping...");
				return;
			}

			$this->command->info("Seeding Stock Return Items (Stock Namespace)...");

			DB::transaction(function () use ($returns, $products, $allLocations) {
				foreach ($returns as $return) {
					$selectedProducts = $products->random(mt_rand(1, 4));
					$warehouseLocations = $allLocations->get($return->warehouse_id);

					foreach ($selectedProducts as $product) {
						$quality = fake()->randomElement(QualityStatus::cases());
						$isCompleted = ($return->status === StockReturnStatus::COMPLETED->value);

						// Logic: Αν είναι completed, μπαίνει σε location.
						$locationId = ($isCompleted && $warehouseLocations) ? $warehouseLocations->random()->id : null;

						// Δημιουργία του Item
						$item = StockReturnItem::query()->create([
							'stock_return_id'  => $return->id,
							'product_id'       => $product->id,
							'location_id'      => $locationId,
							'quantity'         => mt_rand(1, 12),
							'unit_cost'        => $product->cost_price ?? mt_rand(50, 200),
							'quality_status'   => $quality->value,
							// Χρήση του Enum logic για το αν μπορεί να ξαναμπεί στο ράφι
							'is_restockable'   => !in_array($quality, [QualityStatus::DAMAGED, QualityStatus::DEFECTIVE]),
							'restocked_at'     => $isCompleted ? now()->subHours(rand(1, 72)) : null,
						]);

						// --- Καλή χρήση του $item αντικειμένου ---
						// Αν το προϊόν δεν είναι 'NEW', προσθέτουμε υποχρεωτικά ένα inspection note
						if ($quality !== QualityStatus::NEW) {
							$item->update([
								'inspection_notes' => "Automatic Flag: Product returned as " . $quality->label() . ". Verified by Seeder."
							]);
						}
					}
					$this->command->getOutput()->write(".");
				}
			});

			$this->command->info("\nDone! Stock namespace integration verified.");
		}
	}