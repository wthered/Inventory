<?php

	namespace App\Http\Requests\HumanResources\Employees\Leaves;

	use App\Enums\HumanResources\LeaveType;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Carbon;
	use Illuminate\Validation\Rule;

	class LeaveStoreRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return true;
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			$startDate = $this->input('start_date');

			// Αν έχει δοθεί έγκυρη start_date, υπολογίζουμε το ανώτατο όριο (π.χ. +30 ημέρες)
			$maxEndDate = $startDate && strtotime($startDate)
				? Carbon::parse($startDate)->addDays(30)->format('Y-m-d')
				: null;

			$endDateRules = ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'];

			if ($maxEndDate) {
				$endDateRules[] = "before_or_equal:".$maxEndDate;
			}

			return [
				'employee_id' => ['required', 'integer', 'exists:employees,id'],
				'type'        => ['required', Rule::enum(LeaveType::class)],
				'start_date'  => [
					'required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.today()->subDays(30)->format('Y-m-d')
				],
				'end_date'    => $endDateRules,
				'reason'      => ['nullable', 'string', 'max:500'],
			];
		}
	}
