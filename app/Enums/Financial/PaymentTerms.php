<?php

	namespace App\Enums\Financial;

	enum PaymentTerms: string {
		// Άμεση Πληρωμή
		case CASH           = 'cash';
		case IMMEDIATE      = 'immediate';

		// Πίστωση με συγκεκριμένες ημέρες
		case CREDIT_7       = 'credit_7';
		case CREDIT_15      = 'credit_15';
		case CREDIT_30      = 'credit_30';
		case CREDIT_45      = 'credit_45';
		case CREDIT_60      = 'credit_60';
		case CREDIT_90      = 'credit_90';
		case CREDIT_120     = 'credit_120';

		// Ειδικοί εμπορικοί όροι (End of Month)
		case EOM            = 'eom';             // End of Month
		case EOM_30         = 'eom_30';          // 30 days after end of month
		case EOM_60         = 'eom_60';          // 60 days after end of month

		// Προκαταβολές
		case CIA            = 'cia';             // Cash in Advance
		case COD            = 'cod';             // Cash on Delivery

		/**
		 * Επιστρέφει τη μετάφραση από το αρχείο lang: resources/lang/{el}/enums.php
		 */
		public function label(): string {
			return __("enums.payment_terms." . $this->value);
		}

		/**
		 * Επιστρέφει τον αριθμό των ημερών για υπολογισμούς ημερομηνιών λήξης.
		 */
		public function days(): int {
			return match($this) {
				self::CASH, self::IMMEDIATE, self::CIA, self::COD => 0,
				self::CREDIT_7   => 7,
				self::CREDIT_15  => 15,
				self::CREDIT_30, self::EOM => 30,
				self::CREDIT_45  => 45,
				self::CREDIT_60, self::EOM_30 => 60,
				self::CREDIT_90, self::EOM_60 => 90,
				self::CREDIT_120 => 120,
			};
		}

		/**
		 * Hex Colors για το UI.
		 */
		public function color(): string {
			return match($this) {
				self::CASH, self::IMMEDIATE => '#10b981', // Green
				self::CIA, self::COD        => '#059669', // Dark Green
				self::CREDIT_7, self::CREDIT_15 => '#3b82f6', // Blue
				self::CREDIT_30, self::EOM   => '#6366f1', // Indigo
				self::CREDIT_45, self::EOM_30 => '#8b5cf6', // Violet
				self::CREDIT_60, self::EOM_60 => '#d946ef', // Fuchsia
				default                      => '#64748b', // Slate
			};
		}

		/**
		 * Επιστρέφει όλα τα options για dropdowns.
		 */
		public static function options(): array {
			return collect(self::cases())->mapWithKeys(fn ($term) => [
				$term->value => $term->label()
			])->toArray();
		}
	}
