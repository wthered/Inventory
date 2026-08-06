<?php

	namespace App\Http\Requests\Warehouses;

	use App\Rules\Warehouses\ValidAisleForWarehouse;
	use App\Rules\Warehouses\ValidBinForWarehouse;
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
				'zone'      => [
					'nullable',
					'integer',
					'min:1',
					new ValidZoneForWarehouse($this->input('warehouse'))
				],
				'aisle'     => [
					'nullable',
					'integer',
					'min:1',
					new ValidAisleForWarehouse($this->input('warehouse'))
				],
				'rack'      => [
					'nullable',
					'integer',
					'min:1',
					new ValidRackForWarehouse($this->input('warehouse')),
				],
				'shelf'     => [
					'nullable',
					'integer',
					'min:1',
					new ValidShelfForWarehouse($this->input('warehouse')),
				],
				'bin'       => [
					'nullable',
					'integer',
					'min:1',
					new ValidBinForWarehouse($this->input('warehouse')),
				],
				'type'      => [
					'nullable',
					'string',
					'in:aisle,rack,shelf,bin'
				]
			];
		}

		protected function prepareForValidation(): void {
			$this->merge([
				// Ensure warehouse is an integer or null
				'warehouse' => $this->filled('warehouse') ? (int) $this->input('warehouse') : null,

				// Sanitize other inputs to ensure they are numeric strings/integers
				'zone'      => $this->filled('zone') ? (int) preg_replace('/^Z/i', '', trim((string) $this->input('zone'))) : null,
				'aisle'     => $this->filled('aisle') ? (int) $this->input('aisle') : null,
				'rack'      => $this->filled('rack') ? (int) $this->input('rack') : null,
				'shelf'     => $this->filled('shelf') ? (int) $this->input('shelf') : null,
				'bin'       => $this->filled('bin') ? (int) $this->input('bin') : null,
			]);
		}

		public function messages(): array {
			return [
				// Warehouse validation messages
				'warehouse.required' => 'Η επιλογή αποθήκης είναι υποχρεωτική.',
				'warehouse.integer'  => 'Η αναγνωριστική τιμή της αποθήκης πρέπει να είναι ακέραιος αριθμός.',
				'warehouse.min'      => 'Η αναγνωριστική τιμή της αποθήκης πρέπει να είναι θετικός αριθμός.',
				'warehouse.exists'   => 'Η επιλεγμένη αποθήκη δεν είναι έγκυρη.',

				// Zone validation messages
				'zone.integer'       => 'Η ζώνη πρέπει να είναι ακέραιος αριθμός.',
				'zone.min'           => 'Η ζώνη πρέπει να είναι θετικός αριθμός.',

				// Aisle validation messages
				'aisle.integer'      => 'Ο διάδρομος πρέπει να είναι ακέραιος αριθμός.',
				'aisle.min'          => 'Ο διάδρομος πρέπει να είναι θετικός αριθμός.',

				// Rack validation messages
				'rack.integer'       => 'Το ράφι (rack) πρέπει να είναι ακέραιος αριθμός.',
				'rack.min'           => 'Το ράφι (rack) πρέπει να είναι θετικός αριθμός.',

				// Shelf validation messages
				'shelf.integer'      => 'Το επίπεδο (shelf) πρέπει να είναι ακέραιος αριθμός.',
				'shelf.min'          => 'Το επίπεδο (shelf) πρέπει να είναι θετικός αριθμός.',

				// Bin validation messages
				'bin.integer'        => 'Το θέση/κάδος (bin) πρέπει να είναι ακέραιος αριθμός.',
				'bin.min'            => 'Το θέση/κάδος (bin) πρέπει να είναι θετικός αριθμός.',

				// Type validation messages
				'type.required'      => 'Ο τύπος φιλτραρίσματος είναι υποχρεωτικός.',
				'type.string'        => 'Ο τύπος φιλτραρίσματος πρέπει να είναι συμβολοσειρά.',
				'type.in'            => 'Ο επιλεγμένος τύπος φιλτραρίσματος δεν είναι έγκυρος.',
			];
		}
	}
