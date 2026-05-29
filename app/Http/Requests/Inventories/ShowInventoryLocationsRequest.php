<?php

	namespace App\Http\Requests\Inventories;

	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class ShowInventoryLocationsRequest extends FormRequest {
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
			return [
				'warehouse' => [
					'required',
					'integer',
					'min:1',
					Rule::exists('warehouses', 'id')
						->whereNull('deleted_at'),
				],
				'inventory' => [
					'required',
					'integer',
					'min:1',
					Rule::exists('inventories', 'id')->where('warehouse_id', $this->input('warehouse')),
				],
			];
		}

		/**
		 * Get custom validation messages
		 */
		public function messages(): array {
			return [
				'warehouse.required' => 'Warehouse ID is required.',
				'warehouse.exists'   => 'The selected warehouse does not exist.',
				'inventory.required' => 'Inventory ID is required.',
				'inventory.exists'   => 'The inventory record was not found for this warehouse and product combination.',
			];
		}

		/**
		 * Prepare data for validation
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				'warehouse' => intval($this->input('warehouse')),
				'inventory' => intval($this->input('inventory')),
			]);
		}
	}
