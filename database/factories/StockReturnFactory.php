<?php

	namespace Database\Factories;

	use App\Models\Product;
	use App\Models\StockReturn;
	use App\Models\User;
	use App\Models\Warehouse;
	use App\Models\WarehouseLocation;
	use Carbon\Carbon;
	use Illuminate\Database\Eloquent\Factories\Factory;

	/**
	 * @extends Factory<StockReturn>
	 */
	class StockReturnFactory extends Factory {
		protected $model = StockReturn::class;

		public function definition(): array {
			// Select a warehouse first so the location matches
			$warehouses = Warehouse::get()->pluck('id');
			$products   = Product::get()->pluck('id');
			$users      = User::get()->pluck('id');

			$return_date = Carbon::make($this->faker->date());
			return [
				'product_id'   => $products->random(),
				'warehouse_id' => $warehouses,
				'location_id'  => WarehouseLocation::where('warehouse_id', $warehouses)->get()->shuffle()->first(),

				'quantity'  => fake()->numberBetween(1, 64),
				'unit_cost' => fake()->randomFloat(2, 5, 200),

				'return_reason'  => fake()->randomElement([
					'defective',
					'wrong_item',
					'quality',
					'other'
				]),
				'status'         => fake()->randomElement([
					'pending',
					'received',
					'completed'
				]),
				'quality_status' => fake()->randomElement([
					'new',
					'opened',
					'damaged',
					'defective'
				]),

				// 80% of returns are restockable
				'is_restockable' => fake()->boolean(80),
				'return_date'    => $return_date->subHours(mt_rand(1,23))->subMinutes(mt_rand(1,59))->subSeconds(mt_rand(1,59))->timezone(config('app.timezone'))->toIso8601String(),

				'created_by' => $users->shuffle()->first(),
				'notes'      => fake()->sentences(mt_rand(1, 4), true),
			];
		}

		/**
		 * State for a defective return that needs disposal.
		 */
		public function defective(): static {
			return $this->state(fn(array $attributes) => [
				'quality_status'    => 'defective',
				'is_restockable'    => false,
				'status'            => 'disposed',
				'requires_disposal' => true,
			]);
		}
	}
