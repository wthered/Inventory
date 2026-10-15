<?php

	namespace App\Enums\Financial;

	enum PaymentMethod: string {
		case CASH          = 'cash';
		case BANK_TRANSFER = 'bank_transfer';
		case CREDIT_CARD   = 'credit_card';
		case DEBIT_CARD    = 'debit_card';
		case CHEQUE        = 'cheque';
		case PAYPAL        = 'paypal';

		/**
		 * Get a human-readable label for UI display.
		 */
		public function label(): string {
			return __('enums.payment_method.' . $this->value);
		}

		/**
		 * Get array of all raw values (useful for migrations/validation).
		 */
		public static function values(): array {
			return array_column(self::cases(), 'value');
		}

		/**
		 * Get key-value array [value => Label] for select dropdowns.
		 */
		public static function options(): array {
			return array_reduce(self::cases(), function (array $carry, self $method) {
				$carry[$method->value] = $method->label();
				return $carry;
			}, []);
		}
	}