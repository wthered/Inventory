<?php

	namespace App\Http\Requests\Warehouses;

	use App\Models\Warehouse;
	use App\Rules\Warehouses\ValidZoneForWarehouse;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Str;
	use Illuminate\Validation\Rule;

	class IndexWarehouseAislesRequest extends FormRequest {
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
			// Get the warehouse from route parameter
			$warehouse = $this->route('warehouse');

			return [
				'product'   => [
					'nullable',
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
					new ValidZoneForWarehouse($this->input('warehouse')),
					//					function ($attribute, $value, $fail) use ($warehouse) {
					//						if ($warehouse && $value > $warehouse->zones) {
					//							$fail("Zone " . $value . " cannot exceed " . $warehouse->zones . " for this warehouse.");
					//						}
					//					},
				]
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

				'zone.required' => 'Zone is required.',
				'zone.integer'  => 'Zone must be a valid integer.',
				'zone.min'      => 'Zone must be at least 1.',
			];
		}

		/**
		 * Get custom attributes for validator errors.
		 */
		public function attributes(): array {
			return [
				'product' => 'product ID',
				'zone'    => 'zone',
			];
		}

		/**
		 * Handle a passed validation attempt.
		 */
		protected function passedValidation(): void {
			$this->merge([
				// Μετατροπή του zone σε καθαρό integer μετά το επιτυχές validation
				'zone'         => (int) $this->input('zone'),

				// Εξασφαλίζουμε ότι το warehouse_id υπάρχει στα δεδομένα μας
				// παίρνοντάς το είτε από το input είτε από το route object
				'warehouse_id' => $this->route('warehouse') instanceof Warehouse ? $this->route('warehouse')->id : (int) $this->input('warehouse'),
			]);
		}

		/**
		 * Prepare the data for validation.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				'product'   => $this->input('product'),
				'warehouse' => $this->input('warehouse'),
				'zone'      => Str::after($this->input('zone'), 'Z'),
			]);
		}
	}
