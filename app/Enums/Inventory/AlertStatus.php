<?php

	namespace App\Enums\Inventory;

	enum AlertStatus: string {
		case ACTIVE    = 'active';
		case RESOLVED  = 'resolved';
		case DISMISSED = 'dismissed';

		public function label(): string {
			return __("enums.alert_status.{$this->value}");
		}

		public function color(): string {
			return match($this) {
				self::ACTIVE    => 'danger',
				self::RESOLVED  => 'success',
				self::DISMISSED => 'secondary',
			};
		}

		public function hexColor(): string {
			return match($this) {
				self::ACTIVE    => '#ef4444',
				self::RESOLVED  => '#22c55e',
				self::DISMISSED => '#64748b',
			};
		}
	}