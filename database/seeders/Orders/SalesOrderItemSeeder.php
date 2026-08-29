<?php

	namespace Database\Seeders\Orders;

	use App\DataTransferObjects\ProductDTO;
	use App\Enums\Sales\SalesOrderStatus;
	use App\Models\Product;
	use App\Models\Sales\SalesOrder;
	use Illuminate\Database\Seeder;
	use Illuminate\Support\Collection;

	class SalesOrderItemSeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			// Παίρνουμε όλες τις παραγγελίες
			$sales = SalesOrder::all();

			// Παίρνουμε διαθέσιμα προϊόντα και τοποθεσίες αποθήκης
			$products = Product::all();
//			$locations = WarehouseLocation::all()->pluck('id');

			// Warehouses is a collection of Warehouse DTOs
//			$warehouses = Warehouse::query()->get()->map(function (Warehouse $warehouse) {
//				return WarehouseDTO::fromModel($warehouse);
//			});

			if ($products->isEmpty()) {
				$this->command->warn('Δεν βρέθηκαν προϊόντα. Παρακαλώ τρέξτε πρώτα τον ProductSeeder.');
				return;
			}

			// 1. Chunk the collection into subsets
			foreach ($sales->chunk(128) as $salesChunk) {
				foreach ($salesChunk as $sale) {
					// Initialize Random Product
					$randomProducts = Collection::empty();

					// 1. Filter products belonging to the sales order warehouse
					$products = Product::query()->whereHas('warehouses', function ($query) use ($sale) {
						$query->where('warehouses.id', $sale->warehouse_id);
					})->get();

					// 2. Safely pick random items without exceeding available count
					if ($products->isNotEmpty()) {
						$count = min($products->count(), mt_rand(1, 4));
						$randomProducts = $products->random($count);
					}

					foreach ($randomProducts as $product) {
						$product_object = new ProductDTO($product);
						// 1. Calculate available inventory count safely
						$availableCount = $product_object->inventories->getCollection()
						                                              ->where('warehouse_id', $sale->warehouse_id)
						                                              ->count();

						// 2. Set bounds safely so min is always <= max
						$minQty = 2;
						$maxQty = max($minQty, $availableCount);

						$qtyOrdered = rand($minQty, $maxQty);

						// Αν η παραγγελία έχει προχωρήσει πέρα από το Processing/Shipped, βάζουμε shipped quantity
						$qtyShipped = 0;

						if (in_array($sale->status_id?->value, [
							SalesOrderStatus::PROCESSING->value,
							SalesOrderStatus::SHIPPED->value,
							SalesOrderStatus::DELIVERED->value
						])) {
							$qtyShipped = $qtyOrdered;
						}

						// Τυχαίο ποσοστό έκπτωσης (0%, 5%, 10%, 20%)
						$discountRate = Collection::make([0, 5, 10, 15, 20])->random();

						// Υπολογισμός της αξίας της έκπτωσης ανά μονάδα προϊόντος
						$unitPrice = $product_object->cost_price ?? mt_rand(12, 512);
						$discountAmountPerUnit = $unitPrice * ($discountRate / 100);
						$totalDiscountAmount = $discountAmountPerUnit * $qtyOrdered;

						// Getting all locations in specific location
						$locations = $product_object->locations->where('warehouse_id', $sale->warehouse_id)->pluck('warehouse_locations.id');

						// Εισαγωγή μέσω της σχέσης $sale->items()
						$sale->items()->create([
							'product_id'       => $product_object->id,
							'batch_number'     => Product::generateBatchNumber(),
							'location_id'      => $locations->isNotEmpty() ? $locations->random() : null,
							'quantity_ordered' => $qtyOrdered,
							'quantity_shipped' => $qtyShipped,
							'unit_price'       => $unitPrice,
							'discount_rate'    => $discountRate,
							'discount_amount'  => $totalDiscountAmount,
						]);
					}

					$this->updateOrderTotals($sale);
				}
			}
		}

		/**
		 * Helper για τον υπολογισμό και την ενημέρωση των συνόλων της master παραγγελίας
		 */
		private function updateOrderTotals(SalesOrder $sale): void {
			// Κάνουμε refresh για να διαβάσει τα items που μόλις προσθέσαμε
			$sale->load('items');

			$subtotal = 0;
			$totalDiscount = 0;

			foreach ($sale->items()->get() as $item) {
				$subtotal += $item->quantity_ordered * $item->unit_price;
				$totalDiscount += $item->discount_amount;
			}

			$taxAmount = ($subtotal - $totalDiscount) * 0.24; // Υποθέτοντας ΦΠΑ 24%
			$grandTotal = ($subtotal - $totalDiscount) + $taxAmount;

			// update `sales_orders` set `subtotal` = 11559, `tax_amount` = 2726.376, `discount_amount` = 199.1, `grand_total` = 14086.276, `total_amount` = 0,
			$sale->update([
				'subtotal'        => $subtotal,
				'discount_amount' => $totalDiscount,
				'tax_amount'      => $taxAmount,
				'grand_total'     => $grandTotal,
			]);
			$this->command->getOutput()->write('Updating Order totals for sale '.$sale->id."...\r");
		}
	}
