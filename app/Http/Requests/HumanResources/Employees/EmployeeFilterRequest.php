<?php

	namespace App\Http\Requests\HumanResources\Employees;

	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class EmployeeFilterRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check() && $this->user()->can('employee.view');
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'search'     => ['nullable', 'string', 'max:255'],
				'department' => ['nullable', 'integer', Rule::exists('departments', 'id')],
				'warehouse'  => ['nullable', 'integer', Rule::exists('warehouses', 'id')],
				'position'   => ['nullable', 'integer', Rule::exists('positions', 'id')],
			];
		}

		/**
		 * Prepare the data for validation (sanitizes empty strings/null values).
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				'search'     => $this->input('search') ? trim($this->input('search')) : null,
				'department' => $this->input('department') ?: null,
				'warehouse'  => $this->input('warehouse') ?: null,
				'position'   => $this->input('position') ?: null,
			]);
		}
	}
