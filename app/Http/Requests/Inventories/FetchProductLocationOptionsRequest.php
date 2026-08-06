<?php

	namespace App\Http\Requests\Inventories;

	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	/***********************************************
	 * Input: Warehouse, Product, Location
	 */
	class FetchProductLocationOptionsRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check();
		}

		/**
		 * Prepare the data for validation.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				'warehouse'  => $this->filled('warehouse') ? intval($this->input('warehouse')) : null,
				'product_id' => $this->filled('product_id') ? intval($this->input('product_id')) : null,
				'location'   => $this->filled('location') ? intval($this->input('location')) : null,
			]);
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'warehouse'  => [
					'required',
					'integer',
					'min:1',
					Rule::exists('warehouses', 'id'),
				],
				'product_id' => [
					'nullable',
					'integer',
					'min:1',
					Rule::exists('products', 'id'),
				],
				'location'   => [
					'nullable',
					'integer',
					'min:1',
					Rule::exists('warehouse_locations', 'id')->where(function ($query) {
						$query->where('warehouse_id', $this->input('warehouse'));
					}),
				],
			];
		}

		/**
		 * Get custom validation messages.
		 */
		public function messages(): array {
			return [
				'warehouse.required' => 'Please select a warehouse.',
				'warehouse.integer'  => 'Invalid warehouse selection.',
				'warehouse.min'      => 'Invalid warehouse selection.',
				'warehouse.exists'   => 'The selected warehouse is no longer available.',

				'product_id.integer' => 'Invalid product selection.',
				'product_id.min'     => 'Invalid product selection.',
				'product_id.exists'  => 'The selected product does not exist.',

				'location.integer' => 'Invalid location selection.',
				'location.min'     => 'Invalid location selection.',
				'location.exists'  => 'The selected location does not exist in this warehouse.',
			];
		}

		/**
		 * Get custom attributes for validation errors.
		 */
		public function attributes(): array {
			return [
				'warehouse'  => 'warehouse',
				'product_id' => 'product',
				'location'   => 'location',
			];
		}
	}