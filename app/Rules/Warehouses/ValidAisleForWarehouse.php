<?php

	namespace App\Rules\Warehouses;

	use App\Models\Warehouse;
	use Closure;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Translation\PotentiallyTranslatedString;

	class ValidAisleForWarehouse extends ValidWarehouse implements ValidationRule {

		public function __construct(int $warehouse) {
			parent::__construct($warehouse);
		}

		/**
		 * Run the validation rule.
		 *
		 * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
		 */
		public function validate(string $attribute, mixed $value, Closure $fail): void {
			$warehouse  = Warehouse::find($this->warehouse);

			if (intval($value) > $warehouse->aisles) {
				$fail("Ο Διάδρομος ".$value." δεν υπάρχει σε αυτόν τον αποθηκευτικό χώρο.");
			}
		}
	}
