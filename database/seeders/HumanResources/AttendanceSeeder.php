<?php

	namespace Database\Seeders\HumanResources;

	use App\Enums\HumanResources\LeaveStatus;
	use App\Models\HumanResources\Attendance;
	use App\Models\HumanResources\Employee;
	use App\Models\Warehouse;
	use Carbon\Carbon;
	use Carbon\CarbonPeriod;
	use Illuminate\Database\Seeder;

	class AttendanceSeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			// Eager load τις εγκεκριμένες άδειες για να αποφύγουμε N+1 queries
			$employees = Employee::with([
				'leaveRequests' => function ($query) {
					$query->where('status', LeaveStatus::APPROVED->value);
				}
			])->get()->shuffle();

			$warehouses = Warehouse::all();

			if ($employees->isEmpty() || $warehouses->isEmpty()) {
				return;
			}

			while ($employees->isNotEmpty()) {
				$employee = $employees->pull($employees->keys()->random());

				$warehouseId = $employee->warehouse_id ?? $warehouses->random()->id;

				$startDate = $employee->hire_date ? Carbon::parse($employee->hire_date) : now()->subMonth();

				$period = CarbonPeriod::create($startDate, today());

				foreach ($period as $date) {
					// 1. Παράλειψη Σαββατοκύριακων
					if ($date->isWeekend()) {
						continue;
					}

					// 2. Έλεγχος αν ο υπάλληλος έχει εγκεκριμένη άδεια τη συγκεκριμένη ημερομηνία
					$hasLeave = $employee->leaveRequests->contains(function ($leave) use ($date) {
						return $date->between($leave->start_date, $leave->end_date);
					});

					if ($hasLeave) {
						continue; // Αν έχει άδεια, παραλείπουμε τη δημιουργία attendance
					}

					// 3. Τυχαία ώρα Check-in
					$checkIn = $date->copy()->setTime(9, 45, mt_rand(0, 59))->addMinutes(mt_rand(0, 35));
					$checkOut = null;

					if ($date->isToday()) {
						// Αν είναι σήμερα, κάνουμε check-in ΜΟΝΟ αν έχει περάσει η ώρα της έναρξης
						if (now()->isBefore($checkIn)) {
							continue;
						}
					} else {
						// 4. Για παλιότερες μέρες: πιθανότητα 5% να ξέχασε check-out
						$forgotToCheckOut = fake()->boolean(5);

						if (empty($forgotToCheckOut)) {
							$checkOut = $date->copy()->setTime(17, 55, 0)->addMinutes(mt_rand(0, 35));
						}
					}

					// Υπολογισμός Overtime (αν υπάρχει check_out)
					$overtimeHours = 0;
					if (!empty($checkOut)) {
						$totalHours = $checkIn->diffInMinutes($checkOut) / 60;
						$overtimeHours = max(0, round($totalHours - 8, 2));
					}

					Attendance::query()->create([
						'employee_id'    => $employee->id,
						'warehouse_id'   => $warehouseId,
						'work_date'      => $date->format('Y-m-d'),
						'check_in'       => $checkIn,
						'check_out'      => $checkOut,
						'overtime_hours' => $overtimeHours,
						'created_at'     => $date->format('Y-m-d H:i:s'),
						'updated_at'     => $date->copy()->addSeconds(mt_rand(1, 8))->format('Y-m-d H:i:s'),
					]);

					// Live ενημέρωση στην ίδια γραμμή
					$this->command->getOutput()->write("\rProcessing Employee #".$employee->id." for date: ".$date->toDateString());
				}
				$this->command->info("End of employee #".$employee->id."/".$employees->count());
			}
		}
	}
