<?php

	namespace App\Enums\Financial;

	enum PaymentStatus: int {
		case UNPAID         = 10; // Ξεκινάμε από το 10 για να έχουμε "χώρο" ενδιάμεσα αν χρειαστεί
		case PARTIALLY_PAID = 20;
		case PAID           = 30;
		case REFUNDED       = 40;
		case VOID           = 0;  // Πάντα κρατάω το 0 για κάτι που ακυρώθηκε τελείως

		/**
		 * Επιστρέφει τη μετάφραση από το lang/el/enums.php
		 */
		public function label(): string {
			return __("enums.payment_status.".$this->name);
		}

		/**
		 * Επιστρέφει το λεκτικό για Bootstrap classes (primary, success, κτλ)
		 */
		public function color(): string {
			return match ($this) {
				self::UNPAID => 'danger',
				self::PARTIALLY_PAID => 'warning',
				self::PAID => 'success',
				self::REFUNDED => 'secondary',
				self::VOID => '',
			};
		}

		/**
		 * Επιστρέφει το Hex Color για custom CSS ή Mobile APIs
		 */
		public function hexColor(): string {
			return match ($this) {
				self::UNPAID => '#ef4444',         // Red 500
				self::PARTIALLY_PAID => '#f97316', // Orange 500
				self::PAID => '#22c55e',           // Green 500
				self::REFUNDED => '#64748b',       // Slate 500
				self::VOID => '',
			};
		}
	}