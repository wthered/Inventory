<?php

	namespace App\Enums;

	enum ProductHistoryAction: string {
		// --- Κύκλος Ζωής Προϊόντος (Lifecycle) ---
		case CREATED  = 'created';
		case CLONED   = 'cloned';
		case ARCHIVED = 'archived';
		case RESTORED = 'restored';
		case DELETED  = 'deleted';

		// --- Οικονομικές Αλλαγές (Financial) ---
		case PRICE_UPDATED    = 'price_updated';
		case COST_UPDATED     = 'cost_updated';
		case TAX_RATE_CHANGED = 'tax_rate_changed';

		// --- Αλλαγές Αποθέματος (Inventory) ---
		case STOCK_ADJUSTED = 'stock_adjusted';
		case PO_RECEIVED    = 'purchase_order_received';
		case STOCK_MOVED    = 'stock_moved';

		// --- Αλλαγές Μεταδεδομένων (Metadata) ---
		case NAME_UPDATED        = 'name_updated';
		case CATEGORY_UPDATED    = 'category_updated';
		case DESCRIPTION_UPDATED = 'description_updated';

		/**
		 * Επιστρέφει όλα τα values ως Array (αντικαθιστά την παλιά all()).
		 * Χρήσιμο για seeders ή validation rules.
		 *
		 * @return array<string>
		 */
		public static function values(): array {
			return array_column(self::cases(), 'value');
		}

		/**
		 * Επιστρέφει ένα φιλικό label για το UI.
		 */
		public function label(): string {
			return match ($this) {
				self::CREATED => 'Δημιουργήθηκε',
				self::CLONED => 'Κλωνοποιήθηκε',
				self::ARCHIVED => 'Αρχειοθετήθηκε',
				self::RESTORED => 'Επαναφέρθηκε',
				self::DELETED => 'Διαγράφηκε',
				self::PRICE_UPDATED => 'Αλλαγή Τιμής',
				self::COST_UPDATED => 'Αλλαγή Κόστους',
				self::TAX_RATE_CHANGED => 'Αλλαγή ΦΠΑ',
				self::STOCK_ADJUSTED => 'Προσαρμογή Αποθέματος',
				self::PO_RECEIVED => 'Παραλαβή Παραγγελίας',
				self::STOCK_MOVED => 'Εσωτερική Μεταφορά',
				self::NAME_UPDATED => 'Αλλαγή Ονόματος',
				self::CATEGORY_UPDATED => 'Αλλαγή Κατηγορίας',
				self::DESCRIPTION_UPDATED => 'Αλλαγή Περιγραφής',
			};
		}
	}