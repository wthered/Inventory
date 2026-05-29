<?php

	namespace App\Enums\Inventory;

	enum TransactionReason: string {
		// Κύριες Λειτουργίες
		case PURCHASE     = 'purchase';
		case SALE         = 'sale';
		case TRANSFER_IN  = 'transfer_in';
		case TRANSFER_OUT = 'transfer_out';
		case RETURNED     = 'returned';

		// Διορθώσεις Αποθέματος
		case STOCKTAKE      = 'stocktake';
		case COUNTING_ERROR = 'counting_error';
		case DATA_ENTRY     = 'data_entry';
		case FOUND          = 'found';

		// Ποιοτικά Ζητήματα
		case DAMAGED         = 'damaged';
		case EXPIRED         = 'expired';
		case QC_REJECT       = 'qc_reject';
		case QC_SAMPLE       = 'qc_sample';
		case QUALITY_CONTROL = 'quality_control';
		case SPILLAGE        = 'spillage';

		// Απώλειες & Κλοπές
		case THEFT     = 'theft';
		case LOST      = 'lost';
		case WRITE_OFF = 'write_off';

		// Επιχειρησιακή Χρήση
		case PRODUCTION = 'production';
		case SAMPLE     = 'sample';
		case DEMO       = 'demo';
		case PROMO      = 'promo';
		case DONATION   = 'donation';

		case OTHER = 'other';

		public function label(): string {
			return __("inventory.reasons." . $this->value);
		}
	}