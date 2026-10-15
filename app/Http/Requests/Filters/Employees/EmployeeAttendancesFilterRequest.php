<?php

	namespace App\Http\Requests\Filters\Employees;

	use App\Enums\HumanResources\EmployeeAttendanceStatus;
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
			$dateStart = $this->input('date_start', today()->startOfMonth()->format('Y-m-d'));
			$dateFinal = $this->input('date_final', today()->format('Y-m-d'));

			// Prevent filtering prior to employee's hire date
			if ($hireDate && $dateStart < $hireDate) {
				$dateStart = $hireDate;
			}

			if ($hireDate && $dateFinal < $hireDate) {
				$dateFinal = $hireDate;
			}

			// Ensure date_final is not earlier than date_start
			if ($dateFinal < $dateStart) {
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
