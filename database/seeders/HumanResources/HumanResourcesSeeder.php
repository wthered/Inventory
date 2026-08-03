<?php

	namespace Database\Seeders\HumanResources;

	use App\Enums\HumanResources\CompanyDepartments;
	use App\Models\HumanResources\Attendance;
	use App\Models\HumanResources\Department;
	use App\Models\HumanResources\Employee;
	use App\Models\HumanResources\LeaveRequest;
	use App\Models\HumanResources\Position;
	use Illuminate\Database\Seeder;

	class HumanResourcesSeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			// 1. Δημιουργία Τμημάτων (από το Enum) & Θέσεων Εργασίας
			foreach (CompanyDepartments::cases() as $deptEnum) {
				$department = Department::factory()->create([
					'name' => $deptEnum->value,
					'code' => $deptEnum->code(),
				]);

				// Δημιουργούμε 3 θέσεις εργασίας για κάθε τμήμα
				$positions = Position::factory(3)->create([
					'department_id' => $department->id,
				]);

				// 2. Δημιουργία Υπαλλήλων ανά Τμήμα
				// (Το EmployeeFactory φτιάχνει αυτόματα και το EmployeeDetail!)
				$employees = Employee::factory(4)->create([
					'department_id' => $department->id,
					'position_id'   => fn() => $positions->random()->id,
				]);

				// 3. Προσθήκη Παρουσιών (Attendances) & Αδειών (Leave Requests)
				foreach ($employees as $employee) {
					// 5 ημέρες παρουσίας
					Attendance::factory(5)->create([
						'employee_id'  => $employee->id,
						'warehouse_id' => $employee->warehouse_id,
					]);

					// 2 αιτήσεις αδείας (για να πάει και κάποιος για μπάνιο!)
					LeaveRequest::factory(2)->create([
						'employee_id' => $employee->id,
						'approved_by' => $employee->id,
					]);
				}
			}
		}
	}
