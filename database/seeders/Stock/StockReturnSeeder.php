<?php

	namespace Database\Seeders\Stock;

	use App\Enums\Inventory\StockReturnStatus;
	use App\Models\Customer;
	use App\Models\StockReturn;
	use App\Models\Supplier;
	use App\Models\User;
	use App\Models\Warehouse;
	use Carbon\Carbon;
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

			// Χρήση copy() για να μη "εκτοξευθεί" η ημερομηνία στο μέλλον
			$action_day = Carbon::today()->subDays(mt_rand(1, 30));

			Collection::range(1, 24)->each(function (int $i) use (
				$warehouses,
				$users,
				$customers,
				$suppliers,
				$action_day
			) {
				$type = fake()->randomElement([Customer::class, Supplier::class]);

				$action = StockReturn::query()->create([
					'returnable_type' => $type,
					'returnable_id'   => ($type === Customer::class) ? $customers->random() : $suppliers->random(),
					'return_number'   => StockReturn::generateReturnNumber(),
					'warehouse_id'    => $warehouses->random(),
					'status'          => fake()->randomElement(StockReturnStatus::cases())->value,
					'return_date'     => $action_day->addDays(mt_rand(1, $i)),
					'tracking_number' => 'TRK-'.Str::upper(fake()->regexify('[A-Z]{2}[0-9]{9}GR')),
					'carrier'         => fake()->randomElement(['FedEx', 'UPS', 'DHL', 'ΕΛΤΑ']),
					'created_by'      => $users->random(),
					'created_at'      => $action_day->subHours(mt_rand(0, 23))->subMinutes(mt_rand(0, 59))->subSeconds(mt_rand(0, 59)),
					'updated_at'      => $action_day->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59)),
				]);

				$action->update([
					'rma_number' => StockReturn::generateRmaNumber($action),
				]);
			});
			$this->command->info('Seeded 24 Return Headers.');
		}
	}
