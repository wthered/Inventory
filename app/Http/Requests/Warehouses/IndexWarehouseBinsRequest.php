<?php

	namespace App\Http\Requests\Warehouses;

	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class IndexWarehouseBinsRequest extends FormRequest {
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
				'product'   => [
					'required',
					'integer',
					'min:1',
					Rule::exists('products', 'id')
						->whereNull('deleted_at'),
				],
				'warehouse' => [
					'required',
					'integer',
					'min:1',
					Rule::exists('warehouses', 'id')
						->whereNull('deleted_at'),
				],
			];
		}

		/**
		 * Get custom messages for validator errors.
		 */
		public function messages(): array {
			return [
				'product.required' => 'Product ID is required.',
				'product.integer'  => 'Product ID must be a valid integer.',
				'product.min'      => 'Product ID must be at least 1.',
				'product.exists'   => 'The specified product does not exist.',

				'warehouse.required' => 'Warehouse ID is required.',
				'warehouse.integer'  => 'Warehouse ID must be a valid integer.',
				'warehouse.min'      => 'Warehouse ID must be at least 1.',
				'warehouse.exists'   => 'The specified warehouse does not exist.',
			];
		}

		/**
		 * Get custom attributes for validator errors.
		 */
		public function attributes(): array {
			return [
				'product'   => 'product ID',
				'warehouse' => 'warehouse ID',
			];
		}

		/**
		 * Get the validated data.
		 */
		public function validated($key = null, $default = null): array {
			$validated = parent::validated($key, $default);

			return [
				'product'   => $validated['product'],
				'warehouse' => $validated['warehouse'],
			];
		}

		/**
		 * Prepare the data for validation.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				'product'   => (int) $this->input('product'),
				'warehouse' => (int) $this->input('warehouse'),
			]);
		}
	}
