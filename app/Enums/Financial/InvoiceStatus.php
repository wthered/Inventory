<?php

	namespace App\Enums\Financial;

	use Illuminate\Support\Str;

	enum InvoiceStatus: int {
		case DRAFT          = 10;
		case SENT           = 20;
		case VIEWED         = 25;
		case PARTIALLY_PAID = 30;
		case PAID           = 40;
		case OVERDUE        = 50;
		case CANCELLED      = 60;
		case VOID           = 0;

		/**
		 * Επιστρέφει τη μετάφραση από το lang/el/enums.php
		 */
		public function label(): string {
			return __("enums.invoice_status." . Str::lower($this->name));
		}

		/**
		 * Επιστρέφει το λεκτικό για Bootstrap classes (primary, success, κτλ)
		 */
		public function color(): string {
			return match ($this) {
				self::DRAFT => 'secondary',
				self::SENT => 'info',
				self::VIEWED => 'primary',
				self::PARTIALLY_PAID => 'warning',
				self::PAID => 'success',
				self::OVERDUE => 'danger',
				self::CANCELLED => 'dark',
				self::VOID => '',
			};
		}

		/**
		 * Επιστρέφει το Hex Color για custom CSS ή Mobile APIs
		 */
		public function hexColor(): string {
			return match ($this) {
				self::DRAFT => '#64748b',          // Slate 500
				self::SENT => '#0284c7',           // Sky 600
				self::VIEWED => '#3b82f6',         // Blue 500
				self::PARTIALLY_PAID => '#f97316', // Orange 500
				self::PAID => '#22c55e',           // Green 500
				self::OVERDUE => '#ef4444',        // Red 500
				self::CANCELLED => '#334155',      // Slate 700
				self::VOID => '',
			};
		}

		/**
		 * Επιστρέφει όλα τα options για dropdowns.
		 */
		public static function options(): array {
			return collect(self::cases())->mapWithKeys(fn ($status) => [
				$status->value => $status->label()
			])->toArray();
		}
	}