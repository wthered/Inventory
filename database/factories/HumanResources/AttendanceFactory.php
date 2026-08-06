<?php

	namespace Database\Factories\HumanResources;

	use App\Models\HumanResources\Attendance;
	use Carbon\Carbon;
	use Illuminate\Database\Eloquent\Factories\Factory;

	/**
	 * @extends Factory<Attendance>
	 */
	class AttendanceFactory extends Factory {
		/**
		 * Define the model's default state.
		 *
		 * @return array<string, mixed>
		 */
		public function definition(): array {
			$workDate = Carbon::now()->subMonth()->addSeconds(rand(0, 30 * 24 * 60 * 60));
			$check_in = $workDate->copy()->setTime(8, mt_rand(0, 30), mt_rand(0, 59));
			$check_out = $workDate->copy()->setTime(16, mt_rand(0, 45), mt_rand(0, 59));

			// 1. Calculate total duration in hours as a decimal
			$totalHours = $check_in->diffInMinutes($check_out) / 60;

			return [
				'work_date'      => $workDate->format('Y-m-d'),
				'check_in'       => $check_in,
				'check_out'      => $check_out->isToday() ? null : $check_out,
				'overtime_hours' => max(0, round($totalHours - 8, 2)),
			];
		}
	}