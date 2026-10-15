<?php

	namespace Database\Seeders\HumanResources;

	use App\Models\HumanResources\Department;
	use App\Models\HumanResources\Employee;
	use App\Models\HumanResources\Position;
	use App\Models\User;
	use App\Models\Warehouse;
	use Illuminate\Database\Seeder;

	class EmployeeSeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			$departments = Department::all();
			$warehouses = Warehouse::all();
			$users = User::with('account')->get();

			if ($departments->isEmpty()) {
				return;
			}

			// 1. Δημιουργία Employee για ΟΛΟΥΣ τους υπάρχοντες Users (1-to-1)
			foreach ($users as $user) {
				$department = $departments->random();
				$positions = Position::query()->where('department_id', $department->id)->get();

				Employee::factory()->create([
					'user_id'       => $user->id,
					'first_name'    => $user->account?->first_name ?? $user->name,
					'last_name'     => $user->account?->last_name ?? 'User',
					'department_id' => $department->id,
					'position_id'   => $positions->isNotEmpty() ? $positions->random()->id : null,
					'warehouse_id'  => $warehouses->isNotEmpty() ? $warehouses->random()->id : null,
				]);
			}

			// 2. Δημιουργία επιπλέον Employees που ΔΕΝ έχουν User account (user_id = null)
			foreach ($departments as $department) {
				$positions = Position::query()->where('department_id', $department->id)->get();

				Employee::factory(3)->create([
					'user_id'       => null, // Δεν έχουν login στο σύστημα
					'department_id' => $department->id,
					'position_id'   => $positions->isNotEmpty() ? fn() => $positions->random()->id : null,
					'warehouse_id'  => $warehouses->isNotEmpty() ? fn() => $warehouses->random()->id : null,
				]);
			}
		}
	}
