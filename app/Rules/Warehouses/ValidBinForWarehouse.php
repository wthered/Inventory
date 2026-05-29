<?php

	namespace App\Rules\Warehouses;

	use App\Models\Warehouse;
	use Closure;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Translation\PotentiallyTranslatedString;

	class ValidBinForWarehouse extends ValidWarehouse implements ValidationRule {

		public function __construct(int $warehouse) {
			parent::__construct($warehouse);
		}

		/**
		 * Run the validation rule.
		 *
		 * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
		 */
		public function validate(string $attribute, mixed $value, Closure $fail): void {
			// Υποθέτουμε ότι το Bin είναι απλό νούμερο (integer)
			$binNumber = filter_var($value, FILTER_VALIDATE_INT) ? intval($value) : null;

			$warehouse = Warehouse::find($this->warehouse);

			if (!$warehouse) {
				$fail("The warehouse was not found.");
				return;
			}

			// Έλεγχος αν το binNumber υπερβαίνει τα bins της αποθήκης
			if ($binNumber > $warehouse->bins) {
				$fail("The selected bin is invalid for this warehouse (Max: ".$warehouse->bins.")");
			}
		}
	}
