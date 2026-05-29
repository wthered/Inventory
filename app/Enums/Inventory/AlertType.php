<?php

	namespace App\Enums\Inventory;

	enum AlertType: string {
		case LOW_STOCK = 'low_stock';
		case OUT_OF_STOCK = 'out_of_stock';
		case OVERSTOCK = 'overstock';
		case EXPIRING_SOON = 'expiring_soon';
		case EXPIRED = 'expired';

		public function label(): string {
			return __("enums.alert_type.".$this->value);
		}

		public function color(): string {
			return match($this) {
				self::LOW_STOCK     => 'warning',
				self::OUT_OF_STOCK  => 'danger',
				self::OVERSTOCK     => 'info',
				self::EXPIRING_SOON => 'orange', // Custom class ή warning
				self::EXPIRED       => 'dark',
			};
		}

		public function hexColor(): string {
			return match($this) {
				self::LOW_STOCK     => '#f59e0b',
				self::OUT_OF_STOCK  => '#ef4444',
				self::OVERSTOCK     => '#3b82f6',
				self::EXPIRING_SOON => '#f97316',
				self::EXPIRED       => '#7f1d1d',
			};
		}
	}
