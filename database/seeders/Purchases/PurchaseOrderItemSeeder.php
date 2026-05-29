<?php

	namespace Database\Seeders\Purchases;

	use App\Enums\Sales\SalesOrderStatus;
	use App\Models\Product;
	use App\Models\Purchases\PurchaseOrder;
	use App\Models\Purchases\PurchaseOrderItem;
	use Carbon\Carbon;
	use Database\Seeders\ParentSeeder;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\DB;
	use Throwable;

	class PurchaseOrderItemSeeder extends ParentSeeder {

		/**
		 * @throws Throwable
		 */
		public function run(): void {
			if (!PurchaseOrder::exists() || !Product::exists()) {
				$this->command->warn('⚠️ Skipping PurchaseOrderItemSeeder: Missing data.');
				return;
			}

			DB::transaction(function () {
				// Eager load suppliers με το pivot table 'price'
				$productsWithSuppliers = Product::with('suppliers')->get();

				PurchaseOrder::all()->each(function (PurchaseOrder $order) use ($productsWithSuppliers) {
					$this->list = Collection::empty();
					$orderTotal = 0;

					// Τυχαία 4 έως 16 προϊόντα ανά παραγγελία
					$selectedProducts = $productsWithSuppliers->random(mt_rand(4, 16));

					foreach ($selectedProducts as $productModel) {
						$suppliers = $productModel->suppliers;

						// Καθορισμός τιμής (Unit Cost)
						if ($suppliers->isEmpty()) {
							$unitCost = round(mt_rand(100, 5000) / 100, 2);
						} else {
							$randomSupplier = $suppliers->random();
							$unitCost = $randomSupplier->pivot->price ?? round(mt_rand(100, 5000) / 100, 2);
						}

						$quantityOrdered = fake()->numberBetween(1, 128);

						// Υπολογισμοί Φόρων και Εκπτώσεων
						$discountPercent = fake()->randomFloat(2, 0, 15);
						$subtotalBeforeDiscount = $unitCost * $quantityOrdered;
						$discountAmount = round($subtotalBeforeDiscount * ($discountPercent / 100), 2);

						$taxPercent = fake()->randomFloat(2, 0, 24);
						$taxableAmount = $subtotalBeforeDiscount - $discountAmount;
						$taxAmount = round($taxableAmount * ($taxPercent / 100), 2);

						$rowTotal = $taxableAmount + $taxAmount;
						$orderTotal += $rowTotal;

						$orderTime = Carbon::now(config('app.timezone'))->subHours(rand(0, 23))->subMinutes(rand(0, 59));

						$this->list->push([
							'purchase_order_id' => $order->id,
							'product_id'        => $productModel->id, // Διορθώθηκε από $product
							'quantity_ordered'  => $quantityOrdered,
							'quantity_received' => fake()->numberBetween(0, $quantityOrdered),
							'unit_cost'         => $unitCost,
							'discount_percent'  => $discountPercent,
							'discount_amount'   => $discountAmount,
							'tax_percent'       => $taxPercent,
							'tax_amount'        => $taxAmount,
							'subtotal'          => $taxableAmount,
							'total'             => $rowTotal, // Διορθώθηκε το hardcoded 5
							'created_at'        => $orderTime->toDateTimeString(),
							'updated_at'        => $orderTime->copy()->addMinutes(mt_rand(10, 59))->addSeconds(mt_rand(0, 59))->toDateTimeString()
						]);
					}

					// Bulk insert τα items της συγκεκριμένης παραγγελίας
					PurchaseOrderItem::insert($this->list->toArray());

					// Ενημέρωση της παραγγελίας με το σωστό συνολικό ποσό
					$order->update([
						'total_amount' => $orderTotal, // Υποθέτοντας ότι έχεις αυτό το field
						'status'       => fake()->randomElement(SalesOrderStatus::cases())->value,
						'updated_at'   => Carbon::now(config('app.timezone'))->setHours(mt_rand(9,17))->setMinutes(mt_rand(0,59))->setSeconds(mt_rand(0,59))->toDateTimeString()
					]);
				});
			});

			$this->command->info('✅ Purchase Order Items seeded successfully with correct totals.');
		}
	}
