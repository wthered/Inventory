<?php

	namespace Database\Seeders\Purchases;

	use App\Enums\Financial\PaymentStatus;
	use App\Enums\Sales\SalesOrderStatus;
	use App\Models\Purchases\PurchaseOrder;
	use App\Models\Supplier;
	use App\Models\User;
	use App\Models\Warehouse;
	use Illuminate\Database\Seeder;
	use Illuminate\Support\Str;

	class PurchaseOrderSeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			// Ensure related tables have data
			if (!Supplier::query()->exists() || !Warehouse::query()->exists() || !User::query()->exists()) {
				$this->command->warn('⚠️ Missing suppliers, warehouses, or users. Skipping PurchaseOrderSeeder.');
				return;
			}

			$suppliers = Supplier::query()->pluck('id')->toArray();
			$warehouses = Warehouse::query()->pluck('id')->toArray();
			$users = User::role('sales_representative')->pluck('id');

			for ($order = 0; $order <= mt_rand(128, 1024); $order++) {
				$orderDate = fake()->dateTimeBetween('-6 months');
				$subtotal = fake()->randomFloat(2, 500, 5000);
				$tax = fake()->randomFloat(2, 50, 500);
				$discount = fake()->randomFloat(2, 0, 200);
				$shipping = fake()->randomFloat(2, 20, 200);

				$total = ($subtotal + $tax + $shipping) - $discount;

				$order_status = fake()->randomElement(SalesOrderStatus::cases());
				$payment_status = fake()->randomElement(PaymentStatus::cases());
				PurchaseOrder::query()->create([
					'order_number'           => Str::upper(Str::random(10)),
					'supplier_id'            => fake()->randomElement($suppliers),
					'warehouse_id'           => fake()->randomElement($warehouses),
					'order_date'             => $orderDate->format('Y-m-d'),
					'expected_delivery_date' => fake()->optional()->dateTimeBetween($orderDate, '+2 weeks')?->format('Y-m-d'),
					'actual_delivery_date'   => fake()->optional()->dateTimeBetween($orderDate, '+1 month')?->format('Y-m-d'),
					'subtotal'               => $subtotal,
					'tax_amount'             => $tax,
					'discount_amount'        => $discount,
					'shipping_cost'          => $shipping,
					'total_amount'           => $total,
					'status'                 => $order_status->value,
					'payment_status'         => $payment_status->value,
					'notes'                  => fake()->optional()->sentences(mt_rand(1, 5), true),
					'reference_number'       => fake()->optional()->uuid(),
					'created_by'             => $users->random(),
					'approved_by'            => User::role(['admin', 'warehouse_manager'])->pluck('id')->random(),
					'approved_at'            => fake()->optional()->dateTimeBetween($orderDate, '+1 month'),
				]);
			}
			$this->command->info('✅ ' . PurchaseOrder::query()->count() . ' Purchase Orders created successfully!');
		}
	}
