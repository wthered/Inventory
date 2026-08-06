<?php

	namespace Database\Seeders\HumanResources;

	use App\Models\HumanResources\Department;
	use App\Models\HumanResources\Position;
	use Illuminate\Database\Seeder;

	class PositionSeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			$departments = Department::all();

			foreach ($departments as $department) {
				Position::factory(3)->create([
					'department_id' => $department->id,
				]);
			}
		}
	}
