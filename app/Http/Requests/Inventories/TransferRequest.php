<?php

	namespace App\Http\Requests\Inventories;

	use App\Models\Inventory;
	use App\Models\Warehouse;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class TransferRequest extends FormRequest {
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
			// Extract all location components for a single comparison check.
			$sourceLocationComponents = $this->only([
				'sourceLocation.warehouse',
				'sourceLocation.zone',
				'sourceLocation.aisle',
				'sourceLocation.rack',
				'sourceLocation.shelf',
				'sourceLocation.bin'
			]);

			$destinationLocationComponents = $this->only([
				'targetLocation.warehouse',
				'targetLocation.zone',
				'targetLocation.aisle',
				'targetLocation.rack',
				'targetLocation.shelf',
				'targetLocation.bin'
			]);

			$sourceWarehouse = Warehouse::query()->find($this->input('sourceLocation.warehouse'));
			$targetWarehouse = Warehouse::query()->find($this->input('targetLocation.warehouse'));

			return [
				// --- Product ID Validation ---
				'product_id'               => [
					'required',
					'integer',
					// Ensure the Product ID actually exists in your 'products' table
					Rule::exists('products', 'id'),
				],

				// targetInventory has been pushed while the data were preparing for validation
				'targetInventory' => [
					'required',
					'integer',
					Rule::exists('inventories', 'id')
						->where('warehouse_id', $targetWarehouse->id)
						->where('product_id', $this->input('product_id')),
					],

				// --- Quantity Validation ---
				'quantity'                 => [
					'required',
					'integer',
					'min:1',
					// You would typically add a custom rule here to ensure quantity <= available stock at source
				],

				// --- Source Location Validation (The product must currently reside here) ---
				'sourceLocation.warehouse' => [
					'required',
					'integer',
					Rule::exists('warehouses', 'id')
				],
				'sourceLocation.zone'      => [
					'required',
					'string',
					'regex:/^Z(\d+)$/',
					function ($attribute, $value, $fail) use ($sourceWarehouse) {
						// Extract number
						if (preg_match('/^Z(\d+)$/', $value, $m)) {
							$num = intval($m[1]);
							if ($num < 1) {
								$fail($attribute . " must be a positive zone number.");
							} elseif ($num > $sourceWarehouse->zones) {
								$fail($attribute . " must not be greater than " . $sourceWarehouse->zones);
							}
						} else {
							// Regex rule will already fail, but keep it explicit
							$fail("{$attribute} must be in the format Z<number>.");
						}
					},
				],
				'sourceLocation.aisle'     => [
					'required',
					'string',
					'regex:/^A(\d+)$/',
					function ($attribute, $value, $fail) use ($sourceWarehouse) {
						// Extract number
						if (preg_match('/^A(\d+)$/', $value, $m)) {
							$num = intval($m[1]);
							if ($num < 1) {
								$fail($attribute . " must be a positive zone number.");
							} elseif ($num > $sourceWarehouse->aisles) {
								$fail($attribute . " must not be greater than " . $sourceWarehouse->aisles);
							}
						} else {
							// Regex rule will already fail, but keep it explicit
							$fail($attribute . " must be in the format A<number>.");
						}
					},
				],
				'sourceLocation.rack'      => [
					'required',
					'integer',
				],
				'sourceLocation.shelf'     => [
					'required',
					'integer',
				],
				'sourceLocation.bin'       => [
					'required',
					'integer',
				],

				// --- Destination Location Validation (Where the product is going) ---
				'targetLocation.warehouse' => [
					'required',
					'integer',
					Rule::exists('warehouses', 'id'),
					// Custom rule to ensure the full source path is NOT the full destination path
					function ($attribute, $value, $fail) use ($sourceLocationComponents, $destinationLocationComponents) {
						// Check if the flattened arrays of location data are identical
						if (array_values($sourceLocationComponents) === array_values($destinationLocationComponents)) {
							$fail('The source and destination locations cannot be the same.');
						}
					}
				],
				'targetLocation.zone'      => [
					'required',
					'string',
					'regex:/^Z(\d+)$/',
					function ($attribute, $value, $fail) use ($targetWarehouse) {
						// Extract number
						if (preg_match('/^Z(\d+)$/', $value, $m)) {
							$num = intval($m[1]);
							if ($num < 1) {
								$fail($attribute . " must be a positive zone number.");
							} elseif ($num > $targetWarehouse->zones) {
								$fail($attribute . " must not be greater than " . $targetWarehouse->zones);
							}
						} else {
							// Regex rule will already fail, but keep it explicit
							$fail($attribute." must be in the format Z<number>.");
						}
					},
				],
				'targetLocation.aisle'     => [
					'required',
					'string',
					'regex:/^A(\d+)$/',
					function ($attribute, $value, $fail) use ($targetWarehouse) {
						// Extract number
						if (preg_match('/^A(\d+)$/', $value, $m)) {
							$num = intval($m[1]);
							if ($num < 1) {
								$fail($attribute . " must be a positive zone number.");
							} elseif ($num > $targetWarehouse->zones) {
								$fail($attribute . " must not be greater than " . $targetWarehouse->zones);
							}
						} else {
							// Regex rule will already fail, but keep it explicit
							$fail($attribute . " must be in the format A<number>.");
						}
					},
				],
				'targetLocation.rack'      => [
					'required',
					'integer',
				],
				'targetLocation.shelf'     => [
					'required',
					'integer',
				],
				'targetLocation.bin'       => [
					'required',
					'integer',
				],

				'location_id' => [
					'required',
					'integer',
					Rule::exists('warehouse_locations', 'id')->where(function ($query) {
						$query->where('warehouse_id', $this->input('sourceLocation.warehouse'));
					}),
				],

				// --- Optional Fields ---
				'notes'       => [
					'nullable',
					'string',
					'max:500'
				],
			];
		}

		public function validated($key = null, $default = null) {
			return Collection::make(parent::validated());
		}

		protected function prepareForValidation(): void {
			// Clean up input data
			$this->merge([
				'product_id'      => (int) $this->input('product_id'),
				'targetInventory' => Inventory::query()->where([
					'product_id'   => $this->input('product_id'),
					'warehouse_id' => $this->input('targetLocation.warehouse')
				])->first()->id,
				'location_id'     => (int) $this->input('location_id'),
				'quantity'        => (int) $this->input('quantity'),
				'targetLocation'  => [
					'warehouse' => (int) $this->input('targetLocation.warehouse'),
					'zone'      => "Z" . intval($this->input('targetLocation.zone')),
					'aisle'     => "A" . intval($this->input('targetLocation.aisle')),
					'rack'      => (int) $this->input('targetLocation.rack'),
					'shelf'     => (int) $this->input('targetLocation.shelf'),
					'bin'       => (int) $this->input('targetLocation.bin'),
				],
			]);
		}
	}
