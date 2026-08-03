<?php

	namespace Database\Factories\HumanResources;

	use App\Models\HumanResources\Department;
	use App\Models\HumanResources\Employee;
	use App\Models\HumanResources\EmployeeDetail;
	use App\Models\HumanResources\Position;
	use App\Models\User;
	use App\Models\Warehouse;
	use Illuminate\Database\Eloquent\Factories\Factory;

	/**
	 * @extends Factory<Employee>
	 */
	class EmployeeFactory extends Factory {
		/**
		 * Define the model's default state.
		 *
		 * @return array<string, mixed>
		 */
		public function definition(): array {
			return [
				'user_id'       => User::factory(),
				'department_id' => Department::factory(),
				'position_id'   => Position::factory(),
				'warehouse_id'  => Warehouse::query()->pluck('id')->random(),
				'employee_code' => 'EMP-'.fake()->unique()->numberBetween(1000, 9999),
				'first_name'    => fake()->firstName(),
				'last_name'     => fake()->lastName(),
				'phone'         => fake()->phoneNumber(),
				'hire_date'     => fake()->dateTimeBetween('-5 years', 'yesterday')->format('Y-m-d'),
				'is_active'     => true,
			];
		}

		/**
		 * Αυτόματη δημιουργία των 1-to-1 Details μόλις φτιαχτεί ο Employee!
		 */
		public function configure(): static {
			return $this->afterCreating(function (Employee $employee) {
				EmployeeDetail::factory()->create([
					'employee_id' => $employee->id,
				]);
			});
		}
	}