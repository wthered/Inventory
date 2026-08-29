<?php

	namespace App\Http\Requests\Filters\Leaves;

	use App\Enums\HumanResources\LeaveStatus;
	use App\Enums\HumanResources\LeaveType;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rules\Enum;

	class FilterLeavesRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
//			return Auth::check() && Auth::user()->can('');
			return Auth::check();
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'employee_id'  => ['nullable', 'integer', 'exists:employees,id'],
				'leave_type'   => ['nullable', new Enum(LeaveType::class)],
				'leave_status' => ['nullable', new Enum(LeaveStatus::class)],
			];
		}
	}
