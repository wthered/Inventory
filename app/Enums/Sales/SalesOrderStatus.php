<?php

	namespace App\Enums\Sales;

	use Illuminate\Support\Str;

	enum SalesOrderStatus: int {
		case CANCELLED  = 0;
		case DRAFT      = 10;
		case PENDING    = 20;
		case CONFIRMED  = 30;
		case PROCESSING = 40;
		case SHIPPED    = 50;
		case DELIVERED  = 60;
		case COMPLETED  = 70;
		case RETURNED   = 100;

		public function label(): string {
			return __("enums.sales_order_status.".Str::lower($this->name));
		}

		/**
		 * Επιστρέφει Hex Color Codes για χρήση σε Inline CSS ή Charts.
		 */
		public function color(): string {
			return match ($this) {
				self::DRAFT      => '#94a3b8', // Blue-gray
				self::PENDING    => '#f59e0b', // Amber
				self::CONFIRMED  => '#0ea5e9', // Sky blue
				self::PROCESSING => '#6366f1', // Indigo
				self::SHIPPED    => '#2563eb', // Blue
				self::DELIVERED  => '#10b981', // Emerald green
				self::COMPLETED  => '#059669', // Darker emerald for finality
				self::CANCELLED  => '#ef4444', // Red
				self::RETURNED   => '#334155', // Slate
			};
		}

		/**
		 * Ποια statuses επηρεάζουν το απόθεμα.
		 */
		public function shouldAffectStock(): bool {
			return match ($this) {
				self::CONFIRMED,
				self::PROCESSING,
				self::SHIPPED,
				self::DELIVERED,
				self::COMPLETED => true,
				default         => false,
			};
		}

		/**
		 * Επιστρέφει όλα τα διαθέσιμα statuses σε μορφή array για χρήση σε <select>.
		 */
		public static function options(): array {
			return collect(self::cases())->mapWithKeys(fn($status) => [
				$status->value => $status->label()
			])->toArray();
		}

		/**
		 * Ελέγχει αν η παραγγελία είναι ακόμα επεξεργάσιμη
		 */
		public function isEditable(): bool {
			return match ($this) {
				self::DRAFT, self::PENDING => true,
				default                    => false,
			};
		}
	}