<?php

	namespace App\Http\Requests\Stocks\StockTransfers;

	use App\Models\Inventories\Inventory;
	use App\Rules\Warehouses\ValidShelfForWarehouse;
	use App\Rules\Warehouses\ValidAisleForWarehouse;
	use App\Rules\Warehouses\ValidBinForWarehouse;
	use App\Rules\Warehouses\ValidRackForWarehouse;
	use App\Rules\Warehouses\ValidZoneForWarehouse;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Str;
	use Illuminate\Validation\Rule;

	class StockTransferStoreRequest extends FormRequest {
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
				// Product validation
				'product_id' => [
					'required',
					'integer',
					Rule::exists('products', 'id'),
					// Add custom rule to check if product exists in source warehouse
					function ($attribute, $value, $fail) {
						$sourceWarehouseId = $this->input('sourceLocation.warehouse');
						if ($sourceWarehouseId) {
							$location = Inventory::query()->where('product_id', $value)->where('warehouse_id', $sourceWarehouseId);
							if (!$location->exists()) {
								$fail('The selected product is not available in the source warehouse.');
							}
						}
					}
				],

				'quantity' => [
					'required',
					'integer',
					'min:1',
					// Check if source has enough stock
					function ($attribute, $value, $fail) {
						$productId         = $this->input('product_id');
						$sourceWarehouseId = $this->input('sourceLocation.warehouse');
						$sourceLocation = $this->input('location_id');

						if ($productId && $sourceWarehouseId) {
							$currentStock = Inventory::where('product_id', $productId)->where('warehouse_id', $sourceWarehouseId)->where('location_id', $sourceLocation)->value('available_quantity') ?? 0;

							if (intval($value) > $currentStock) {
								$fail("Insufficient stock. Available: " . $currentStock);
							}
						}
					}
				],

				'notes'                    => [
					'nullable',
					'string',
					'max:255',
				],

				// Source location validation
				'sourceLocation.warehouse' => [
					'required',
					'integer',
					Rule::exists('warehouses', 'id'),
				],
				'sourceLocation.zone'      => [
					'required',
					'integer',
					new ValidZoneForWarehouse($this->input('sourceLocation.warehouse'))
				],
				'sourceLocation.aisle'     => [
					'required',
					'integer',
					new ValidAisleForWarehouse($this->input('sourceLocation.warehouse')),
				],
				'sourceLocation.rack'      => [
					'required',
					'integer',
					new ValidRackForWarehouse($this->input('sourceLocation.warehouse')),
				],
				'sourceLocation.shelf'     => [
					'required',
					'integer',
					new ValidShelfForWarehouse($this->input('sourceLocation.warehouse')),
				],
				'sourceLocation.bin'       => [
					'required',
					'integer',
					new ValidBinForWarehouse($this->input('sourceLocation.warehouse')),
				],

				// Target location validation
				'targetLocation.warehouse' => [
					'required',
					'integer',
					Rule::exists('warehouses', 'id'),
				],
				'targetLocation.zone'      => [
					'required',
					'integer',
					new ValidZoneForWarehouse($this->input('targetLocation.warehouse'))
				],
				'targetLocation.aisle'     => [
					'required',
					'integer',
					new ValidAisleForWarehouse($this->input('targetLocation.warehouse')),
				],
				'targetLocation.rack'      => [
					'required',
					'integer',
					new ValidRackForWarehouse($this->input('targetLocation.warehouse')),
				],
				'targetLocation.shelf'     => [
					'required',
					'integer',
					new ValidShelfForWarehouse($this->input('targetLocation.warehouse')),
				],
				'targetLocation.bin'       => [
					'required',
					'integer',
					new ValidBinForWarehouse($this->input('targetLocation.warehouse')),
				],

				// Optional location_id (if you're updating an existing location)
				'location_id'              => [
					'nullable',
					'integer',
					Rule::exists('warehouse_locations', 'id'),
				],
			];
		}

		public function messages(): array {
			return [
				'product_id.required' => 'Please select a product.',
				'product_id.exists'   => 'The selected product does not exist.',

				'quantity.required' => 'Quantity is required.',
				'quantity.numeric'  => 'Quantity must be a number.',
				'quantity.min'      => 'Quantity must be at least 0.01.',
				'quantity.max'      => 'Quantity is too large.',

				'sourceLocation.warehouse.required'  => 'Source warehouse is required.',
				'sourceLocation.warehouse.exists'    => 'The source warehouse does not exist.',
				'sourceLocation.warehouse.string'    => 'Source warehouse should be a string and not and not :input',

				'sourceLocation.zone.required'  => 'Source zone is required.',
				'sourceLocation.zone.integer'    => 'Source zone should be a string and not and :input.',

				'sourceLocation.aisle.required' => 'Source aisle is required.',
				'sourceLocation.aisle.integer'    => 'Source aisle should be a number and not and :input.',

				'sourceLocation.rack.required'  => 'Source rack is required.',
				'sourceLocation.rack.integer' => 'Source rack should be a number and not and :input.',

				'sourceLocation.shelf.required' => 'Source shelf is required.',
				'sourceLocation.shelf.integer' => 'Source shelf should be a number and not and :input.',

				'sourceLocation.bin.required'   => 'Source bin is required.',
				'sourceLocation.bin.integer' => 'Source bin should be a number and not and :input.',

				'targetLocation.warehouse.required'  => 'Target warehouse is required.',
				'targetLocation.warehouse.exists'    => 'The target warehouse does not exist.',
				'targetLocation.warehouse.string'    => 'Target warehouse should be a string and not and not and :input',

				'targetLocation.zone.required'  => 'Target zone is required.',
				'targetLocation.aisle.required' => 'Target aisle is required.',
				'targetLocation.rack.required'  => 'Target rack is required.',
				'targetLocation.shelf.required' => 'Target shelf is required.',
				'targetLocation.bin.required'   => 'Target bin is required.',

				'notes.max' => 'Notes cannot exceed 1000 characters.',
			];
		}

		public function attributes(): array {
			return [
				'product_id'               => 'product',
				'quantity'                 => 'quantity',
				'sourceLocation.warehouse' => 'source warehouse',
				'sourceLocation.zone'      => 'source zone',
				'sourceLocation.aisle'     => 'source aisle',
				'sourceLocation.rack'      => 'source rack',
				'sourceLocation.shelf'     => 'source shelf',
				'sourceLocation.bin'       => 'source bin',
				'targetLocation.warehouse' => 'target warehouse',
				'targetLocation.zone'      => 'target zone',
				'targetLocation.aisle'     => 'target aisle',
				'targetLocation.rack'      => 'target rack',
				'targetLocation.shelf'     => 'target shelf',
				'targetLocation.bin'       => 'target bin',
			];
		}

		/**
		 * Get the validated data with location objects.
		 */
		public function validated($key = null, $default = null) {
			$validated = parent::validated($key, $default);

//			dd($validated);

			// Create location objects from the nested arrays
			$validated['source_location'] = [
				'warehouse_id' => $validated['sourceLocation']['warehouse'],
				'zone'         => $validated['sourceLocation']['zone'],
				'aisle'        => $validated['sourceLocation']['aisle'],
				'rack'         => $validated['sourceLocation']['rack'],
				'shelf'        => $validated['sourceLocation']['shelf'],
				'bin'          => $validated['sourceLocation']['bin'],
			];

			$validated['target_location'] = [
				'warehouse_id' => $validated['targetLocation']['warehouse'],
				'zone'         => $validated['targetLocation']['zone'],
				'aisle'        => $validated['targetLocation']['aisle'],
				'rack'         => $validated['targetLocation']['rack'],
				'shelf'        => $validated['targetLocation']['shelf'],
				'bin'          => $validated['targetLocation']['bin'],
			];

			// Remove the nested arrays if you don't need them
			unset($validated['sourceLocation'], $validated['targetLocation']);

			return $validated;
		}

		/**
		 * Prepare the data for validation.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				'product_id'     => (int) $this->input('product_id'),
				'quantity'       => (int) $this->input('quantity'),
				'sourceLocation' => [
					'warehouse' => (int) $this->input('sourceLocation.warehouse'),
					'zone'      => (int) Str::after($this->input('sourceLocation.zone'), 'Z'),
					'aisle'     => (int) Str::after($this->input('sourceLocation.aisle'), 'A'),
					'rack'      => (int) $this->input('sourceLocation.rack'),
					'shelf'     => (int) $this->input('sourceLocation.shelf'),
					'bin'       => (int) $this->input('sourceLocation.bin'),
				],
				'targetLocation' => [
					'warehouse' => (int) $this->input('targetLocation.warehouse'),
					'zone'      => (int) Str::after($this->input('targetLocation.zone'), 'Z'),
					'aisle'     => (int) Str::after($this->input('targetLocation.aisle'), 'A'),
					'rack'      => (int) $this->input('targetLocation.rack'),
					'shelf'     => (int) $this->input('targetLocation.shelf'),
					'bin'       => (int) $this->input('targetLocation.bin'),
				],
				'location_id'    => !empty($this->input('location_id')) ? intval($this->input('location_id')) : null,
			]);

//			dd($this->input());
		}
	}
