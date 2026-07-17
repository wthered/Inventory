<?php

	namespace Database\Factories;

	use App\Enums\Inventory\StockReturnStatus;
	use App\Models\Customer;
	use App\Models\StockReturn;
	use App\Models\Supplier;
	use App\Models\User;
	use App\Models\Warehouse;
	use Illuminate\Database\Eloquent\Factories\Factory;
	use Illuminate\Support\Str;

	/**
	 * @extends Factory<StockReturn>
	 */
	class ReturnFactory extends Factory {
		protected $model = StockReturn::class;

		public function definition(): array {
			$type = fake()->randomElement([Customer::class, Supplier::class]);

			return [
				'return_number'   => 'RET-' . Str::padLeft(mt_rand(1, 999999), 6, '0'),
				'rma_number'      => StockReturn::generateRmaNumber($this->model),
				'returnable_type' => $type,
				'returnable_id'   => function () use ($type) {
					return $type === Customer::class
						? (Customer::inRandomOrder()->first()?->id ?? Customer::factory())
						: (Supplier::inRandomOrder()->first()?->id ?? Supplier::factory());
				},
				'warehouse_id'    => fn () => Warehouse::inRandomOrder()->first()?->id ?? Warehouse::factory(),
				'status'          => fake()->randomElement(StockReturnStatus::cases())->value,
				'return_date'     => now()->subDays(mt_rand(1, 30))->format('Y-m-d'),
				'tracking_number' => 'TRK-' . Str::upper(fake()->regexify('[A-Z]{2}[0-9]{9}GR')),
				'carrier'         => fake()->randomElement(['FedEx', 'UPS', 'DHL', 'ΕΛΤΑ']),
				'created_by'      => fn () => User::inRandomOrder()->first()?->id ?? User::factory(),
			];
		}
	}