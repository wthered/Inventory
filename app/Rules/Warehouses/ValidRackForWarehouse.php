<?php

	namespace App\Rules\Warehouses;

	use Closure;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\DB;

	class ValidRackForWarehouse extends ValidWarehouse implements ValidationRule {

		/**
		 * All the data under validation.
		 *
		 * @var Collection
		 */
		protected Collection $data;

		public function __construct(int $warehouse) {
			parent::__construct($warehouse);
		}

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
			// First, check if the rack value is an integer
			if (!filter_var($value, FILTER_VALIDATE_INT)) {
				$fail("The ".$attribute." must be an integer.");
				return;
			}

			$rackNumber = intval($value);

			// Validate warehouse exists and get max racks
			$warehouse = DB::table('warehouses')->where('id', $this->warehouse)->select('racks')->first();

			if (!$warehouse) {
				$fail("The selected warehouse does not exist.");
				return;
			}

			$maxRacks = intval($warehouse->racks);

			// Validate rack number is within range
			if ($rackNumber < 1) {
				$fail("The ".$attribute." must be at least 1.");
				return;
			}

			if ($rackNumber > $maxRacks) {
				$fail("The ".$attribute." must not be greater than ".$maxRacks." for the selected warehouse #".$warehouse->id." aka ".$warehouse->name.".");
			}
		}
	}
