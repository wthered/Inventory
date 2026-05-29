<?php

	namespace App\Http\Requests\Warehouses;

	use App\Models\Warehouse;
	use App\Rules\Warehouses\ValidAisleForWarehouse;
	use App\Rules\Warehouses\ValidZoneForWarehouse;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Str;
	use Illuminate\Validation\Rule;

	class IndexWarehouseRacksRequest extends FormRequest {
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
			$warehouse = $this->route('warehouse');
			return [
				'product' => [
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
				'zone'    => [
					'required',
					'integer',
					'min:1',
					new ValidZoneForWarehouse($this->input('warehouse')),
					function ($attribute, $value, $fail) use ($warehouse) {
						if ($warehouse && $value > $warehouse->zones) {
							$fail("Zone cannot exceed " . $warehouse->zones . " for this warehouse.");
						}
					},
				],
				'aisle'   => [
					'required',
					'integer',
					'min:1',
					new ValidAisleForWarehouse($this->input('warehouse')),
					function ($attribute, $value, $fail) use ($warehouse) {
//						dd(compact('attribute', 'value', 'warehouse'));
						if ($warehouse && $value > $warehouse->aisles) {
							$fail("Aisle cannot exceed " . $warehouse->aisles . " for this warehouse.");
						}
					},
				],
			];
		}

		/**
		 * Configure the validator instance.
		 */
		public function withValidator($validator): void {
			$validator->after(function ($validator) {
				// Only perform these checks if basic validation passes
				if ($validator->errors()->any()) {
					return;
				}

				$warehouseId = $this->input('warehouse');
				$zone        = $this->input('zone');
				$aisle       = $this->input('aisle');

				// Fetch warehouse to check zone and aisle limits
				$warehouse = Warehouse::query()->find($warehouseId);

				if (!$warehouse) {
					// This should already be caught by the exists rule, but as a fallback
					$validator->errors()->add('warehouse', 'The specified warehouse does not exist.');
					return;
				}

				// Validate zone against warehouse limit
				if ($zone > $warehouse->zones) {
					$validator->errors()->add('zone', "Zone cannot exceed " . $warehouse->zones . " for this warehouse.");
				}

				// Validate aisle against warehouse limit (assuming there's an aisle_count property)
				// Adjust the property name based on your actual database column
				if (isset($warehouse->aisles) && $aisle > $warehouse->aisles) {
					$validator->errors()->add('aisle', "Aisle cannot exceed " . $warehouse->aisles . " for this warehouse.");
				}
			});
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

				'zone.required' => 'Zone is required.',
				'zone.integer'  => 'Zone must be a valid integer.',
				'zone.min'      => 'Zone must be at least 1.',

				'aisle.required' => 'Aisle is required.',
				'aisle.integer'  => 'Aisle must be a valid integer.',
				'aisle.min'      => 'Aisle must be at least 1.',
			];
		}

		/**
		 * Get custom attributes for validator errors.
		 */
		public function attributes(): array {
			return [
				'product'   => 'Product ID',
				'warehouse' => 'Warehouse ID',
				'zone'      => 'Zone number',
				'aisle'     => 'Aisle number',
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
				'zone'      => $validated['zone'],
				'aisle'     => $validated['aisle'],
			];
		}

		/**
		 * Prepare the data for validation.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				'product'   => (int) $this->input('product'),
				'warehouse' => (int) $this->route('warehouse')->id,
				'zone'      => (int) Str::after($this->input('zone'), 'Z'),
				'aisle'     => (int) Str::after($this->input('aisle'), 'A'),
			]);
		}
	}
