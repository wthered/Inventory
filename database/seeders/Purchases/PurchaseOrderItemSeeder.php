<?php

	namespace Database\Seeders\Purchases;

	use App\Enums\Purchases\PurchaseOrderStatus;
	use App\Models\Product;
	use App\Models\Purchases\PurchaseOrder;
	use App\Models\Purchases\PurchaseOrderItem;
	use App\Models\WarehouseLocation;
	use Carbon\Carbon;
	use Database\Seeders\ParentSeeder;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Str;
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
				$products = Product::all();

				// Φέρνουμε τα διαθέσιμα Bins (Warehouse Locations) για να αντιστοιχίσουμε κατά την παραλαβή
				$locations = WarehouseLocation::pluck('id')->toArray();

				PurchaseOrder::all()->each(function (PurchaseOrder $order) use ($products, $locations) {
					$this->list = Collection::empty();
					$orderSubtotal = 0;

					// Τυχαία 3 έως 12 προϊόντα ανά αγορά
					$selectedProducts = $products->random(mt_rand(3, 12));

					foreach ($selectedProducts as $productModel) {
						$quantityOrdered = mt_rand(10, 200);

						// Αν η παραγγελία είναι "Received" (Status 6), τότε quantity_received = quantity_ordered
						// Αν είναι "Partially Received" (Status 5), βάζουμε ένα τυχαίο μικρότερο νούμερο
						if ($order->status_id == PurchaseOrderStatus::RECEIVED) {
							$quantityReceived = $quantityOrdered;
						} elseif ($order->status_id == PurchaseOrderStatus::PARTIALLY_RECEIVED) {
							$quantityReceived = mt_rand(1, $quantityOrdered - 1);
						} else {
							$quantityReceived = 0;
						}

						$unitPrice = $productModel->cost_price > 0 ? $productModel->cost_price : round(mt_rand(500, 4500) / 100, 2);
						$discountRate = fake()->randomElement([0.00, 5.00, 7.5, 10.00]); // Ποσοστό έκπτωσης

						// Υπολογισμός για το subtotal της κεφαλίδας
						$netUnitPrice = $unitPrice * (1 - ($discountRate / 100));
						$orderSubtotal += ($quantityOrdered * $netUnitPrice);

						$orderTime = Carbon::parse($order->order_date)->setHours(rand(0, 23))->setMinutes(rand(0, 59))->setSeconds(mt_rand(0, 59));
						$productDate = today()->subDays(mt_rand(0, 365));

						$this->list->push([
							'purchase_order_id'  => $order->id,
							'product_id'         => $productModel->id,
							'batch_number'       => $quantityReceived > 0 ? 'BAT-'.$orderTime->format('Ymd').'-'.Str::upper(Str::random(3)) : null,
							'manufacturing_date' => $productDate,
							'expiry_date'        => $productDate->addDays(mt_rand(0, 365)),
							'location_id'        => $quantityReceived > 0 ? fake()->randomElement($locations) : null,
							'quantity_ordered'   => $quantityOrdered,
							'quantity_received'  => $quantityReceived,
							'unit_price'         => $unitPrice,
							'discount_rate'      => $discountRate,
							'created_at'         => $orderTime,
							'updated_at'         => $orderTime->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59)),
						]);
					}

					// Bulk insert τα items της συγκεκριμένης παραγγελίας
					PurchaseOrderItem::insert($this->list->toArray());

					// Υπολογισμός και ακριβής ενημέρωση της Master εγγραφής με βάση τα πραγματικά items
					$taxAmount = $orderSubtotal * 0.24;
					$order->update([
						'subtotal'    => $orderSubtotal,
						'tax_amount'  => $taxAmount,
						'grand_total' => $orderSubtotal + $taxAmount - $order->discount_amount,
					]);
				});
			});

			$this->command->info('✅ PurchaseOrderItemSeeder completed with correct calculations.');
		}
	}