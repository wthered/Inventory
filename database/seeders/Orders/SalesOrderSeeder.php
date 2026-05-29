<?php

	namespace Database\Seeders\Orders;

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
			$customers      = Customer::pluck('id');
			$warehouses     = Warehouse::pluck('id');
			$products       = Product::all();
			$administrators = User::role('admin')->pluck('id');

			if ($products->isEmpty()) {
				$this->command->error("No products found. Run ProductSeeder first.");
				return;
			}

			foreach (Collection::range(1, 16)->shuffle() as $index) {
				$order = SalesOrder::query()->create([
					'order_number'   => 'SALE-' . now()->format('Y-m-d') . '-' . $index . '-' . Str::upper(Str::random(6)),
					'customer_id'    => $customers->random(),
					'warehouse_id'   => $warehouses->random(),
					'order_date'     => Carbon::now()->subDays(rand(1, 30)),
					'delivery_date'  => Carbon::now()->addDays(rand(1, 7)),
					'status'         => SalesOrderStatus::DRAFT,
					'payment_status' => PaymentStatus::UNPAID,
					'created_by'     => $administrators->random(),
					'notes'          => fake()->boolean(30) ? fake()->sentence() : null,
				]);

				$orderSubtotal = 0;
				$orderTax      = 0;

				foreach ($products->random(rand(2, 6)) as $product) {
					$quantity = rand(1, 5);
					$unitPrice  = $product->price ?? rand(50, 500);

					// --- Realistic Calculations ---
					$taxPercent = 24.00; // Τυπικός ΦΠΑ

					// Τυχαία έκπτωση 5-15% στο 20% των περιπτώσεων
					$discPercent = fake()->boolean(20) ? fake()->randomElement([5, 10, 15]) : 0;

					$subtotalBeforeDisc = $quantity * $unitPrice;
					$discAmount         = round($subtotalBeforeDisc * ($discPercent / 100), 2);
					$subtotalAfterDisc  = $subtotalBeforeDisc - $discAmount;
					$taxAmount          = round($subtotalAfterDisc * ($taxPercent / 100), 2);
					$totalItem          = $subtotalAfterDisc + $taxAmount;

					$order->items()->create([
						'sales_order_id'   => $order->id,
						'product_id'       => $product->id,
						'quantity'         => $quantity,
						'quantity_shipped' => 0,
						'unit_price'       => $unitPrice,
						'discount_percent' => $discPercent,
						'discount_amount'  => $discAmount,
						'tax_percent'      => $taxPercent,
						'tax_amount'       => $taxAmount,
						'subtotal'         => $subtotalAfterDisc,
						'total'            => $totalItem,
					]);

					$orderSubtotal += $subtotalAfterDisc;
					$orderTax      += $taxAmount;
				}

				// Ενημέρωση Header με τα σωστά σύνολα
				$order->update([
					'subtotal'     => $orderSubtotal,
					'tax_amount'   => $orderTax,
					'total_amount' => $orderSubtotal + $orderTax,
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
					$order->update(['status' => $newStatus]);

					// Αν είναι Shipped ή Delivered, μπορεί να έχει πληρωθεί κιόλας
					if ($newStatus === SalesOrderStatus::SHIPPED) {
						$order->update(['payment_status' => PaymentStatus::PAID]);
					}

					$this->command->info("Order ".$order->order_number." -> {$newStatus->label()} (Stock Syncing...)");
				}
			}
		}
	}
