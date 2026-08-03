<?php

	namespace Database\Factories\HumanResources;

	use App\Enums\HumanResources\LeaveStatus;
	use App\Enums\HumanResources\LeaveType;
	use App\Models\HumanResources\Employee;
	use App\Models\HumanResources\LeaveRequest;
	use App\Models\User;
	use Illuminate\Database\Eloquent\Factories\Factory;

	/**
	 * @extends Factory<LeaveRequest>
	 */
	class LeaveRequestFactory extends Factory {
		/**
		 * Define the model's default state.
		 *
		 * @return array<string, mixed>
		 */
		public function definition(): array {
			$startDate = fake()->dateTimeBetween('-1 month', '+1 month');
			$days = fake()->numberBetween(3, 15); // Καλοκαιρινές άδειες διαρκείας!
			$endDate = (clone $startDate)->modify("+{$days} days");

			return [
				'employee_id' => Employee::factory(),
				'leave_type'  => fake()->randomElement(LeaveType::cases()),
				'start_date'  => $startDate->format('Y-m-d'),
				'end_date'    => $endDate->format('Y-m-d'),
				'total_days'  => $days,
				'reason'      => fake()->randomElement([
					'Θερινές διακοπές',
					'Οικογενειακές διακοπές',
					'Ξεκούραση',
					'Άδεια ειδικού σκοπού'
				]),
				'status'      => fake()->randomElement(LeaveStatus::cases()),
				'approved_by' => User::factory(),
				'action_at'   => now(),
			];
		}
	}