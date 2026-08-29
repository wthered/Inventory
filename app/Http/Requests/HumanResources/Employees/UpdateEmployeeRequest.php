<?php

	namespace App\Http\Requests\HumanResources\Employees;

	use Carbon\Carbon;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class UpdateEmployeeRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check() && $this->user()->can('employee.update'); //
		}

		/**
		 * Prepare data for validation by sanitizing formatted inputs.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				'afm'  => $this->filled('afm') ? preg_replace('/\s+/', '', $this->input('afm')) : null,
				'iban' => $this->filled('iban') ? strtoupper(preg_replace('/\s+/', '', $this->input('iban'))) : null,
			]);
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			$employeeId = $this->route('employee')?->id ?? $this->route('employee'); //

			return [
				// Basic Employee Information
				'employee_code'           => [
					'required',
					'string',
					'regex:/^EMP-\d{4}$/',
					Rule::unique('employees', 'employee_code')->ignore($employeeId),
				],
				'hire_date'               => ['required', 'date', 'date_format:Y-m-d'],
				'first_name'              => ['nullable', 'string', 'max:255'],
				'last_name'               => ['nullable', 'string', 'max:255'],

				// Foreign Keys & Relationships
				'department_id'           => ['nullable', 'integer', Rule::exists('departments', 'id')],
				'position_id'             => ['nullable', 'integer', Rule::exists('positions', 'id')],
				'warehouse_id'            => ['nullable', 'integer', Rule::exists('warehouses', 'id')],
				'user_id'                 => [
					'nullable',
					'integer',
					Rule::exists('users', 'id'),
					Rule::unique('employees', 'user_id')->ignore($employeeId),
				],

				// Contact Info
				'phone'                   => ['nullable', 'string', 'max:30'],

				// Employee Details (Personal & Sensitive)
				'afm'                     => ['nullable', 'string', 'max:15'],
				'social_security'         => ['nullable', 'string', 'max:16'],
				'id_card_number'          => ['nullable', 'string', 'max:20'],
				'birth_date'              => [
					'nullable',
					'date',
					'date_format:Y-m-d',
					'before_or_equal:'.Carbon::today()->subYears(18)->format('Y-m-d'),
				],
				'address'                 => ['nullable', 'string', 'max:255'],
				'city'                    => ['nullable', 'string', 'max:255'],
				'postal_code'             => ['nullable', 'string', 'max:10'],
				'iban'                    => ['nullable', 'string', 'max:34'],
				'emergency_contact_name'  => ['nullable', 'string', 'max:255'],
				'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
			];
		}

		/**
		 * Get custom error messages for validation failures.
		 */
		public function messages(): array {
			return [
				'employee_code.regex'        => 'The employee code must follow the EMP-XXXX format (e.g. EMP-1066).',
				'birth_date.before_or_equal' => 'The employee must be at least 18 years old.',
			];
		}
	}
