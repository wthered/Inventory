<?php

	namespace App\Http\Requests\Warehouses;

	use App\Rules\Warehouses\ValidAisleForWarehouse;
	use App\Rules\Warehouses\ValidRackForWarehouse;
	use App\Rules\Warehouses\ValidShelfForWarehouse;
	use App\Rules\Warehouses\ValidZoneForWarehouse;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;

	class WarehouseFilterRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			// Check if the user exists and has any of the required roles
			return Auth::check() && Auth::user()->hasRole(['admin', 'super-admin', 'manager']);
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
					'min:1',
					'exists:warehouses,id'
				],
				'zone' => [
					'nullable',
					'integer',
					'min:1',
					new ValidZoneForWarehouse($this->input('warehouse'))
				],
				'aisle' => [
					'nullable',
					'integer',
					'min:1',
					new ValidAisleForWarehouse($this->input('warehouse'))
				],
				'rack' => [
					'nullable',
					'integer',
					'min:1',
					new ValidRackForWarehouse($this->input('warehouse')),
				],
				'shelf' => [
					'nullable',
					'integer',
					'min:1',
					new ValidShelfForWarehouse($this->input('warehouse')),
				],
				'type' => [
					'required',
					'string',
					'in:aisle,rack,shelf'
				]
			];
		}

		protected function prepareForValidation(): void {
			$this->merge([
				// Ensure warehouse is an integer or null
				'warehouse' => $this->filled('warehouse') ? (int) $this->input('warehouse') : null,

				// Sanitize other inputs to ensure they are numeric strings/integers
				'zone'   => $this->filled('zone') ? (int) $this->input('zone') : null,
				'aisle'  => $this->filled('aisle') ? (int) $this->input('aisle') : null,
				'rack'   => $this->filled('rack') ? (int) $this->input('rack') : null,
				'shelf'  => $this->filled('shelf') ? (int) $this->input('shelf') : null,
			]);
		}

		public function messages(): array {
			return [
				'warehouse.required' => 'Η επιλογή αποθήκης είναι υποχρεωτική.',
				'warehouse.exists'   => 'Η επιλεγμένη αποθήκη δεν είναι έγκυρη.',
				'zone.min'           => 'Η ζώνη πρέπει να είναι θετικός αριθμός.',
				'aisle.min'          => 'Ο διάδρομος πρέπει να είναι θετικός αριθμός.',
				'rack.min'           => 'Το ράφι (rack) πρέπει να είναι θετικός αριθμός.',
				'shelf.min'          => 'Το επίπεδο (shelf) πρέπει να είναι θετικός αριθμός.',
			];
		}
	}
