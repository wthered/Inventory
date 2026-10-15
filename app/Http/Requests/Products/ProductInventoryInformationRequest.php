<?php

	namespace App\Http\Requests\Products;

	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;

	class ProductInventoryInformationRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check();
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'warehouse' => [
					'required',
					'integer',
					'exists:warehouses,id'
				],
			];
		}

		/**
		 * Prepare data before validation runs.
		 */
		protected function prepareForValidation(): void {
			// Example: Ensure warehouse is cast or sanitized if needed before running rules
			if ($this->has('warehouse')) {
				$this->merge([
					'warehouse' => intval($this->input('warehouse')),
				]);
			}
		}
	}
