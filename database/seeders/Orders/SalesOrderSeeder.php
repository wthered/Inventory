<?php

	namespace Database\Seeders\Orders;

	use App\Enums\Financial\PaymentStatus;
	use App\Enums\Sales\SalesOrderStatus;
	use App\Models\Customer;
	use App\Models\Product;
	use App\Models\Sales\SalesOrder;
	use App\Models\User;
	use App\Models\Warehouse;
	use App\Models\WarehouseLocation;
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

			$initial_date = Carbon::parse('1st January last year');
			foreach (Collection::range(1, 1024 * 4)->shuffle() as $index) {
				$warehouse = $warehouses->random();
				$order = SalesOrder::query()->create([
					'order_number'      => 'SALE-'.$initial_date->addDays(mt_rand(1, 7))->format('Y-m-d').'-'.$index.'-'.Str::upper(Str::random(6)),
					'customer_id'       => $customers->random(),
					'warehouse_id'      => $warehouse,
					'status_id'         => SalesOrderStatus::DRAFT->value,
					'payment_status_id' => PaymentStatus::UNPAID->value,
					'order_date'        => Carbon::now()->subDays(rand(1, 30)),
					'shipping_date'     => Carbon::now()->addDays(rand(1, 7)),
					'created_by'        => $administrators->random(),
					'notes'             => fake()->optional()->sentence(),
				]);

				$orderSubtotal = 0;
				$orderTax = 0;
				$discount = 0;

				// Getting Warehouse locations for successful seeding
				$locations = WarehouseLocation::query()->where('warehouse_id', $warehouse)->pluck('id');

				foreach ($products->random(mt_rand(2, 6)) as $product) {
					$quantity = mt_rand(1, 8);
					$unitPrice = $product->price ?? mt_rand(32, 512);

					// --- Realistic Calculations ---
					$taxPercent = 24.00; // Τυπικός ΦΠΑ

					// Τυχαία έκπτωση 5 - 15% στο 20% των περιπτώσεων
					$discPercent = fake()->boolean(20) ? fake()->randomElement([5, 10, 15]) : 0;

					$subtotalBeforeDisc = $quantity * $unitPrice;
					$discAmount = round($subtotalBeforeDisc * ($discPercent / 100), 2);
					$subtotalAfterDisc = $subtotalBeforeDisc - $discAmount;
					$taxAmount = round($subtotalAfterDisc * ($taxPercent / 100), 2);
					$shippedQuantity = mt_rand(1, $quantity);

					$order->items()->create([
						'product_id'       => $product->id,
						'batch_number'     => fake()->boolean(70) ? 'BAT-'.Str::upper(Str::random(5)) : null,
						'location_id'      => $locations->random(),
						'quantity_ordered' => $quantity,
						'quantity_shipped' => $shippedQuantity,
						'unit_price'       => $unitPrice,
						'discount_rate'    => $discPercent,
						'discount_amount'  => $discAmount,
						'total_ordered_price' => $unitPrice * $quantity,
						'total_shipped_price' => $unitPrice * $shippedQuantity,
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
					$order->update(['status_id' => $newStatus]);

					// Αν είναι Shipped ή Delivered, μπορεί να έχει πληρωθεί κιόλας
					if ($newStatus === SalesOrderStatus::SHIPPED) {
						$order->update(['payment_status_id' => PaymentStatus::PAID]);
					}

					$this->command->getOutput()->write("Order ".$order->order_number." -> {$newStatus->label()} (Stock Syncing...)\r");
				}
			}
		}
	}
