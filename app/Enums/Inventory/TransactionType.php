<?php

	namespace App\Enums\Inventory;

	enum TransactionType: string {
		case IN         = 'in';
		case OUT        = 'out';
		case ADJUSTMENT = 'adjustment';
		case TRANSFER   = 'transfer';
		case RETURN     = 'return';
		case OTHER      = 'other'; // Προσθήκη για να μην σκάει ο κώδικας

		public function label(): string {
			return __("inventory.types." . $this->value);
		}

		public function validReasons(): array {
			return match ($this) {
				self::IN => [
					TransactionReason::PURCHASE,
					TransactionReason::RETURNED,
					TransactionReason::FOUND,
					TransactionReason::PRODUCTION
				],
				self::OUT => [
					TransactionReason::SALE,
					TransactionReason::DAMAGED,
					TransactionReason::LOST,
					TransactionReason::SAMPLE,
					TransactionReason::THEFT,
					TransactionReason::EXPIRED
				],
				self::ADJUSTMENT => [
					TransactionReason::STOCKTAKE,
					TransactionReason::COUNTING_ERROR,
					TransactionReason::DATA_ENTRY,
					TransactionReason::FOUND,
					TransactionReason::OTHER
				],
				self::TRANSFER => [
					TransactionReason::TRANSFER_IN,
					TransactionReason::TRANSFER_OUT
				],
				self::RETURN, self::OTHER => [TransactionReason::OTHER],
			};
		}

		public function sign(): int {
			return match ($this) {
				self::IN, self::RETURN => 1,
				self::OUT => -1,
				self::ADJUSTMENT, self::TRANSFER, self::OTHER => 0,
			};
		}
	}