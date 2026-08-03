<?php

	namespace App\Enums\HumanResources;

	enum LeaveType: string {
		case ANNUAL    = 'annual';       // Κανονική
		case SICK      = 'sick';           // Ασθενείας
		case UNPAID    = 'unpaid';       // Άνευ αποδοχών
		case MATERNITY = 'maternity'; // Μητρότητας / Πατρότητας
		case SPECIAL   = 'special';     // Ειδική (π.χ. Γάμος, εκλογές)

		/**
		 * Επιστρέφει τη μεταφρασμένη ονομασία βάσει του active locale.
		 */
		public function label(): string {
			return __("human_resources.leave_types.{$this->value}");
		}
	}
