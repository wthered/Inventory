<?php

	namespace Database\Seeders\HumanResources;

	use App\Models\HumanResources\Employee;
	use App\Models\HumanResources\LeaveRequest;
	use App\Models\User;
	use Illuminate\Database\Seeder;

	class LeaveRequestSeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			$employees = Employee::all();
			$users = User::all();

			if ($employees->isEmpty()) {
				return;
			}

			foreach ($employees as $employee) {
				// Δημιουργούμε 2 αιτήσεις αδείας για κάθε υπάλληλο
				LeaveRequest::factory(2)->create([
					'employee_id' => $employee->id,
					'approved_by' => $users->isNotEmpty() && fake()->boolean(mt_rand(66, 75)) ? $users->random()->id : null,
				]);
			}
		}
	}
