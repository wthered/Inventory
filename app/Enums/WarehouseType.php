<?php

	namespace App\Enums;

	enum WarehouseType: string {
		// General Purpose
		case GENERAL      = 'general_storage';
		case DISTRIBUTION = 'distribution_center';
		case REGIONAL     = 'regional_warehouse';
		case FULFILLMENT  = 'fulfillment_center';

		// Climate Controlled
		case COLD    = 'cold_storage';
		case CLIMATE = 'climate_controlled';
		case FROZEN  = 'frozen_storage';

		// Legal / Operations
		case BONDED  = 'bonded_warehouse';
		case PUBLIC  = 'public_warehouse';
		case PRIVATE = 'private_warehouse';

		// Logistics Logic
		case CROSS_DOCKING  = 'cross_docking';
		case BUFFER         = 'buffer_warehouse';
		case RAW_MATERIALS  = 'raw_materials';
		case FINISHED_GOODS = 'finished_goods';

		// Specialized
		case HAZMAT    = 'hazardous_materials';
		case AUTOMATED = 'automated_warehouse';
		case RETURNS   = 'returns_processing';

		/**
		 * Επιστρέφει ένα αναγνώσιμο Label για το UI
		 */
		public function label(): string {
			return match ($this) {
				self::GENERAL => 'Γενικής Χρήσης',
				self::DISTRIBUTION => 'Κέντρο Διανομής',
				self::REGIONAL => 'Περιφερειακή Αποθήκη',
				self::FULFILLMENT => 'Fulfillment Center',
				self::COLD => 'Ψυχρή Αποθήκευση',
				self::CLIMATE => 'Κλιματιζόμενη',
				self::FROZEN => 'Κατάψυξη',
				self::BONDED => 'Τελωνειακή Αποταμίευση',
				self::PUBLIC => 'Δημόσια Αποθήκη',
				self::PRIVATE => 'Ιδιωτική Αποθήκη',
				self::CROSS_DOCKING => 'Cross-Docking',
				self::BUFFER => 'Αποθήκη Ασφαλείας',
				self::RAW_MATERIALS => 'Πρώτων Υλών',
				self::FINISHED_GOODS => 'Έτοιμων Προϊόντων',
				self::HAZMAT => 'Επικίνδυνων Υλικών (HazMat)',
				self::AUTOMATED => 'Αυτοματοποιημένη (Smart)',
				self::RETURNS => 'Κέντρο Επιστροφών',
			};
		}
	}