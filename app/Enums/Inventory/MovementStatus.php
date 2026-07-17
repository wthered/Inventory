<?php

	namespace App\Enums\Inventory;

	use Illuminate\Support\Str;

	enum MovementStatus: int {
		case DRAFT      = 1;
		case PENDING    = 2;
		case APPROVED   = 3;
		case IN_TRANSIT = 4;
		case COMPLETED  = 5;
		case CANCELED   = 6;
		case ON_HOLD    = 7;

		/**
		 * Καταστάσεις που επιτρέπονται σε μια Προσαρμογή Αποθέματος (Stock Adjustment)
		 */
		public static function forAdjustment(): array {
			return [
				self::DRAFT,
				self::PENDING,
				self::APPROVED,
				self::COMPLETED,
				self::CANCELED,
				self::ON_HOLD,
			];
		}

		/**
		 * Καταστάσεις που επιτρέπονται σε μια Μεταφορά (Stock Transfer)
		 */
		public static function forTransfer(): array {
			return [
				self::DRAFT,
				self::PENDING,
				self::APPROVED,
				self::IN_TRANSIT,
				self::COMPLETED,
				self::CANCELED,
				self::ON_HOLD,
			];
		}

		/**
		 * Καταστάσεις που επιτρέπονται σε μια Επιστροφή (Stock Return)
		 */
		public static function forReturn(): array {
			return [
				self::DRAFT,
				self::PENDING,
				self::APPROVED,
				self::COMPLETED,
				self::CANCELED,
				self::ON_HOLD,
			];
		}

		/**
		 * Επιστρέφει το μεταφρασμένο label από το αρχείο movement.php
		 */
		public function label(): string {
			return __('movement.status.' . Str::lower($this->name));
		}

		/**
		 * Επιστρέφει Hex Color για τα Badges στο UI
		 */
		public function color(): string {
			return match ($this) {
				self::DRAFT => '#94a3b8',      // Slate (Προσχέδιο)
				self::PENDING => '#6b7280',    // Gray (Εκκρεμεί Έγκριση)
				self::APPROVED => '#14b8a6',   // Teal (Εγκρίθηκε)
				self::IN_TRANSIT => '#3b82f6', // Blue (Καθ' οδόν - μόνο για Transfers)
				self::COMPLETED => '#22c55e',  // Green (Ολοκληρώθηκε / Οριστικοποιήθηκε)
				self::CANCELED => '#ef4444',   // Red (Ακυρώθηκε)
				self::ON_HOLD => '#f97316',    // Orange (Σε Αναμονή)
			};
		}

		public function icon(): string {
			return match ($this) {
				self::DRAFT => '📝',
				self::PENDING => '⏳',
				self::APPROVED => '✅',
				self::IN_TRANSIT, self::COMPLETED => '📦',
				self::CANCELED => '❌',
				self::ON_HOLD => '🛑',
			};
		}
	}