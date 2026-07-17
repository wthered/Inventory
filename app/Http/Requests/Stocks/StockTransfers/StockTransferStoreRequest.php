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
					Rule::exists('products', 'id')->whereNull('deleted_at'),
					// Έλεγχος αν το προϊόν υπάρχει γενικά στο source warehouse
					function ($attribute, $value, $fail) {
						$sourceWarehouseId = $this->input('sourceLocation.warehouse');
						if ($sourceWarehouseId) {
							$exists = Inventory::query()
								->where('product_id', $value)
								->where('warehouse_id', $sourceWarehouseId)
								->exists();

							if (!$exists) {
								$fail('The selected product is not available in the source warehouse.');
							}
						}
					}
				],

				'quantity' => [
					'required',
					'integer',
					'min:1',
					// Έλεγχος διαθέσιμου αποθέματος στη συγκεκριμένη θέση
					function ($attribute, $value, $fail) {
						$productId         = $this->input('product_id');
						$sourceWarehouseId = $this->input('sourceLocation.warehouse');

						if ($productId && $sourceWarehouseId) {
							$query = Inventory::where('product_id', $productId)->where('warehouse_id', $sourceWarehouseId);

							// Αν υπάρχει συγκεκριμένο location_id το χρησιμοποιούμε,
							// αλλιώς φιλτράρουμε με τα ιεραρχικά στοιχεία της θέσης
							if ($this->input('location_id')) {
								$query->where('location_id', $this->input('location_id'));
							} else {
								$query->whereHas('location', function ($q) {
									$q->where('zone', $this->input('sourceLocation.zone'))
										->where('aisle', $this->input('sourceLocation.aisle'))
										->where('rack', $this->input('sourceLocation.rack'))
										->where('shelf', $this->input('sourceLocation.shelf'))
										->where('bin', $this->input('sourceLocation.bin'));
								});
							}

							$currentStock = $query->value('available_quantity') ?? 0;

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
					Rule::exists('warehouses', 'id')->whereNull('deleted_at'),
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
					Rule::exists('warehouses', 'id')->whereNull('deleted_at'),
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

				'location_id'              => [
					'nullable',
					'integer',
					Rule::exists('warehouse_locations', 'id')->whereNull('deleted_at'),
				],
			];
		}

		public function messages(): array {
			return [
				'product_id.required' => 'Please select a product.',
				'product_id.exists'   => 'The selected product does not exist.',

				'quantity.required' => 'Quantity is required.',
				'quantity.integer'  => 'Quantity must be a valid integer.',
				'quantity.min'      => 'Quantity must be at least 1.',

				'sourceLocation.warehouse.required' => 'Source warehouse is required.',
				'sourceLocation.warehouse.exists'   => 'The source warehouse does not exist.',

				'sourceLocation.zone.required'  => 'Source zone is required.',
				'sourceLocation.zone.integer'   => 'Source zone must be an integer.',
				'sourceLocation.aisle.required' => 'Source aisle is required.',
				'sourceLocation.aisle.integer'  => 'Source aisle must be an integer.',
				'sourceLocation.rack.required'  => 'Source rack is required.',
				'sourceLocation.rack.integer'   => 'Source rack must be an integer.',
				'sourceLocation.shelf.required' => 'Source shelf is required.',
				'sourceLocation.shelf.integer'  => 'Source shelf must be an integer.',
				'sourceLocation.bin.required'   => 'Source bin is required.',
				'sourceLocation.bin.integer'    => 'Source bin must be an integer.',

				'targetLocation.warehouse.required' => 'Target warehouse is required.',
				'targetLocation.warehouse.exists'   => 'The target warehouse does not exist.',
				'targetLocation.zone.required'      => 'Target zone is required.',
				'targetLocation.aisle.required'     => 'Target aisle is required.',
				'targetLocation.rack.required'      => 'Target rack is required.',
				'targetLocation.shelf.required'     => 'Target shelf is required.',
				'targetLocation.bin.required'       => 'Target bin is required.',

				'notes.max' => 'Notes cannot exceed 255 characters.',
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
		 * Handle a passed validation attempt.
		 * * Αυτή η μέθοδος τρέχει ΜΟΝΟ αν το validation πετύχει.
		 * Αναδιαμορφώνει τη δομή των δεδομένων πριν αυτά πάνε στον Controller.
		 */
		protected function passedValidation(): void {
			// Δημιουργούμε τα flat αντικείμενα τοποθεσιών που θέλει ο Controller μας
			$this->merge([
				'source_location' => [
					'warehouse_id' => $this->input('sourceLocation.warehouse'),
					'zone'         => $this->input('sourceLocation.zone'),
					'aisle'        => $this->input('sourceLocation.aisle'),
					'rack'         => $this->input('sourceLocation.rack'),
					'shelf'        => $this->input('sourceLocation.shelf'),
					'bin'          => $this->input('sourceLocation.bin'),
				],
				'target_location' => [
					'warehouse_id' => $this->input('targetLocation.warehouse'),
					'zone'         => $this->input('targetLocation.zone'),
					'aisle'        => $this->input('targetLocation.aisle'),
					'rack'         => $this->input('targetLocation.rack'),
					'shelf'        => $this->input('targetLocation.shelf'),
					'bin'          => $this->input('targetLocation.bin'),
				],
			]);

			// Αφαιρούμε τα παλιά nested arrays από το Request, ώστε να μην μπερδεύουν τον Controller
			$this->request->remove('sourceLocation');
			$this->request->remove('targetLocation');
		}

		/**
		 * Prepare the data for validation.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				'product_id' => $this->input('product_id') ? (int) $this->input('product_id') : null,
				'quantity'   => $this->input('quantity') ? (int) $this->input('quantity') : null,

				'sourceLocation' => [
					'warehouse' => (int) $this->input('sourceLocation.warehouse'),
					'zone'      => (int) Str::after($this->input('sourceLocation.zone'), config('warehouses.prefixes.zone')),
					'aisle'     => (int) Str::after($this->input('sourceLocation.aisle'), config('warehouses.prefixes.aisle')),
					'rack'      => (int) $this->input('sourceLocation.rack'),
					'shelf'     => (int) $this->input('sourceLocation.shelf'),
					'bin'       => (int) $this->input('sourceLocation.bin'),
				],

				'targetLocation' => [
					'warehouse' => (int) $this->input('targetLocation.warehouse'),
					'zone'      => (int) Str::after($this->input('targetLocation.zone'), config('warehouses.prefixes.zone')),
					'aisle'     => (int) Str::after($this->input('targetLocation.aisle'), config('warehouses.prefixes.aisle')),
					'rack'      => (int) $this->input('targetLocation.rack'),
					'shelf'     => (int) $this->input('targetLocation.shelf'),
					'bin'       => (int) $this->input('targetLocation.bin'),
				],

				'location_id' => !empty($this->input('location_id')) ? (int) $this->input('location_id') : null,
			]);
		}
	}
