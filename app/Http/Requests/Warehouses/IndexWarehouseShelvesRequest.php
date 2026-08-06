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

	class IndexWarehouseShelvesRequest extends FormRequest {
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
					Rule::exists('products', 'id')->whereNull('deleted_at'),
				],
				'warehouse' => [
					'required',
					'integer',
					'min:1',
					Rule::exists('warehouses', 'id')->whereNull('deleted_at'),
				],
				'zone'      => [
					'required',
					'integer',
					'min:1',
					new ValidZoneForWarehouse($this->input('warehouse')),
				],
				'aisle'     => [
					'required',
					'integer',
					'min:1',
					new ValidAisleForWarehouse($this->input('warehouse')),
				],
				'rack'      => [
					'required',
					'integer',
					'min:1',
				],
			];
		}

		/**
		 * Configure the validator instance.
		 */
		public function withValidator($validator): void {
//			dd();
			$validator->after(function ($validator) {
				// Validate that the warehouse exists and isn't deleted
				$warehouse = Warehouse::query()->where('id', $this->input('warehouse'))->pluck('id');

				if ($warehouse->isEmpty()) {
					$validator->errors()->add('warehouse', 'The specified warehouse does not exist or has been deleted.');
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

				'zone.required' => 'Zone is required.',
				'zone.integer'  => 'Zone must be a valid integer.',
				'zone.min'      => 'Zone must be at least 1.',

				'aisle.required' => 'Aisle is required.',
				'aisle.integer'  => 'Aisle must be a valid integer.',
				'aisle.min'      => 'Aisle must be at least 1.',

				'rack.required' => 'Rack is required.',
				'rack.integer'  => 'Rack must be a valid integer.',
				'rack.min'      => 'Rack must be at least 1.',
			];
		}

		/**
		 * Get custom attributes for validator errors.
		 */
		public function attributes(): array {
			return [
				'product' => 'product ID',
				'zone'    => 'zone number',
				'aisle'   => 'aisle number',
				'rack'    => 'rack number',
			];
		}

		/**
		 * Prepare the data for validation.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				'product'   => (int) $this->input('product'),
				'warehouse' => (int) $this->input('warehouse'),
				'zone'      => (int) Str::after($this->input('zone'), 'Z'),
				'aisle'     => (int) Str::after($this->input('aisle'), 'A'),
				'rack'      => (int) $this->input('rack'),
			]);
		}
	}
