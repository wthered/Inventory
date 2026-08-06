<?php

	namespace App\Rules\Customers;

	use Closure;
	use Illuminate\Contracts\Validation\ValidationRule;

	class GreekPhoneNumber implements ValidationRule {
		/**
		 * Run the validation rule.
		 *
		 * Valid Greek phone number formats:
		 * - Mobile: 69XXXXXXXX (10 digits starting with 69)
		 * - Landline: 2XXXXXXXXX (10 digits starting with 2)
		 * - International prefixes allowed: +30 or 0030
		 */
		public function validate(string $attribute, mixed $value, Closure $fail): void {
			if (is_null($value) || $value === '') {
				return;
			}

			// Clean spaces, dashes, dots, and parentheses
			$cleaned = preg_replace('/[\s\-.()]+/', '', (string) $value);

			// Normalize international prefixes (+30 or 0030 -> stripped to local 10-digit number)
			if (str_starts_with($cleaned, '+30')) {
				$cleaned = substr($cleaned, 3);
			} elseif (str_starts_with($cleaned, '0030')) {
				$cleaned = substr($cleaned, 4);
			}

			// Regular expression for Greek landlines (starts with 2) or mobile (starts with 69) followed by 8 or 9 digits
			// Exactly 10 digits total: 2XXXXXXXXX or 69XXXXXXXX
			if (!preg_match('/^(2\d{9}|69\d{8})$/', $cleaned)) {
				$fail('The :attribute must be a valid Greek phone number (landline or mobile).');
			}
		}
	}