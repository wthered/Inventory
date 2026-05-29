<?php

	namespace App\Enums\Stock;

	use Illuminate\Support\Collection;

	enum QualityStatus: string {
		case NEW       = 'new';
		case OPENED    = 'opened';
		case DAMAGED   = 'damaged';
		case DEFECTIVE = 'defective';
		case EXPIRED   = 'expired';

		/**
		 * Επιστρέφει όλα τα cases σε μορφή array για dropdowns [value => label]
		 */
		public static function options(): array {
			return Collection::make(self::cases())->pluck('value', 'value')->map(function ($v) {
				self::from($v)->label();
			})->toArray();
		}

		/**
		 * Επιστρέφει τη μετάφραση από το enums.php
		 */
		public function label(): string {
			return __("enums.quality_status." . $this->value);
		}
	}