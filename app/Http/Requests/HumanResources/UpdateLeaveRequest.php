<?php

	namespace App\Http\Requests\HumanResources;

	use App\Enums\HumanResources\LeaveStatus;
	use App\Enums\HumanResources\LeaveType;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;
	use Illuminate\Validation\Rules\Enum;

	class UpdateLeaveRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check() && $this->user()->can('leave_request.update');
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'employee_id'      => ['required', 'integer', 'exists:employees,id'],
				'leave_type_id'    => ['required', new Enum(LeaveType::class)],
				// Use 'exists:leave_types,id' if foreign key integer
				'status'           => ['required', new Enum(LeaveStatus::class)],
				'start_date'       => ['required', 'date', 'date_format:Y-m-d'],
				'end_date'         => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
				'reason'           => ['nullable', 'string', 'max:1000'],
				'rejection_reason' => [
					'nullable',
					'string',
					'max:1000',
					Rule::requiredIf(fn() => $this->input('status') === LeaveStatus::REJECTED->value),
				],
			];
		}
	}
