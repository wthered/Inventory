<?php

	namespace App\Rules\Warehouses;

	use App\Models\Warehouse;
	use Closure;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Translation\PotentiallyTranslatedString;

	class ValidZoneForWarehouse implements ValidationRule {
		protected int $warehouse;

		public function __construct(int $warehouse) {
			$this->warehouse = $warehouse;
		}

		/**
		 * Run the validation rule.
		 *
		 * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
		 */
		public function validate(string $attribute, mixed $value, Closure $fail): void {

			# Έλεγχος Format: Z + numbers
//			if (!preg_match('/^Z(\d+)$/', $value, $matches)) {
//				$fail('Το format της ζώνης '.$value.' είναι άκυρο (π.χ. Z1)');
//				return;
//			}

			$zoneNumber = intval($value);
			$warehouse = Warehouse::query()->find($this->warehouse);

//			dd([
//				'zoneNumber' => $zoneNumber,
//				'warehouse'  => $warehouse->zones,
//			]);

			if ($zoneNumber > $warehouse->zones) {
				$fail("Η ζώνη ".$zoneNumber." δεν υπάρχει σε αυτόν τον αποθηκευτικό χώρο.");
			}

		}
	}
