<?php

	namespace App\Exceptions\Inventory;

	use Exception;

	class InsufficientStockException extends Exception {
		/**
		 * Δημιουργία νέου Exception για ελλιπές απόθεμα.
		 */
		public function __construct(string $message = "Ανεπαρκές απόθεμα στο συγκεκριμένο ράφι.", int $code = 422) {
			parent::__construct($message, $code);
		}
	}
