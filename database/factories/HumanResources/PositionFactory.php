<?php

	namespace Database\Factories\HumanResources;

	use App\Models\HumanResources\Department;
	use App\Models\HumanResources\Position;
	use Illuminate\Database\Eloquent\Factories\Factory;

	/**
	 * @extends Factory<Position>
	 */
	class PositionFactory extends Factory {
		/**
		 * Define the model's default state.
		 *
		 * @return array<string, mixed>
		 */
		public function definition(): array {
			return [
				'department_id' => Department::factory(),
				'title'         => fake()->jobTitle(),
				'description'   => fake()->sentence(),
			];
		}
	}