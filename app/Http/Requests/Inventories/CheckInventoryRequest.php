<?php

	namespace App\Http\Requests\Inventories;

	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class CheckInventoryRequest extends FormRequest {
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
				'location' => [
					'nullable',
					'integer',
					'min:1',
					Rule::exists('warehouse_locations', 'id')->whereNull('deleted_at'),
				],
			];
		}

		/**
		 * Get custom messages for validator errors.
		 */
		public function messages(): array {
			return [
				'location.integer' => 'Location ID must be a valid integer.',
				'location.min'     => 'Location ID must be at least 1.',
				'location.exists'  => 'The specified location (warehouse) does not exist.',
			];
		}

		/**
		 * Get custom attributes for validator errors.
		 */
		public function attributes(): array {
			return [
				'location' => 'warehouse location',
			];
		}

		/**
		 * Get the validated data.
		 */
		public function validated($key = null, $default = null): array {
			$validated = parent::validated($key, $default);

			// Get product from route parameter
			$product = $this->route('product');

			return [
				'product_id'  => is_object($product) ? $product->id : (int) $product,
				'location_id' => $validated['location'] ?? null,
			];
		}

		/**
		 * Prepare the data for validation.
		 */
		protected function prepareForValidation(): void {
			if ($this->has('location')) {
				$this->merge([
					'location' => (int) $this->input('location'),
				]);
			}
		}
	}
