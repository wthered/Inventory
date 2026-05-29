<?php

	namespace App\Http\Requests\Warehouses;

	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class IndexWarehouseZonesRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check();
		}

		/**
		 * Get the validation rules that apply to the request.
		 */
		public function rules(): array {
			$warehouse = $this->route('warehouse');

			return [
				'product'   => [
					'required',
					'integer',
					'min:1',
					Rule::exists('products', 'id')->whereNull('deleted_at'),
				],
				'warehouse' => [
					'required',
					'integer',
					'min:1',
					Rule::exists('warehouses', 'id')->whereNull('deleted_at'),
				],
				//				'zone'      => [
				//					'required',
				//					'integer',
				//					'min:1',
				//					function ($attribute, $value, $fail) use ($warehouse) {
				//						// Get the warehouse's max zone count
				//						$warehouse = Warehouse::query()->find($warehouse);
				//
				//						if (!$warehouse) {
				//							$fail('Warehouse not found.');
				//							return;
				//						}
				//
				//						// Validate zone is within warehouse's zone range
				//						if ($value < 1 || $value > $warehouse->toArray()['zones']) {
				//							$fail("Zone must be between 1 and ".$warehouse->toArray()['zones']);
				//						}
				//					},
				//					// Also check if zone exists in warehouse_locations table
				//					Rule::exists('warehouse_locations', 'zone')->where('warehouse_id', $warehouse->id)->whereNull('deleted_at'),
				//				],
			];
		}

		/**
		 * Get custom validation messages.
		 */
		public function messages(): array {
			return [
				'warehouse.required' => 'Warehouse ID is required',
				'warehouse.integer'  => 'Warehouse ID must be a valid integer',
				'warehouse.min'      => 'Warehouse ID must be at least 1',
				'warehouse.exists'   => 'The specified warehouse does not exist',

				'product.required' => 'Product is required',
				'product.integer'  => 'Product must be a numeric value',
				'product.min'      => 'Product must be at least 1',
				'product.exists'   => 'The specified Product does not exist in database',
			];
		}

		/**
		 * Get custom attributes for validator errors.
		 */
		public function attributes(): array {
			return [
				'warehouse' => 'warehouse',
				'product'   => 'product',
			];
		}

		/**
		 * Get the validated data.
		 */
		public function validated($key = null, $default = null): array {
			$validated = parent::validated($key, $default);

			return [
				'warehouse_id' => $validated['warehouse'],
				'product'      => $validated['product'],
			];
		}

		/**
		 * Prepare the data for validation.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				'warehouse' => $this->route('warehouse')->id,
				'zone'      => intval($this->query('zone')),
			]);
		}
	}
