<?php

	namespace App\Enums\Inventory;

	enum StockReturnStatus: string {
		case PENDING    = 'pending';
		case RECEIVED   = 'received';
		case INSPECTING = 'inspecting';
		case COMPLETED  = 'completed';
		case CANCELLED  = 'cancelled';

		/**
		 * Επιστρέφει το label από το translation file.
		 */
		public function label(): string {
			return __("enums.stock_return_status.".$this->value);
		}

		/**
		 * Επιστρέφει το HexCode για το χρώμα.
		 */
		public function color(): string {
			return match ($this) {
				self::PENDING    => '#FBBF24',
				self::RECEIVED   => '#3B82F6',
				self::INSPECTING => '#A855F7',
				self::COMPLETED  => '#22C55E',
				self::CANCELLED  => '#EF4444',
			};
		}

		/**
		 * Helper για να παίρνεις και ένα "light" background αν το θες στο UI
		 */
		public function bgColor(): string {
			return match ($this) {
				self::PENDING    => '#FEF3C7',
				self::RECEIVED   => '#DBEAFE',
				self::INSPECTING => '#F3E8FF',
				self::COMPLETED  => '#DCFCE7',
				self::CANCELLED  => '#FEE2E2',
			};
		}
	}