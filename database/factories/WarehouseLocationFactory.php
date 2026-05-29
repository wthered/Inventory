<?php

	namespace Database\Factories;

	use App\Models\WarehouseLocation;
	use Illuminate\Database\Eloquent\Factories\Factory;
	use Illuminate\Support\Carbon;

	class WarehouseLocationFactory extends Factory {
		protected $model = WarehouseLocation::class;

		public function definition(): array {
			return [
				'warehouse_id' => $this->faker->randomNumber(),
				'code'         => $this->faker->word(),
				'name'         => $this->faker->name(),
				'zone'         => $this->faker->word(),
				'aisle'        => $this->faker->word(),
				'rack'         => $this->faker->randomNumber(),
				'shelf'        => $this->faker->randomNumber(),
				'bin'          => $this->faker->randomNumber(),
				'description'  => $this->faker->text(),
				'is_active'    => $this->faker->boolean(),
				'created_at'   => Carbon::now(),
				'updated_at'   => Carbon::now(),
			];
		}
	}
