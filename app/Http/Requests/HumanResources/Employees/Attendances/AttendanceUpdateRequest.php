<?php

	namespace App\Http\Requests\HumanResources\Employees\Attendances;

	use App\Enums\HumanResources\EmployeeAttendanceStatus;
	use Carbon\Carbon;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class AttendanceUpdateRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check() && $this->user()->can('attendance.update');
		}

		/**
		 * Prepare the data for validation.
		 */
		protected function prepareForValidation(): void {
//			dd($this->input());
			$this->merge([
				'employee_id' => $this->input('employee_id') ? (int) $this->input('employee_id') : null,
				'check_in'    => $this->input('check_in') ? trim($this->input('check_in')) : null,
				'check_out'   => $this->input('check_out') ? trim($this->input('check_out')) : null,
				'notes'       => $this->input('notes') ? trim($this->input('notes')) : null,
			]);
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'employee_id'    => ['required', 'integer', 'exists:employees,id'],
				'date'           => ['required', 'date', 'before_or_equal:today'],
				'status'         => ['required', Rule::enum(EmployeeAttendanceStatus::class)],
				'check_in'       => ['nullable', 'date_format:H:i,H:i:s'],
				'check_out'      => ['nullable', 'date_format:H:i,H:i:s', 'after:check_in'],
				'notes'          => ['nullable', 'string', 'max:1000'],

				// Declarations for payload inclusion
				'work_date'      => ['nullable'],
				'overtime_hours' => ['nullable'],
			];
		}

		/**
		 * Custom error messages for validation rules.
		 */
		public function messages(): array {
			return [
				'employee_id.required'  => 'Please select a valid employee.',
				'employee_id.exists'    => 'The selected employee does not exist in the system.',
				'date.required'         => 'The attendance work date is required.',
				'date.before_or_equal'  => 'Attendance date cannot be set in the future.',
				'check_in.date_format'  => 'Check In time must be a valid time format (HH:MM or HH:MM:SS).',
				'check_out.date_format' => 'Check Out time must be a valid time format (HH:MM or HH:MM:SS).',
				'check_out.after'       => 'Check Out time must be after Check In time.',
				'notes.max'             => 'Notes cannot exceed 1000 characters.',
			];
		}

		/**
		 * Handle a passed validation attempt.
		 * Automatically combines date and timestamps into full DateTime instances,
		 * calculates overtime hours, and sets the computed status.
		 */
		protected function passedValidation(): void {
//			dd($this->validated());
			$workDate = $this->validated('date');
			$checkIn = $this->validated('check_in') ? Carbon::parse($this->validated('check_in')) : null;
			$checkOut = $this->validated('check_out') ? Carbon::parse($this->validated('check_out')) : null;

//			dd($workDate, $checkIn, $checkOut);

			$overtimeHours = 0;
			if ($checkIn && $checkOut) {
				$totalMinutes = $checkIn->diffInMinutes($checkOut);
				$standardMinutes = 8 * 60;
				if ($totalMinutes > $standardMinutes) {
					$overtimeHours = round(($totalMinutes - $standardMinutes) / 60, 2);
				}
			}

			$this->merge([
				'work_date'      => $workDate,
				'check_in'       => $workDate." ".$checkIn->format('H:i:s'),
				'check_out'      => $workDate." ".$checkOut->format('H:i:s'),
				'overtime_hours' => $overtimeHours,
				'status'         => $this->input('status'), // Respect user manual selection
			]);
		}
	}
