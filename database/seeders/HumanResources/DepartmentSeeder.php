<?php

	namespace Database\Seeders\HumanResources;

	use App\Enums\HumanResources\CompanyDepartments;
	use App\Models\HumanResources\Department;
	use App\Models\User;
	use Illuminate\Database\Seeder;

	class DepartmentSeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			$users = User::all();

			foreach (CompanyDepartments::cases() as $deptEnum) {
				Department::factory()->create([
					'name'       => $deptEnum->value,
					'code'       => $deptEnum->code(),
					'manager_id' => $users->isNotEmpty() ? $users->random()->id : null,
				]);
			}
		}
	}
