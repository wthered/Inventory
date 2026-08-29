<?php

	namespace Database\Seeders\Orders;

	use App\DataTransferObjects\ProductDTO;
	use App\Enums\Financial\PaymentStatus;
	use App\Enums\Sales\SalesOrderStatus;
	use App\Models\Customer;
	use App\Models\Product;
	use App\Models\Sales\SalesOrder;
	use App\Models\User;
	use App\Models\Warehouse;
	use Carbon\Carbon;
	use Illuminate\Database\Seeder;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Str;

	class SalesOrderSeeder extends Seeder {
		public function run(): void {
			$customers = Customer::query()->pluck('id');

			if ($customers->isEmpty()) {
				$this->command->error("No customers found. Run CustomerSeeder first.");
				return;
			}

			$warehouses = Warehouse::query()->whereHas('locations')->pluck('id');
			$products = Product::all();
			$administrators = User::role('admin')->pluck('id');

			if ($products->isEmpty()) {
				$this->command->error("No products found. Run ProductSeeder first.");
				return;
			}

			$orders_seeded = 4 * 1024;

			// Step 1: Calculate the starting point in the past based on total orders
			$days_per_order = 3; // Average spacing between orders
			$current_date = Carbon::today()
			                      ->subDays($orders_seeded * $days_per_order)
			                      ->setTime(mt_rand(0, 23), mt_rand(0, 59), mt_rand(0, 59));

			// Step 2: Loop sequentially using Collection::range()
			Collection::range(1, $orders_seeded)->each(function ($index) use (
				&$current_date,
				$warehouses,
				$customers,
				$administrators,
				$orders_seeded
			) {
				// Step forward by 1 to 4 days + random time increments for realism
				$current_date->addDays(mt_rand(1, 4))
				             ->addHours(mt_rand(1, 8))
				             ->addMinutes(mt_rand(0, 59))
				             ->addSeconds(mt_rand(0, 59));
				$warehouse = $warehouses->random();

				$order_date = $current_date->copy();

				$order = SalesOrder::query()->create([
					'order_number'      => 'SALE-'.$order_date->format('Y-m-d').'-'.Str::upper(Str::random(4)),
					'customer_id'       => $customers->random(),
					'warehouse_id'      => $warehouses->random(),
					'status_id'         => SalesOrderStatus::DRAFT->value,
					'payment_status_id' => PaymentStatus::UNPAID->value,
					'order_date'        => $order_date,
					'shipping_date'     => $order_date->copy()->addDays(mt_rand(1, 5)),
					'created_by'        => $administrators->random(),
					'notes'             => $this->createNote(),
					'created_at'        => $order_date,
					'updated_at'        => $order_date->copy()->addMinutes(mt_rand(15, 120)),
				]);

				$orderSubtotal = 0;
				$orderTax = 0;
				$discount = 0;

				// Get all products stocked inside the selected warehouse
				$products = Product::query()->whereHas('locations', function ($query) use ($warehouse) {
					$query->where('inventories.warehouse_id', $warehouse);
				})->get();

				if ($products->isNotEmpty()) {
					$warehouseProducts = $products->random(
						mt_rand(
							min(4, $products->count()),
							min(12, $products->count())
						)
					);
					foreach ($warehouseProducts as $randomProduct) {
						$product = new ProductDTO($randomProduct->id);
						$quantity = mt_rand(1, max(1, $product->current_stock));
						$unitPrice = $product->price ?? mt_rand(32, 512);

						// --- Realistic Calculations ---
						$taxPercent = $product->tax_percent;

						// Τυχαία έκπτωση 2.5 - 15% στο 20% των περιπτώσεων
						$discPercent = fake()->boolean(20) ? fake()->randomElement([2.5, 5, 7.5, 10, 12.5, 15]) : 0;

						$subtotalBeforeDisc = $quantity * $unitPrice;
						$discAmount = round($subtotalBeforeDisc * ($discPercent / 100), 2);
						$subtotalAfterDisc = $subtotalBeforeDisc - $discAmount;
						$taxAmount = round($subtotalAfterDisc * ($taxPercent / 100), 2);
						$shippedQuantity = mt_rand(1, $quantity);

						$order->items()->create([
							'product_id'       => $product->id,
							'batch_number'     => Product::generateBatchNumber(),
							'location_id'      => $product->locations->where('warehouse_id', $warehouse)->pluck('id')->random(),
							'quantity_ordered' => $quantity,
							'quantity_shipped' => $shippedQuantity,
							'unit_price'       => $unitPrice,
							'discount_rate'    => $discPercent,
							'discount_amount'  => $discAmount,
						]);

						$orderSubtotal += $subtotalAfterDisc;
						$orderTax += $taxAmount;
						$discount += $discAmount;
					} // <-- Fixed: Closed product items loop
				}

				// Ενημέρωση Header με τα σωστά σύνολα
				$order->update([
					'subtotal'        => $orderSubtotal,
					'tax_amount'      => $orderTax,
					'discount_amount' => $discount,
					'grand_total'     => $orderSubtotal + $orderTax - $discount,
				]);

				// --- TEST OBSERVER & REALISTIC QUANTITIES ---
				if (mt_rand(1, 10) > 3) {
					$newStatus = fake()->randomElement([
						SalesOrderStatus::CONFIRMED,
						SalesOrderStatus::PROCESSING,
						SalesOrderStatus::SHIPPED
					]);

					// Αν η παραγγελία θεωρείται Shipped, ενημέρωσε και το quantity_shipped
					if ($newStatus === SalesOrderStatus::SHIPPED) {
						$order->items()->each(function ($item) {
							$item->update(['quantity_shipped' => $item->quantity_ordered ?? 0]);
						});
					}

					// Το update αυτό θα πυροδοτήσει τον SalesOrderObserver
					$order->update(['status_id' => $newStatus->value]);

					// Αν είναι Shipped ή Delivered, μπορεί να έχει πληρωθεί κιόλας
					if ($newStatus === SalesOrderStatus::SHIPPED) {
						$order->update(['payment_status_id' => PaymentStatus::PAID->value]);
					}

					$this->command->getOutput()->write("[".$order->id." / ".$orders_seeded."] Order ".$order->order_number." -> ".$newStatus->label()."...\r");
				}
			}); // <-- Fixed: Closed Collection::range()->each() closure
		}

		private function createNote(): string {
			return Collection::make([
				// Delivery & Shipping Instructions
				'Call customer 30 minutes prior to delivery.',
				'Leave package with building reception if recipient is unavailable.',
				'Deliver strictly during morning business hours (09:00 - 12:00).',
				'Fragile items included. Handle with extra care during loading.',
				'Delivery driver requires liftgate assistance upon arrival.',

				// Payment & Invoicing
				'Payment pending upon invoice receipt.',
				'Paid in full via credit card transaction.',
				'Apply standard 10% commercial customer discount.',
				'Invoice to be mailed separately to accounting department.',

				// Warehouse & Order Processing
				'Customer requested partial shipment if items are backordered.',
				'Items packed in multiple cartons per customer request.',
				'Quality check required for item specifications prior to dispatch.',
				'Order created via phone support on customer request.',
				'Urgent dispatch: Priority processing requested.',

				// General & Customer Preferences
				'Customer will pick up directly from warehouse slot.',
				'First-time order: Includes welcome brochure and promotion items.',
				'Special packaging required for export standard compliance.',
				'Order modified per phone conversation with client.',
			])->random();
		}
	}
