<?php

	namespace Database\Seeders\Stock;

	use App\Enums\Inventory\StockReturnStatus;
	use App\Models\Customer;
	use App\Models\StockReturn;
	use App\Models\Supplier;
	use App\Models\User;
	use App\Models\Warehouse;
	use Illuminate\Database\Seeder;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Str;

	class StockReturnSeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			$warehouses = Warehouse::query()->pluck('id');
			$users = User::query()->pluck('id');
			$customers = Customer::query()->pluck('id');
			$suppliers = Supplier::query()->pluck('id');

			if ($customers->isEmpty() || $suppliers->isEmpty()) {
				$this->command->error('Customers Found:'.$customers->count());
				$this->command->error('Suppliers Found:'.$suppliers->count());
				$this->command->error('Missing Customers and / or Suppliers! Seed them first.');
				return;
			}

			Collection::range(1, 24)->each(function (int $i) use ($warehouses, $users, $customers, $suppliers) {
				$type = fake()->randomElement([Customer::class, Supplier::class]);
				$targetId = ($type === Customer::class) ? $customers->random() : $suppliers->random();

				// Χρήση copy() για να μην "εκτοξευθεί" η ημερομηνία στο μέλλον
				$date = now()->subDays(mt_rand(1, 30));

				StockReturn::query()->create([
					'returnable_type' => $type,
					'returnable_id'   => $targetId,
					'warehouse_id'    => $warehouses->random(),
					'status'          => fake()->randomElement(StockReturnStatus::cases())->value,
					'return_date'     => $date->format('Y-m-d'),
					'tracking_number' => 'TRK-'.Str::upper(fake()->regexify('[A-Z]{2}[0-9]{9}GR')),
					'carrier'         => fake()->randomElement(['FedEx', 'UPS', 'DHL', 'ΕΛΤΑ']),
					'created_by'      => $users->random(),
				]);
			});
			$this->command->info('Seeded 24 Return Headers.');
		}
	}
