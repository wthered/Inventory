<?php

	namespace Database\Seeders\Stock;

	use App\Enums\Inventory\StockReturnStatus;
	use App\Models\Product;
	use App\Models\StockReturn;
	use App\Models\StockReturnItem;
	use App\Models\WarehouseLocation;
	use Database\Factories\ReturnItemFactory;
	use Database\Seeders\ParentSeeder;
	use Illuminate\Support\Facades\DB;
	use Throwable;

	class StockReturnItemSeeder extends ParentSeeder {

		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			// 1. Παίρνουμε μόνο τα απαραίτητα IDs προϊόντων για εξοικονόμηση μνήμης
			$productIds = Product::pluck('id')->toArray();

			// 2. Φορτώνουμε όλα τα locations ομαδοποιημένα ανά warehouse_id
			$allLocations = WarehouseLocation::all(['id', 'warehouse_id'])->groupBy('warehouse_id');

			if (empty($productIds)) {
				$this->command->error("No products found to seed return items!");
				return;
			}

			$this->command->info("Seeding Stock Return Items using Factory Chunks...");

			// 3. Επεξεργασία των StockReturn σε chunks των 128 εγγραφών για προστασία της RAM
			StockReturn::query()->chunk(128, function ($returns) use ($productIds, $allLocations) {
				$insertBuffer = [];

				foreach ($returns as $return) {
					// Επιλογή 1 έως 4 τυχαίων (αλλά μοναδικών στο ίδιο return) προϊόντων
					$totalItems = mt_rand(1, 4);
					$randomKeys = (array) array_rand($productIds, min($totalItems, count($productIds)));

					// Παίρνουμε τα locations που ανήκουν αποκλειστικά στην αποθήκη του τρέχοντος return
					$warehouseLocations = $allLocations->get($return->warehouse_id);
					$isCompleted = $return->status === StockReturnStatus::COMPLETED->value;

					foreach ($randomKeys as $key) {
						$productId = $productIds[$key];

						// Παραγωγή των default attributes από το Factory (ως raw array, χωρίς εγγραφή στη βάση)
						$factoryAttributes = ReturnItemFactory::new()->raw([
							'stock_return_id' => $return->id,
							'product_id'      => $productId,
						]);

						// Εφαρμογή της custom business logic για το location & restock ημερομηνία
						if ($isCompleted && $warehouseLocations && $warehouseLocations->isNotEmpty()) {
							$factoryAttributes['location_id']  = $warehouseLocations->random()->id;
							$factoryAttributes['restocked_at'] = now()->subHours(mt_rand(1, 72))->format('Y-m-d H:i:s');
						} else {
							$factoryAttributes['location_id']  = null;
							$factoryAttributes['restocked_at'] = null;
						}

						// Προσθήκη στον buffer για μαζικό insert
						$insertBuffer[] = $factoryAttributes;
					}
				}

				// 4. Bulk insert όλου του chunk απευθείας στη βάση δεδομένων
				if (!empty($insertBuffer)) {
					try {
						DB::transaction(function () use ($insertBuffer) {
							StockReturnItem::query()->insert($insertBuffer);
						});
					} catch (Throwable $e) {
						$this->command->error("Failed to insert stock return items chunk: " . $e->getMessage());
					}
				}
			});

			$this->command->info("✅ Stock Return Items seeding complete! Current Total: " . StockReturnItem::query()->count());
		}
	}