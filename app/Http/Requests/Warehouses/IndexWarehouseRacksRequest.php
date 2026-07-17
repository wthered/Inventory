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
						if ($warehouse && $value > $warehouse->aisles) {
							$fail("Aisle cannot exceed " . $warehouse->aisles . " for this warehouse.");
						}
					},
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
		 * Prepare the data for validation.
		 */
		protected function prepareForValidation(): void {
			$warehouse = $this->route('warehouse');

			$this->merge([
				'product'   => $this->input('product') ? (int) $this->input('product') : null,
				'warehouse' => $warehouse instanceof Warehouse ? $warehouse->id : (int) $this->input('warehouse'),
				'zone'      => (int) Str::after($this->input('zone'), 'Z'),
				'aisle'     => (int) Str::after($this->input('aisle'), 'A'),
			]);
		}

		/**
		 * Handle a passed validation attempt.
		 */
		protected function passedValidation(): void {
			$warehouse = $this->route('warehouse');

			$this->merge([
				'warehouse_id' => $warehouse instanceof Warehouse ? $warehouse->id : (int) $this->input('warehouse'),
			]);
		}
	}
