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
			$departments = Department::query()->pluck('id');
			$warehouses = Warehouse::all();
			$usersCollection = User::query()->pluck('id');

			if ($departments->isEmpty()) {
				return;
			}

			for ($i = 1; $i <= $usersCollection->count(); $i++) {
				// Φιλτράρουμε τα διαθέσιμα IDs ώστε να εξαιρέσουμε το $i (αν υπάρχει ακόμα στη συλλογή)
				$available = $usersCollection->reject(fn($id) => $id === $i);

				// Αν λόγω εξαιρέσεων έμεινε μόνο το ίδιο το $i (σπάνιο edge case στο τελευταίο element),
				// παίρνουμε αναγκαστικά όποιο υπάρχει στη συλλογή
				$selectedUserId = $available->isNotEmpty() ? $available->random() : $usersCollection->random();

				// Αφαιρούμε/Διαγράφουμε το επιλεγμένο ID από το αρχικό collection
				$usersCollection = $usersCollection->reject(fn($id) => $id === $selectedUserId);

				$department = $departments->random();
				$positions  = $department->positions;

				Employee::factory()->create([
					'user_id'       => $selectedUserId,
					'department_id' => $department->id,
					'position_id'   => $positions->isNotEmpty() ? $positions->random()->id : null,
					'warehouse_id'  => $warehouses->isNotEmpty() ? $warehouses->random()->id : null,
				]);
			}

			// 2. Δημιουργία επιπλέον Employees που ΔΕΝ έχουν User account (user_id = null)
			foreach ($departments as $department) {
				$positions = Position::query()->where('department_id', $department)->get();

				Employee::factory(3)->create([
					'user_id'       => null, // Δεν έχουν login στο σύστημα
					'department_id' => $departments->random(),
					'position_id'   => $positions->isNotEmpty() ? fn() => $positions->random()->id : null,
					'warehouse_id'  => $warehouses->isNotEmpty() ? fn() => $warehouses->random()->id : null,
				]);
			}
		}
	}
