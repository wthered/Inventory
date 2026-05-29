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
			$zoneNumber = intval($value);
			$warehouse  = Warehouse::find($this->warehouse);

			if ($zoneNumber >= $warehouse->zones) {
				$fail("Η ζώνη $value δεν υπάρχει σε αυτόν τον αποθηκευτικό χώρο.");
			}
		}
	}
