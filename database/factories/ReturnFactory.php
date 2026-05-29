<?php

	namespace Database\Factories;

	use App\Models\Product;
	use App\Models\ReturnModel;
	use Illuminate\Database\Eloquent\Factories\Factory;

	/**
	 * @extends Factory<ReturnModel>
	 */
	class ReturnFactory extends Factory {
		/**
		 * Define the model's default state.
		 *
		 * @return array<string, mixed>
		 */
		protected $model = ReturnModel::class;

		public function definition(): array {
			return [
				'return_number' => ReturnModel::generateReturnNumber(),
				'product_id'    => Product::factory(),
				'quantity'      => $this->faker->randomFloat(3, 1, 100),
				'unit_cost'     => $this->faker->randomFloat(2, 10, 500),
				'return_reason' => $this->faker->randomElement([
					'defective',
					'wrong_item',
					'customer_change',
					'quality',
					'other'
				]),
				'status'        => $this->faker->randomElement([
					'pending',
					'processing',
					'processed',
					'rejected'
				]),
				'notes'         => $this->faker->sentence(),
				'return_date'   => $this->faker->dateTimeBetween('-30 days', 'now'),
			];
		}
	}
