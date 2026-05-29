<?php

	namespace App\Http\Requests\Products;

	use App\Models\Inventory;
	use App\Models\Warehouse;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Str;
	use Illuminate\Validation\Rule;

	class TransferProductRequest extends FormRequest {

		protected Inventory $inventory;
		private array       $sourceWarehouse;
		private array       $targetWarehouse;

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
			$sourceWarehouse = $this->input('sourceLocation.warehouse');
			$targetWarehouse = $this->input('targetLocation.warehouse');

			$this->sourceWarehouse = Warehouse::query()
				->findOrFail($sourceWarehouse)
				->toArray();
			$this->targetWarehouse = Warehouse::query()
				->findOrFail($targetWarehouse)
				->toArray();
			$this->inventory       = Inventory::query()
				->findOrFail($this->input('inventory'));

			return [
				// Product ID (if it's in the route)
				'product_id'               => [
					'required',
					'integer',
					'min:1',
					Rule::exists('products', 'id')
						->whereNull('deleted_at'),
				],
				'inventory'                => [
					'required',
					'integer',
					'min:1',
					Rule::exists('inventories', 'id'),
				],

				// Source location validation
				'sourceLocation.warehouse' => [
					'required',
					'integer',
					'min:1',
					Rule::exists('warehouses', 'id')
						->whereNull('deleted_at'),
				],
				'sourceLocation.zone'      => [
					'required',
					'string',
					'max:50',
					Rule::exists('warehouse_locations', 'zone')
						->where('warehouse_id', $sourceWarehouse)
						->whereNull('deleted_at'),
				],
				'sourceLocation.aisle'     => [
					'required',
					'string',
					'max:3',
					Rule::exists('warehouse_locations', 'aisle')
						->where('warehouse_id', $sourceWarehouse)
						->where('zone', $this->input('sourceLocation.zone'))
						->whereNull('deleted_at'),
				],
				'sourceLocation.rack'      => [
					'required',
					'integer',
					'min:1',
					'max:' . $this->sourceWarehouse['racks'],
					Rule::exists('warehouse_locations', 'rack')
						->where('warehouse_id', $sourceWarehouse)
						->where('zone', $this->input('sourceLocation.zone'))
						->where('aisle', $this->input('sourceLocation.aisle'))
						->whereNull('deleted_at'),
				],
				'sourceLocation.shelf'     => [
					'required',
					'integer',
					'min:1',
					'max:' . $this->sourceWarehouse['shelves'],
					Rule::exists('warehouse_locations', 'shelf')
						->where('warehouse_id', $sourceWarehouse)
						->where('zone', $this->input('sourceLocation.zone'))
						->where('aisle', $this->input('sourceLocation.aisle'))
						->where('rack', $this->input('sourceLocation.rack'))
						->whereNull('deleted_at'),
				],
				'sourceLocation.bin'       => [
					'required',
					'integer',
					'min:1',
					'max:' . $this->sourceWarehouse['bins'],
					Rule::exists('warehouse_locations', 'bin')
						->where('warehouse_id', $sourceWarehouse)
						->where('zone', $this->input('sourceLocation.zone'))
						->where('aisle', $this->input('sourceLocation.aisle'))
						->where('rack', $this->input('sourceLocation.rack'))
						->where('shelf', $this->input('sourceLocation.shelf'))
						->whereNull('deleted_at'),
				],

				// Target location validation
				'targetLocation.warehouse' => [
					'required',
					'integer',
					'min:1',
					Rule::exists('warehouses', 'id')
						->whereNull('deleted_at'),
				],

				// Max string length is 3 because it follows the format ZXX (Z1 to Z99)
				'targetLocation.zone'      => [
					'required',
					'integer',
					'min:1',
					'max:' . $this->targetWarehouse['zones'],
					function ($attribute, $value, $fail) {
						if (!empty($this->targetWarehouse) && Str::substr($value, 1) > ($this->targetWarehouse['zones'] ?? 1)) {
							$fail("Target zone " . Str::substr($value, 1) . " cannot exceed " . $this->targetWarehouse['zones'] . " for warehouse " . $this->targetWarehouse['id'] . ".");
						}
					},
				],
				'targetLocation.aisle'     => [
					'required',
					'integer',
					'min:1',
					'max:' . $this->targetWarehouse['aisles'],
					function ($attribute, $value, $fail) {
						if (!empty($this->targetWarehouse) && Str::substr($value, 1) > ($this->targetWarehouse['aisles'] ?? 1)) {
							$fail("Target aisle " . Str::substr($value, 1) . " cannot exceed " . $this->targetWarehouse['aisles'] . " for warehouse " . $this->targetWarehouse['id'] . ".");
						}
					},
				],
				'targetLocation.rack'      => [
					'required',
					'integer',
					'min:1',
					'max:' . $this->targetWarehouse['racks'],
				],
				'targetLocation.shelf'     => [
					'required',
					'integer',
					'min:1',
					'max:' . $this->targetWarehouse['shelves'],
				],
				'targetLocation.bin'       => [
					'required',
					'integer',
					'min:1',
					'max:' . $this->targetWarehouse['bins'],
				],

				// Transfer details
				'quantity'                 => [
					'required',
					'integer',
					'min:1',
					function ($attribute, $value, $fail) {
						// You might want to add custom validation here
						// to check if source has enough quantity
						if ($value > $this->inventory->quantity) {
							$fail('Quantity must not be more than ' . $this->inventory->quantity . '...');
						}
					},
				],
				'notes'                    => [
					'nullable',
					'string',
					'max:1000',
				],
			];
		}

		/**
		 * Configure the validator instance.
		 */
		public function withValidator($validator): void {
			$validator->after(function ($validator) {
				// Ensure source and target locations are different
				$source = json_encode($this->input('sourceLocation'));
				$target = json_encode($this->input('targetLocation'));

				if ($source === $target) {
					$validator
						->errors()
						->add('targetLocation', 'Source and destination locations cannot be the same.');
				}

				// Additional business logic validations can go here
				// Example: Check if source has sufficient inventory
			});
		}

		/**
		 * Get custom validation messages.
		 */
		public function messages(): array {
			$sourceWarehouse = $this->input('sourceLocation.warehouse');
			$sourceZone      = $this->input('sourceLocation.zone', '');
			$sourceAisle     = $this->input('sourceLocation.aisle', '');
			$sourceRack      = $this->input('sourceLocation.rack', '');
			$sourceShelf     = $this->input('sourceLocation.shelf', '');
			$sourceBin       = $this->input('sourceLocation.bin', '');

			$targetWarehouse = $this->input('targetLocation.warehouse');
			$targetZone      = $this->input('targetLocation.zone', '');
			$targetAisle     = $this->input('targetLocation.aisle', '');
			$targetRack      = $this->input('targetLocation.rack', '');
			$targetShelf     = $this->input('targetLocation.shelf', '');
			$targetBin       = $this->input('targetLocation.bin', '');
			return [
				'product_id.required'               => 'Product ID is required.',
				'product_id.exists'                 => 'The specified product does not exist.',

				// Source location messages
				'sourceLocation.warehouse.required' => 'Source warehouse is required.',
				'sourceLocation.warehouse.exists'   => 'The source warehouse ' . $sourceWarehouse . ' does not exist.',
				'sourceLocation.zone.exists'        => 'The source zone ' . $sourceZone . ' does not exist in this warehouse ' . $sourceWarehouse,
				'sourceLocation.aisle.exists'       => 'The source aisle ' . $sourceAisle . ' does not exist in this zone ' . $sourceZone . '.',
				'sourceLocation.rack.exists'        => 'The source rack ' . $sourceRack . ' does not exist in this aisle ' . $sourceAisle . '.',
				'sourceLocation.shelf.exists'       => 'The source shelf ' . $sourceShelf . ' does not exist in this rack ' . $sourceRack . '.',
				'sourceLocation.bin.exists'         => 'The source bin ' . $sourceBin . ' does not exist on this shelf ' . $sourceShelf . '.',

				// Target location messages
				'targetLocation.warehouse.required' => 'Destination warehouse is required.',
				'targetLocation.warehouse.exists'   => 'The destination warehouse ' . $targetWarehouse . ' does not exist.',
				'targetLocation.zone.exists'        => 'The destination zone ' . $targetZone . ' does not exist in this warehouse ' . $targetWarehouse,
				'targetLocation.aisle.exists'       => 'The destination aisle ' . $targetAisle . ' does not exist in this zone ' . $targetZone . '.',
				'targetLocation.rack.exists'        => 'The destination rack ' . $targetRack . ' does not exist in this aisle ' . $targetAisle . '.',
				'targetLocation.shelf.exists'       => 'The destination shelf ' . $targetShelf . ' does not exist in this rack ' . $targetRack . '.',
				'targetLocation.bin.exists'         => 'The destination bin ' . $targetBin . ' does not exist on this shelf ' . $targetShelf . '.',

				// Quantity messages
				'quantity.required'                 => 'Transfer quantity is required.',
				'quantity.integer'                  => 'Quantity must be a whole number.',
				'quantity.min'                      => 'Quantity must be at least 1.',

				// Notes messages
				'notes.max'                         => 'Notes may not be longer than 1000 characters.',
			];
		}

		/**
		 * Get custom attributes for validator errors.
		 */
		public function attributes(): array {
			return [
				'product_id'               => 'product',
				'sourceLocation.warehouse' => 'source warehouse',
				'sourceLocation.zone'      => 'source zone',
				'sourceLocation.aisle'     => 'source aisle',
				'sourceLocation.rack'      => 'source rack',
				'sourceLocation.shelf'     => 'source shelf',
				'sourceLocation.bin'       => 'source bin',
				'targetLocation.warehouse' => 'destination warehouse',
				'targetLocation.zone'      => 'destination zone',
				'targetLocation.aisle'     => 'destination aisle',
				'targetLocation.rack'      => 'destination rack',
				'targetLocation.shelf'     => 'destination shelf',
				'targetLocation.bin'       => 'destination bin',
				'quantity'                 => 'quantity',
				'notes'                    => 'notes',
			];
		}

		/**
		 * Get the validated data.
		 */
		public function validated($key = null, $default = null): array {
			$validated = parent::validated($key, $default);

			return [
				'product_id'       => $validated['product_id'] ?? $this->route('product'),
				'source_warehouse' => $validated['sourceLocation']['warehouse'],
				'source_zone'      => $validated['sourceLocation']['zone'],
				'source_aisle'     => $validated['sourceLocation']['aisle'],
				'source_rack'      => intval($validated['sourceLocation']['rack']),
				'source_shelf'     => intval($validated['sourceLocation']['shelf']),
				'source_bin'       => intval($validated['sourceLocation']['bin']),
				'target_warehouse' => $validated['targetLocation']['warehouse'],
				'target_zone'      => $validated['targetLocation']['zone'],
				'target_aisle'     => $validated['targetLocation']['aisle'],
				'target_rack'      => intval($validated['targetLocation']['rack']),
				'target_shelf'     => intval($validated['targetLocation']['shelf']),
				'target_bin'       => intval($validated['targetLocation']['bin']),
				'inventory'        => $this->inventory,
				'quantity'         => $validated['quantity'],
				'notes'            => $validated['notes'] ?? '',
			];
		}

		/**
		 * Prepare the data for validation.
		 */
		protected function prepareForValidation(): void {
			// If product_id is in route
			if ($this->route('product')) {
				$this->merge([
					'product_id' => (int) $this->route('product'),
				]);
			}

			// Cast numeric fields
			$this->merge([
				'sourceLocation.warehouse' => intval($this->input('sourceLocation.warehouse')),
				'sourceLocation.rack'      => intval($this->input('sourceLocation.rack')),
				'sourceLocation.shelf'     => intval($this->input('sourceLocation.shelf')),
				'sourceLocation.bin'       => intval($this->input('sourceLocation.bin')),

				'targetLocation.warehouse' => intval($this->input('targetLocation.warehouse')),
				'targetLocation.zone'      => intval($this->input('targetLocation.zone')),
				'targetLocation.aisle'     => intval($this->input('targetLocation.aisle')),
				'targetLocation.rack'      => intval($this->input('targetLocation.rack')),
				'targetLocation.shelf'     => intval($this->input('targetLocation.shelf')),
				'targetLocation.bin'       => intval($this->input('targetLocation.bin')),

				'inventory' => intval($this->route('inventory')),
				'quantity'  => intval($this->input('quantity')),
			]);

//			dd($this->input());
		}
	}
