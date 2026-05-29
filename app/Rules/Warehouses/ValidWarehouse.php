<?php

	namespace App\Rules\Warehouses;

	use Closure;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Support\Facades\DB;

	class ValidWarehouse implements ValidationRule {

		protected int $warehouse;

		public function __construct(int $warehouse) {
			$this->warehouse = $warehouse;
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
			// First, check if the value is an integer
			if (!is_numeric($value) || (int) $value != $value) {
				$fail("The ".$attribute." must be an integer.");
				return;
			}

			// Convert to integer to ensure proper database comparison
			$warehouseId = (int) $value;

			// Check if the integer exists in the warehouses.id column
			$warehouse = DB::table('warehouses')->where('id', $warehouseId);

			if (!$warehouse->exists()) {
				$fail("The selected ".$attribute." is invalid. Warehouse with ID ".$warehouseId." does not exist.");
			}
		}
	}
