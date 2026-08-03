<?php

	namespace Database\Factories\HumanResources;

	use App\Enums\HumanResources\CompanyDepartments;
	use App\Models\HumanResources\Department;
	use App\Models\User;
	use Illuminate\Database\Eloquent\Factories\Factory;

	/**
	 * @extends Factory<Department>
	 */
	class DepartmentFactory extends Factory {
		protected $model = Department::class;

		public function definition(): array {
			$department = fake()->unique()->randomElement(CompanyDepartments::cases());

			return [
				'name'        => $department->value,
				'code'        => $department->code(),
				'description' => fake()->sentence(),
				'manager_id'  => User::factory(),
			];
		}
	}