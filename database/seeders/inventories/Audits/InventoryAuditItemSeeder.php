<?php

	namespace Database\Seeders\inventories\Audits;

	use App\Enums\Inventory\AdjustmentType;
	use App\Models\Product;
	use Database\Seeders\ParentSeeder;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\DB;

	class InventoryAuditItemSeeder extends ParentSeeder {
		public function run(): void {
			$audits = DB::table('inventory_audits')->get();
			$products = Product::select('id', 'current_stock')->get();

			// Παίρνουμε τις θέσεις αποθήκης (locations)
			$locations = DB::table('warehouse_locations')->pluck('id');

			if ($products->isEmpty() || $locations->isEmpty()) return;

			$this->list = Collection::empty();

			foreach ($audits as $audit) {
				// Για κάθε απογραφή, διαλέγουμε 16 - 32 τυχαία προϊόντα
				$selectedProducts = $products->random(mt_rand(16, 32));

				foreach ($selectedProducts as $product) {
					$systemQty = $product->current_stock;

					// Προσομομοίωση μέτρησης:
					// 75% πιθανότητα να είναι σωστό, 25% να υπάρχει διαφορά
					$isCorrect = fake()->boolean(70);
					$physicalQty = $isCorrect ? $systemQty : $systemQty + mt_rand(-10, 10);

					// Περιορισμός ώστε να μην έχουμε αρνητική φυσική ποσότητα
					$physicalQty = max(0, $physicalQty);
					$discrepancy = $physicalQty - $systemQty;

					$this->list->push([
						'inventory_audit_id' => $audit->id,
						'product_id'         => $product->id,
						'location_id'        => $locations->random(),
						'system_quantity'    => $systemQty,
						'physical_quantity'  => $physicalQty,
						'discrepancy'        => $discrepancy,
						'adjustment_status'  => $audit->status === 'completed' ? AdjustmentType::ADJUSTMENT->value : AdjustmentType::PENDING->value,
						'created_at'         => now(),
						'updated_at'         => now(),
					]);
				}
			}

			// Insert σε chunks για ασφάλεια
			$this->list->chunk(self::BATCH_SIZE)->each(function (Collection $chunk) {
				DB::table('inventory_audit_items')->insert($chunk->toArray());
			});
		}
	}
