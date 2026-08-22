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

			// We want
			$initial_date = Carbon::today()->subDays($orders_seeded / 8);

			foreach (Collection::range(1, $orders_seeded)->shuffle() as $index) {
				$warehouse = $warehouses->random();
				$order_date = $initial_date->addDays(mt_rand(1, 7));
				$order = SalesOrder::query()->create([
					'order_number'      => 'SALE-'.$order_date->format('Y-m-d').'-'.$index.'-'.Str::upper(Str::random(6)),
					'customer_id'       => $customers->random(),
					'warehouse_id'      => $warehouse,
					'status_id'         => SalesOrderStatus::DRAFT->value,
					'payment_status_id' => PaymentStatus::UNPAID->value,
					'order_date'        => $order_date,
					'shipping_date'     => $order_date->copy()->addDays(rand(1, 7)),
					'created_by'        => $administrators->random(),
					'notes'             => $this->createNote(),
					'created_at'        => $order_date,
					'updated_at'        => $order_date->copy()->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59)),
				]);

				$orderSubtotal = 0;
				$orderTax = 0;
				$discount = 0;

				// Get all products stocked inside the selected warehouse
				$products = Product::query()
				                   ->whereHas('locations', function ($query) use ($warehouse) {
					                   $query->where('inventories.warehouse_id', $warehouse);
				                   })->get();

				foreach ($products->random(mt_rand(4, 12)) as $randomProduct) {
					$product = new ProductDTO($randomProduct->id);
					$quantity = mt_rand(1, $product->current_stock);
					$unitPrice = $product->price ?? mt_rand(32, 512);

					// --- Realistic Calculations ---
					$taxPercent = $product->tax_percent;

					// Τυχαία έκπτωση 5 - 15% στο 20% των περιπτώσεων
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
						// The value specified for generated column 'total_ordered_price' in table 'sales_order_items' has been ignored
						//						'total_ordered_price' => $unitPrice * $quantity,
						// The value specified for generated column 'total_shipped_price' in table 'sales_order_items' has been ignored
						//						'total_shipped_price' => $unitPrice * $shippedQuantity,
					]);

					$orderSubtotal += $subtotalAfterDisc;
					$orderTax += $taxAmount;
					$discount += $discAmount;
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

					$this->command->getOutput()->write("Order ".$order->order_number." -> ".$newStatus->label()."...\r");
				}
			}
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
