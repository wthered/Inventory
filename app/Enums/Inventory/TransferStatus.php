<?php

	namespace App\Enums\Inventory;

	use Illuminate\Support\Str;

	/**
	 * Unified Transfer Status Enum
	 * IDs are sequential 1-13.
	 * Labels are retrieved from lang files.
	 */
	enum TransferStatus: int {

		case DRAFT           = 1;
		case PENDING         = 2;
		case APPROVED        = 3;
		case IN_TRANSIT      = 4;
		case COMPLETED       = 5;
		case CANCELED        = 6;
		case FAILED          = 7;
		case ON_HOLD         = 8;
		case EXPIRED         = 9;
		case REFUNDED        = 10;
		case ACTION_REQUIRED = 11;
		case PARTIAL         = 12;
		case RETURNED        = 13;

		/**
		 * Επιστρέφει το μεταφρασμένο label από το el/enums.php
		 */
		public function label(): string {
			// Χρησιμοποιούμε το Str::lower($this->name) για να ταιριάζει με τα keys στο enums.php
			return __('enums.transfer_status.' . Str::lower($this->name));
		}

		/**
		 * Επιστρέφει το Hex Color για το UI.
		 */
		public function color(): string {
			return match ($this) {
				self::DRAFT           => '#94a3b8', // Slate
				self::PENDING         => '#6b7280', // Gray
				self::APPROVED        => '#14b8a6', // Teal
				self::IN_TRANSIT      => '#3b82f6', // Blue
				self::COMPLETED       => '#22c55e', // Green
				self::CANCELED,
				self::FAILED,
				self::EXPIRED         => '#ef4444', // Red
				self::ON_HOLD,
				self::ACTION_REQUIRED => '#f97316', // Orange
				self::REFUNDED,
				self::RETURNED        => '#a855f7', // Purple
				self::PARTIAL         => '#84cc16', // Lime
			};
		}

		/**
		 * Ελέγχει αν η μεταφορά έχει ολοκληρωθεί ή ακυρωθεί.
		 */
		public function isFinalized(): bool {
			return in_array($this, [
				self::COMPLETED,
				self::CANCELED,
				self::FAILED,
				self::EXPIRED,
				self::REFUNDED,
				self::RETURNED
			]);
		}

		/**
		 * Ελέγχει αν η μεταφορά είναι σε "ανοιχτή" κατάσταση.
		 */
		public function isActive(): bool {
			return in_array($this, [
				self::PENDING,
				self::APPROVED,
				self::IN_TRANSIT,
				self::ON_HOLD,
				self::ACTION_REQUIRED
			]);
		}
	}