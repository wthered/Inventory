<?php

	namespace App\Enums\Financial;

	use Illuminate\Support\Str;

	enum TransactionStatus: int {
		case PENDING    = 10;
		case COMPLETED  = 20;
		case FAILED     = 30;
		case REFUNDED   = 40;
		case RECONCILED = 50;

		public function label(): string {
			return __("enums.transaction_status.".Str::lower($this->name));
		}

		/**
		 * Επιστρέφει το Hex Color για custom CSS, Badges ή Mobile APIs
		 */
		public function hexColor(): string {
			return match ($this) {
				self::PENDING    => '#f59e0b', // Amber 500
				self::COMPLETED  => '#22c55e', // Green 500
				self::FAILED     => '#ef4444', // Red 500
				self::REFUNDED   => '#64748b', // Slate 500
				self::RECONCILED => '#06b6d4', // Cyan 500
			};
		}
	}
