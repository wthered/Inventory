<?php

	namespace Database\Seeders\Orders;

	use App\Enums\Financial\PaymentStatus;
	use App\Enums\Sales\SalesOrderStatus;
	use App\Models\Customer;
	use App\Models\Sales\SalesOrder;
	use App\Models\User;
	use App\Models\Warehouse;
	use Carbon\Carbon;
	use Database\Seeders\ParentSeeder;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Str;

	class SalesOrderSeeder extends ParentSeeder {
		public function run(): void {
			DB::disableQueryLog();

			$customers = Customer::query()->pluck('id');
			if ($customers->isEmpty()) {
				$this->command->error("No customers found. Run CustomerSeeder first.");
				return;
			}

			$warehouses     = Warehouse::query()->whereHas('locations')->pluck('id');
			$administrators = User::role('admin')->pluck('id');

			$orders_seeded = 2 * self::BATCH_SIZE;
			$initial_date  = Carbon::today()->subDays($orders_seeded / 8);

			Collection::range(1, $orders_seeded)->chunk(self::BATCH_SIZE)->each(function ($chunk) use ($warehouses, $customers, $administrators, $initial_date) {
				DB::transaction(function () use ($chunk, $warehouses, $customers, $administrators, $initial_date) {
					foreach ($chunk as $index) {
						$warehouse  = $warehouses->random();
						$order_date = $initial_date->copy()->addDays(mt_rand(1, 7));

						$order = SalesOrder::query()->create([
							'order_number'      => 'SALE-' . $order_date->format('Y-m-d') . '-' . $index . '-' . Str::upper(Str::random(6)),
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

						if (mt_rand(1, 10) > 3) {
							$newStatus = fake()->randomElement([
								SalesOrderStatus::CONFIRMED,
								SalesOrderStatus::PROCESSING,
								SalesOrderStatus::SHIPPED
							]);

							$order->update(['status_id' => $newStatus->value]);

							if ($newStatus === SalesOrderStatus::SHIPPED) {
								$order->update(['payment_status_id' => PaymentStatus::PAID->value]);
							}
						}
					}
				});

				// Explicitly free memory per chunk if needed
				gc_collect_cycles();
			});

			$this->command->info("Sales orders seeded successfully!");
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
