<?php

	namespace App\Enums\Inventory;

	use Exception;

	enum AdjustmentType: string {
		case INCREASE   = 'increase';
		case DECREASE   = 'decrease';
		case TRANSFER   = 'transfer';
		case ADJUSTMENT = 'adjustment';
		case CORRECTION = 'correction';

		case PENDING = 'pending';

		public function label(): string {
			return __("inventory.types." . $this->value);
		}

		/**
		 * Returns the allowed Enum objects for each adjustment type.
		 *
		 * @throws Exception
		 */
		public function validReasons(): array {
			return match ($this) {
				self::INCREASE => [
					AdjustmentReason::FOUND,
					AdjustmentReason::ADJUSTMENT,
					AdjustmentReason::PRODUCTION,
					AdjustmentReason::RETURNED,
					AdjustmentReason::TRANSFER_IN
				],
				self::DECREASE => [
					AdjustmentReason::DAMAGED,
					AdjustmentReason::LOST,
					AdjustmentReason::EXPIRED,
					AdjustmentReason::SAMPLE,
					AdjustmentReason::PROMO,
					AdjustmentReason::ADJUSTMENT,
					AdjustmentReason::TRANSFER_OUT,
				],
				self::TRANSFER => [
					AdjustmentReason::TRANSFER_IN,
					AdjustmentReason::TRANSFER_OUT,
				],
				self::ADJUSTMENT, self::CORRECTION => [
					AdjustmentReason::ADJUSTMENT,
					AdjustmentReason::FOUND,
					AdjustmentReason::LOST,
					AdjustmentReason::OTHER,
				],
				self::PENDING => throw new Exception('To be implemented'),
			};
		}
	}