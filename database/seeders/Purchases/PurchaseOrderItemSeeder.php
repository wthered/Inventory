<?php

	namespace Database\Seeders\Purchases;

	use App\DataTransferObjects\ProductDTO;
	use App\Enums\Purchases\PurchaseOrderStatus;
	use App\Models\Product;
	use App\Models\Purchases\PurchaseOrder;
	use App\Models\Purchases\PurchaseOrderItem;
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
			if (!PurchaseOrder::query()->exists() || !Product::query()->exists()) {
				$this->command->warn("Purchase orders found:" . PurchaseOrder::query()->count());
				$this->command->warn("Products found:" . Product::query()->count());
				$this->command->warn('⚠️ Skipping PurchaseOrderItemSeeder: Missing data.');
				return;
			}

			DB::transaction(function () {
				$productsWithLocations = Product::query()->whereHas('locations')->pluck('id');

				PurchaseOrder::all()->each(function (PurchaseOrder $order) use ($productsWithLocations) {
					$this->list    = Collection::empty();
					$orderSubtotal = 0;

					// Τυχαία 3 έως 12 προϊόντα ανά αγορά
					$totalAvailable = $productsWithLocations->count();

					$products = Product::query()->whereIn('id', $productsWithLocations)->whereHas('inventories', function ($query) use ($order) {
						$query->where('warehouse_id', $order->warehouse_id);
					})->pluck('id');

					if ($products->isNotEmpty()) {
						$randomCount       = mt_rand(min(4, $totalAvailable), min(12, $totalAvailable));
						$availableProducts = Product::query()->whereIn('id', $products->random($randomCount))->get();
						$selectedProducts  = $availableProducts->map(function (Product $product) {
							return new ProductDTO($product);
						});
					} else {
						$selectedProducts = collect();
					}

//					dd($selectedProducts);

					foreach ($selectedProducts as $productModel) {
						$quantityOrdered = mt_rand(12, 256);

						// Αν η παραγγελία είναι "Received" (Status 6), τότε quantity_received = quantity_ordered
						// Αν είναι "Partially Received" (Status 5), βάζουμε ένα τυχαίο μικρότερο νούμερο
						if ($order->status_id == PurchaseOrderStatus::RECEIVED) {
							$quantityReceived = $quantityOrdered;
						} elseif ($order->status_id == PurchaseOrderStatus::PARTIALLY_RECEIVED) {
							$quantityReceived = mt_rand(1, $quantityOrdered - 1);
						} else {
							$quantityReceived = 0;
						}

//						dump($productModel->locations);

						$discountRate = fake()->randomElement([
							0.00,
							5.00,
							7.5,
							10.00
						]); // Ποσοστό έκπτωσης

						// Υπολογισμός για το subtotal της κεφαλίδας
						$netUnitPrice  = $productModel->cost_price * (1 - ($discountRate / 100));
						$orderSubtotal += ($quantityOrdered * $netUnitPrice);

						$orderTime   = $order->order_date->setHours(rand(0, 23))->setMinutes(rand(0, 59))->setSeconds(mt_rand(0, 59));
						$productDate = Carbon::today()->subDays(mt_rand(0, 365));

						$this->list->push([
							'purchase_order_id'  => $order->id,
							'product_id'         => $productModel->id,
							'batch_number'       => $quantityReceived > 0 ? 'BAT-' . $orderTime->format('Y-m-d') . '-' . Str::upper(Str::random(3)) : null,
							'manufacturing_date' => $productDate,
							'expiry_date'        => $productDate->addDays(mt_rand(0, 365)),
							'location_id'        => $quantityReceived > 0 && $productModel->locations->isNotEmpty() ? $productModel->locations->random()->id : null,
							'quantity_ordered'   => $quantityOrdered,
							'quantity_received'  => $quantityReceived,
							'unit_price'         => $productModel->cost_price,
							'discount_rate'      => $discountRate,
							'created_at'         => $orderTime,
							'updated_at'         => $orderTime->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59)),
						]);
					}

					// Bulk insert τα items της συγκεκριμένης παραγγελίας
					PurchaseOrderItem::query()->insert($this->list->toArray());

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
