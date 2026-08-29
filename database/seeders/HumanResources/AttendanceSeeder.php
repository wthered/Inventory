<?php

	namespace Database\Seeders\HumanResources;

	use App\Enums\HumanResources\EmployeeAttendanceStatus;
	use App\Models\HumanResources\Attendance;
	use App\Models\HumanResources\Employee;
	use App\Models\Warehouse;
	use Carbon\Carbon;
	use Carbon\CarbonPeriod;
	use Database\Seeders\ParentSeeder;
	use Illuminate\Support\Collection;

	class AttendanceSeeder extends ParentSeeder {

		public function run(): void {
//			$employees = Employee::with([
//				'leaveRequests' => function ($query) {
//					$query->where('status', LeaveStatus::APPROVED->value);
//				}
//			])->get();
			$employees = Employee::query()->limit(self::BATCH_SIZE)->get();

			$warehouses = Warehouse::all();

			if ($employees->isEmpty() || $warehouses->isEmpty()) {
				return;
			}

			$processedCount = 0;

			$employees->chunk(64)->each(function (Collection $group) use ($warehouses, &$processedCount, $employees) {
				$this->command->info("Setting attendance from employee ".$group->min('id')." to ".$group->max('id'));
				$group->shuffle()->each(function (Employee $employee) use ($warehouses, &$processedCount, $employees) {
					$processedCount++;
					$warehouseId = $employee->warehouse_id ?? $warehouses->random()->id;

					$startDate = $employee->hire_date ? Carbon::parse($employee->hire_date) : now()->copy()->subMonth();
					$endDate = $employee->termination_date ? Carbon::parse($employee->termination_date) : today();

					if ($startDate->gt($endDate)) {
						return;
					}

					$period = CarbonPeriod::create($startDate, $endDate);

					foreach ($period as $date) {
						if ($date->isWeekend()) {
							continue;
						}

						$hasLeave = $employee->leaveRequests->contains(function ($leave) use ($date) {
							return $date->between($leave->start_date, $leave->end_date);
						});

						if ($hasLeave) {
							continue;
						}

						$attendanceStatus = fake()->randomElement(EmployeeAttendanceStatus::cases());
						$isAbsentOrLeave = in_array($attendanceStatus, [
							EmployeeAttendanceStatus::ABSENT,
							EmployeeAttendanceStatus::ON_LEAVE
						], true);

						$checkIn = null;
						$checkOut = null;
						$overtimeHours = 0;

						if (!$isAbsentOrLeave) {
							$checkIn = $date->copy()->setTime(8, 45, mt_rand(0, 59))->addMinutes(mt_rand(0, 35));

							if ($date->isToday()) {
								if (now()->isBefore($checkIn)) {
									continue;
								}
							} else {
								$forgotToCheckOut = fake()->boolean(5);

								if (!$forgotToCheckOut) {
									$checkOut = $date->copy()->setTime(16, mt_rand(45, 59), mt_rand(0, 59))
									                 ->addMinutes(mt_rand(1, 35))
									                 ->setSeconds(mt_rand(0, 59));
								}
							}

							if ($checkOut) {
								$totalHours = $checkIn->diffInMinutes($checkOut) / 60;
								$overtimeHours = max(0, round($totalHours - 8, 2));
							}
						}

						$createdAt = $checkIn ? $checkIn->format('Y-m-d H:i:s') : $date->setTime(9, 0)->format('Y-m-d H:i:s');
						$updatedAt = $checkOut ? $checkOut->format('Y-m-d H:i:s') : $createdAt;

						$this->list->push([
							'employee_id'    => $employee->id,
							'warehouse_id'   => $warehouseId,
							'work_date'      => $date->format('Y-m-d'),
							'check_in'       => $checkIn?->format('Y-m-d H:i:s'),
							'check_out'      => $checkOut?->format('Y-m-d H:i:s'),
							'overtime_hours' => $overtimeHours,
							'status'         => $attendanceStatus->value,
							'notes'          => fake()->optional(0.3)->sentence(),
							'created_at'     => $createdAt,
							'updated_at'     => $updatedAt,
						]);

						$this->command->getOutput()->write("\rProcessing Employee #".$employee->id." for date: ".$date->toDateString()."\r");
					}

					$this->command->info("\n[Employee #".$employee->id."] Completed (".$processedCount."/".$employees->count().")");
				});

				// Perform batch insert per 64-employee chunk
				if ($this->list->isNotEmpty()) {
					// Split large batch inserts into smaller database chunks (e.g., 500 rows at a time)
					$this->list->chunk(self::BATCH_SIZE)->each(function (Collection $group) {
						Attendance::query()->insert($group->toArray());
					});

					// FIX: Reset $this->list so records aren't re-inserted in subsequent chunks
					$this->list = Collection::empty();
				}
			});
		}
	}
