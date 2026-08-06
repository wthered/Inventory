<?php

	namespace App\Rules\Customers;

	use Closure;
	use Illuminate\Contracts\Validation\ValidationRule;

	class GreekAfm implements ValidationRule {
		/**
		 * Run the validation rule.
		 */
		public function validate(string $attribute, mixed $value, Closure $fail): void {
			// Allow null/empty if handled by 'nullable'
			if (!$value) {
				return;
			}

			// Must be exactly 9 digits
			if (!preg_match('/^\d{9}$/', $value)) {
				$fail('The :attribute must be exactly 9 digits.');
				return;
			}

			// Calculate Modulo 11 Checksum
			$sum = 0;
			for ($i = 0; $i < 8; $i++) {
				$sum += (int) $value[$i] * (1 << (8 - $i));
			}

			$remainder = $sum % 11;
			$expectedCheckDigit = ($remainder === 10) ? 0 : $remainder;
			$actualCheckDigit = (int) $value[8];

			if ($expectedCheckDigit !== $actualCheckDigit) {
				$fail('The :attribute is not a valid Greek Tax Number (ΑΦΜ).');
			}
		}
	}