<?php

	namespace App\Http\Requests\HumanResources\Employees\Attendances;

	use App\Enums\HumanResources\EmployeeAttendanceStatus;
	use App\Models\HumanResources\Attendance;
	use Carbon\Carbon;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class AttendanceStoreRequest extends FormRequest {

		public function authorize(): bool {
			return Auth::check() && $this->user()->can('create', Attendance::class);
		}

		public function rules(): array {
			return [
				'employee_id' => ['required', 'integer', 'exists:employees,id'],
				'date'        => [
					'required',
					'date_format:Y-m-d',
					'before_or_equal:today',
					Rule::unique('attendances', 'work_date')->where(function ($query) {
						return $query->where('employee_id', $this->input('employee_id'));
					}),
				],
				'clock_in'    => ['nullable', 'date_format:H:i,H:i:s'],
				'clock_out'   => ['nullable', 'date_format:H:i,H:i:s', 'after:clock_in'],
				'status'      => ['required', Rule::enum(EmployeeAttendanceStatus::class)],
				'notes'       => ['nullable', 'string', 'max:1000'],
			];
		}

		protected function passedValidation(): void {
			$workDate = $this->input('date');
			$clockIn  = $this->input('clock_in');
			$clockOut = $this->input('clock_out');

			// Parse full timestamps for check_in & check_out
			$checkIn  = $clockIn ? Carbon::parse($workDate." ".$clockIn) : null;
			$checkOut = $clockOut ? Carbon::parse($workDate." ".$clockOut) : null;

			$totalHours    = 0.00;
			$overtimeHours = 0.00;

			if ($checkIn && $checkOut) {
				$minutes    = $checkIn->diffInMinutes($checkOut);
				$totalHours = round($minutes / 60, 2);

				if ($totalHours > 8) {
					$overtimeHours = round($totalHours - 8, 2);
				}
			}

			// Inject transformed data for Eloquent mass-assignment
			$this->merge([
				'work_date'      => $workDate,
				'check_in'       => $checkIn,
				'check_out'      => $checkOut,
				'total_hours'    => $totalHours,
				'overtime_hours' => $overtimeHours,
			]);
		}

		public function messages(): array {
			return [
				'employee_id.required' => 'Please select an employee.',
				'employee_id.exists'   => 'The selected employee does not exist.',
				'date.required'        => 'The work date is required.',
				'date.date_format'     => 'The date must be a valid date string (YYYY-MM-DD).',
				'date.unique'          => 'An attendance record already exists for this employee on this date.',
				'date.before_or_equal' => 'Attendance cannot be logged for future dates.',
				'clock_in.date_format' => 'Clock In must be a valid time format (HH:MM or HH:MM:SS).',
				'clock_out.date_format' => 'Clock Out must be a valid time format (HH:MM or HH:MM:SS).',
				'clock_out.after'      => 'Clock Out time must be later than Clock In time.',
				'status.required'      => 'Please select an attendance status.',
			];
		}
	}
