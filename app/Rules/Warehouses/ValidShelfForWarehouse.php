<?php

	namespace App\Rules\Warehouses;

	use Closure;
	use Illuminate\Contracts\Validation\DataAwareRule;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\DB;

	class ValidShelfForWarehouse extends ValidWarehouse implements ValidationRule, DataAwareRule {
		/**
		 * All the data under validation.
		 *
		 * @var Collection
		 */
		protected Collection $data;

		/**
		 * Set the data under validation.
		 *
		 * @param  array  $data
		 *
		 * @return $this
		 */
		public function setData(array $data): static {
			$this->data = Collection::make($data);

			return $this;
		}

		/**
		 * Run the validation rule.
		 *
		 * @param  string  $attribute
		 * @param  mixed  $value
		 * @param  Closure  $fail
		 *
		 * @return void
		 */
		public function validate(string $attribute, mixed $value, Closure $fail): void {
			// First, check if the shelf value is an integer
			if (!filter_var($value, FILTER_VALIDATE_INT)) {
				$fail("The {$attribute} must be an integer.");
				return;
			}

			// Get warehouse ID
			$warehouseId = data_get($this->data, 'targetLocation.warehouse') ?? data_get($this->data, 'warehouse') ?? request()->input('targetLocation.warehouse') ?? request()->input('warehouse');

			if (!$warehouseId) {
				$fail("Warehouse ID is required to validate the shelf number.");
				return;
			}

			// Get rack number
			$rackNumber = data_get($this->data, 'targetLocation.rack') ?? data_get($this->data, 'rack') ?? request()->input('targetLocation.rack') ?? request()->input('rack');

			if (!$rackNumber) {
				$fail("Rack number is required to validate the shelf number.");
				return;
			}

			// Get warehouse configuration (assuming warehouses table has 'shelves_per_rack' column)
			$warehouse = DB::table('warehouses')->where('id', $this->warehouse)->select([
				'racks',
				'shelves'
			])->first();

			if (!$warehouse) {
				$fail("The selected warehouse does not exist.");
				return;
			}

			// Validate rack exists
			if ($rackNumber < 1 || $rackNumber > (int) $warehouse->racks) {
				$fail("Invalid rack number for this warehouse.");
				return;
			}

			$maxShelves = intval($warehouse->shelves);
			$shelfNumber = intval($value);

			// Validate shelf number
			if ($shelfNumber < 1) {
				$fail("The {$attribute} must be at least 1.");
				return;
			}

			if ($shelfNumber > $maxShelves) {
				$fail("The " . $attribute . " must not be greater than " . $maxShelves . " for rack " . $rackNumber);
			}
		}
	}