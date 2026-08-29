<?php

	namespace App\Http\Requests\Filters\Employees;

	use App\Enums\HumanResources\EmployeeAttendanceStatus;
	use Carbon\Carbon;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class EmployeeAttendancesFilterRequest extends FormRequest {

		public function authorize(): bool {
			return Auth::check();
		}

		protected function prepareForValidation(): void {
			$employee = $this->route('employee');
			if (!$employee) {
				return;
			}

			$hireDate = $employee->hire_date?->format('Y-m-d');
			$maxDate = today()->format('Y-m-d');

			if ($employee->termination_date && Carbon::parse($employee->termination_date)->format('Y-m-d') < $maxDate) {
				$maxDate = Carbon::parse($employee->termination_date)->format('Y-m-d');
			}

			$dateStart = $this->input('date_start');
			$dateFinal = $this->input('date_final');

			// Clamp date_start limits
			if ($dateStart && $hireDate && $dateStart < $hireDate) {
				$dateStart = $hireDate;
			}
			if ($dateStart && $dateStart > $maxDate) {
				$dateStart = $maxDate;
			}

			// Clamp date_final limits
			if ($dateFinal && $hireDate && $dateFinal < $hireDate) {
				$dateFinal = $hireDate;
			}
			if ($dateFinal && $dateFinal > $maxDate) {
				$dateFinal = $maxDate;
			}

			// Ensure date_final is never earlier than date_start
			if ($dateStart && $dateFinal && $dateFinal < $dateStart) {
				$dateFinal = $dateStart;
			}

			$this->merge([
				'date_start' => $dateStart,
				'date_final' => $dateFinal,
			]);
		}

		public function rules(): array {
			return [
				'date_start' => ['nullable', 'date', 'date_format:Y-m-d'],
				'date_final' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:date_start'],
				'status'     => ['nullable', 'string', Rule::enum(EmployeeAttendanceStatus::class)],
			];
		}
	}
