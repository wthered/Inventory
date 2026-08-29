<?php

	namespace App\Http\Requests\Filters\Employees;

	use App\Enums\HumanResources\LeaveStatus;
	use App\Enums\HumanResources\LeaveType;
	use App\Models\HumanResources\Employee;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class EmployeeLeavesFilterRequest extends FormRequest {

		public function authorize(): bool {
			return Auth::check() && $this->user()->can('view', Employee::class);
		}

		protected function prepareForValidation(): void {
			$dateFrom = $this->input('leave_date_start');
			$dateTo = $this->input('leave_date_final');

			// Align end date if placed before start date
			if ($dateFrom && $dateTo && $dateTo < $dateFrom) {
				$dateTo = $dateFrom;
			}

			$this->merge([
				'leave_date_start' => $dateFrom,
				'leave_date_final' => $dateTo,
			]);
		}

		public function rules(): array {
			return [
				'leave_type'       => ['nullable', 'string', Rule::enum(LeaveType::class)],
				'leave_status'     => ['nullable', 'string', Rule::enum(LeaveStatus::class)],
				'leave_date_start' => ['nullable', 'date', 'date_format:Y-m-d'],
				'leave_date_final' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:leave_date_start'],
			];
		}
	}
