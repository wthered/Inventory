<?php

	namespace Database\Seeders\Orders;

	use App\Enums\Sales\SalesOrderStatus;
	use App\Models\Product;
	use App\Models\Sales\SalesOrder;
	use App\Models\WarehouseLocation;
	use Illuminate\Database\Seeder;

	class SalesOrderItemSeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			// Παίρνουμε όλες τις παραγγελίες
			$sales = SalesOrder::all();

			// Παίρνουμε διαθέσιμα προϊόντα και τοποθεσίες αποθήκης
			$products = Product::all();
			$locations = WarehouseLocation::all()->pluck('id')->toArray();

			if ($products->isEmpty()) {
				$this->command->warn('Δεν βρέθηκαν προϊόντα. Παρακαλώ τρέξτε πρώτα τον ProductSeeder.');
				return;
			}

			foreach ($sales as $sale) {
				// Παίρνουμε τυχαία προϊόντα (π.χ. 1 έως 4) για αυτή την παραγγελία
				$randomProducts = $products->random(mt_rand(1, 4));

				foreach ($randomProducts as $product) {
					$qtyOrdered = rand(2, 12);

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
					$discountRate = collect([0, 5, 10, 20])->random();

					// Υπολογισμός της αξίας της έκπτωσης ανά μονάδα προϊόντος
					$unitPrice = $product->price ?? mt_rand(10, 500); // Χρήση τιμής προϊόντος ή fallback
					$discountAmountPerUnit = $unitPrice * ($discountRate / 100);
					$totalDiscountAmount = $discountAmountPerUnit * $qtyOrdered;

					// Εισαγωγή μέσω της σχέσης $sale->items()
					$sale->items()->create([
						'product_id'       => $product->id,
						'batch_number'     => collect([
							null, 'BATCH-'.mt_rand(128, 999), 'LOT-'.mt_rand(2026, 2030)
						])->random(),
						'location_id'      => !empty($locations) ? collect($locations)->random() : null,
						'quantity_ordered' => $qtyOrdered,
						'quantity_shipped' => $qtyShipped,
						'unit_price'       => $unitPrice,
						'discount_rate'    => $discountRate,
						'discount_amount'  => $totalDiscountAmount,
					]);
				}

				// ΠΡΟΑΙΡΕΤΙΚΟ: Αφού μπήκαν τα items, μπορούμε να κάνουμε update τα totals του SalesOrder
				// αν θέλεις να ευθυγραμμιστούν τα subtotal, grand_total κλπ της παραγγελίας στη βάση.
				$this->updateOrderTotals($sale);
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

			foreach ($sale->items as $item) {
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
		}
	}
