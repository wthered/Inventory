<?php

	namespace Database\Factories\HumanResources;

	use App\Enums\HumanResources\EmployeeAttendanceStatus;
	use App\Models\HumanResources\Attendance;
	use App\Models\HumanResources\Employee;
	use App\Models\Warehouse;
	use Carbon\Carbon;
	use Illuminate\Database\Eloquent\Factories\Factory;

	/**
	 * @extends Factory<Attendance>
	 */
	class AttendanceFactory extends Factory {
		protected $model = Attendance::class;

		/**
		 * Define the model's default state.
		 *
		 * @return array<string, mixed>
		 */
		public function definition(): array {
			// 1. Fetch a random existing employee or fail-safe
			$employee = Employee::query()->inRandomOrder()->first() ?? Employee::factory()->create();

			// 2. Bound the work date between hire_date and termination_date (or today)
			$hireDate = $employee->hire_date ? Carbon::parse($employee->hire_date) : Carbon::now()->subMonth();
			$endDate = $employee->termination_date ? Carbon::parse($employee->termination_date) : Carbon::today();

			// Generate a random work date within employment bounds
			$workDate = Carbon::createFromTimestamp(mt_rand($hireDate->timestamp, $endDate->timestamp));

			$status = fake()->randomElement(EmployeeAttendanceStatus::cases());

			$isAbsentOrLeave = in_array($status, [
				EmployeeAttendanceStatus::ABSENT,
				EmployeeAttendanceStatus::ON_LEAVE
			], true);

			$checkIn = $isAbsentOrLeave ? null : $workDate->copy()->setTime(8, mt_rand(0, 45), mt_rand(0, 59));
			$checkOut = $isAbsentOrLeave ? null : $workDate->copy()->setTime(16, mt_rand(30, 59), mt_rand(0, 59));

			$totalHours = ($checkIn && $checkOut) ? ($checkIn->diffInMinutes($checkOut) / 60) : 0;

			return [
				'employee_id'    => $employee->id,
				'warehouse_id'   => $employee->warehouse_id ?? Warehouse::query()->inRandomOrder()->value('id'),
				'work_date'      => $workDate->format('Y-m-d'),
				'check_in'       => $checkIn,
				'check_out'      => $checkOut,
				'overtime_hours' => max(0, round($totalHours - 8, 2)),
				'status'         => $status->value,
				'notes'          => fake()->optional(0.2)->sentence(),
			];
		}

		/**
		 * State helper to explicitly pass an employee instance when making factories manually.
		 */
		public function forEmployee(Employee $employee): static {
			return $this->state(function () use ($employee) {
				$hireDate = $employee->hire_date ? Carbon::parse($employee->hire_date) : Carbon::now()->subMonth();
				$endDate = $employee->termination_date ? Carbon::parse($employee->termination_date) : Carbon::today();

				$workDate = Carbon::createFromTimestamp(mt_rand($hireDate->timestamp, $endDate->timestamp));

				return [
					'employee_id'  => $employee->id,
					'warehouse_id' => $employee->warehouse_id ?? Warehouse::inRandomOrder()->value('id'),
					'work_date'    => $workDate->format('Y-m-d'),
				];
			});
		}
	}
