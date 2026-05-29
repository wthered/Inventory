<?php

	namespace App\Enums;

	enum ProductUnit: string {
		case PIECES    = 'pcs';
		case LITERS    = 'liter';
		case PACKS     = 'pack';
		case KILOGRAMS = 'kg';

		/**
		 * Get all units as array for dropdowns
		 */
		public static function toArray(): array {
			return [
				self::PIECES->value    => self::PIECES->label(),
				self::LITERS->value    => self::LITERS->label(),
				self::PACKS->value     => self::PACKS->label(),
				self::KILOGRAMS->value => self::KILOGRAMS->label(),
			];
		}

		public function label(): string {
			return match ($this) {
				self::PIECES => 'Pieces',
				self::LITERS => 'Liters',
				self::PACKS => 'Packs',
				self::KILOGRAMS => 'Kilograms',
			};
		}
	}