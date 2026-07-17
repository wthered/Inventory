<?php

	namespace App\Enums\Purchases;

	enum PurchaseOrderStatus: int {
		case DRAFT              = 1;
		case AWAITING_APPROVAL  = 2;
		case APPROVED           = 3;
		case SENT_TO_VENDOR     = 4;
		case PARTIALLY_RECEIVED = 5;
		case RECEIVED           = 6;
		case BACKORDER          = 7;
		case CANCELLED          = 8;
		case REJECTED           = 9;

		/**
		 * Group statuses representing an active/open pipeline cycle.
		 *
		 * @return array<int>
		 */
		public static function openStatuses(): array {
			return [
				self::AWAITING_APPROVAL->value,
				self::APPROVED->value,
				self::SENT_TO_VENDOR->value,
				self::PARTIALLY_RECEIVED->value,
				self::BACKORDER->value,
			];
		}

		/**
		 * Group statuses representing a finalized state.
		 *
		 * @return array<int>
		 */
		public static function finalizedStatuses(): array {
			return [
				self::RECEIVED->value,
				self::CANCELLED->value,
				self::REJECTED->value,
			];
		}

		/**
		 * Get the display label for the status (Greek).
		 */
		public function label(): string {
			return match ($this) {
				self::DRAFT => 'Προσχέδιο',
				self::AWAITING_APPROVAL => 'Αναμονή Έγκρισης',
				self::APPROVED => 'Εγκρίθηκε',
				self::SENT_TO_VENDOR => 'Απεστάλη στον Προμηθευτή',
				self::PARTIALLY_RECEIVED => 'Μερική Παραλαβή',
				self::RECEIVED => 'Παραλήφθηκε',
				self::BACKORDER => 'Σε Εκκρεμότητα (Backorder)',
				self::CANCELLED => 'Ακυρώθηκε',
				self::REJECTED => 'Απορρίφθηκε',
			};
		}

		/**
		 * Get the hex color code for UI components/badges.
		 */
		public function color(): string {
			return match ($this) {
				self::DRAFT => '#6c757d',                     // Muted Gray
				self::AWAITING_APPROVAL => '#ffc107',         // Amber/Warning Yellow
				self::APPROVED => '#0dcaf0',                  // Cyan/Info Blue
				self::SENT_TO_VENDOR => '#0d6efd',            // Royal Blue
				self::PARTIALLY_RECEIVED => '#6610f2',        // Indigo
				self::RECEIVED => '#198754',                  // Emerald Green
				self::BACKORDER => '#212529',                 // Charcoal/Dark Gray
				self::CANCELLED, self::REJECTED => '#dc3545', // Crimson Red
			};
		}
	}
